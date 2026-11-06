<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Student Management System</h2>
      <!-- User Profile & Logout -->
      <div v-if="currentUser" class="text-end">
        <span class="me-2 fw-bold">{{ currentUser.name }} ({{ currentUser.role || 'User' }})</span>
        <button @click="handleLogout" class="btn btn-outline-danger btn-sm">Logout</button>
      </div>
    </div>

    <!-- Alert Notifications -->
    <div v-if="message" class="alert alert-success alert-dismissible fade show" role="alert">
      {{ message }}
      <button type="button" class="btn-close" @click="message = ''"></button>
    </div>
    <div v-if="error" class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ error }}
      <button type="button" class="btn-close" @click="error = ''"></button>
    </div>

    <!-- Login Form (v-if not authenticated) -->
    <div v-if="!token" class="card mx-auto mb-4" style="max-width: 450px;">
      <div class="card-header bg-primary text-white font-weight-bold">
        Login to Access Student Management
      </div>
      <div class="card-body">
        <form @submit.prevent="handleLogin">
          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input v-model="loginForm.email" type="email" class="form-control" required placeholder="admin@example.com" />
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input v-model="loginForm.password" type="password" class="form-control" required placeholder="••••••••" />
          </div>
          <button type="submit" class="btn btn-primary w-100" :disabled="loading">
            {{ loading ? 'Authenticating...' : 'Login' }}
          </button>
        </form>
      </div>
    </div>

    <!-- Main Content (v-if authenticated) -->
    <div v-else>
      <!-- Add / Edit Form (ADMIN ONLY) -->
      <div v-if="isAdmin" class="card mb-4">
        <div class="card-header font-weight-bold bg-light">
          {{ isEditing ? 'Edit Student (Admin Action)' : 'Add New Student (Admin Action)' }}
        </div>
        <div class="card-body">
          <form @submit.prevent="saveStudent">
            <div class="row">
              <div class="col-md-5 mb-2">
                <input v-model="form.name" type="text" class="form-control" placeholder="Student Name" required />
              </div>
              <div class="col-md-5 mb-2">
                <input v-model="form.email" type="email" class="form-control" placeholder="Email Address" required />
              </div>
              <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                  {{ isEditing ? 'Update' : 'Add' }}
                </button>
              </div>
            </div>
            <button v-if="isEditing" type="button" @click="resetForm" class="btn btn-secondary btn-sm mt-2">
              Cancel Edit
            </button>
          </form>
        </div>
      </div>

      <!-- Regular User Notice -->
      <div v-else class="alert alert-info">
        ℹ️ You are logged in as a <strong>Regular User</strong>. You have Read-Only access. Admin privilege is required to Add, Edit, or Delete records.
      </div>

      <!-- Student List Table -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Student List</span>
          <span v-if="loading" class="spinner-border spinner-border-sm text-primary"></span>
        </div>
        <div class="card-body p-0">
          <table class="table table-striped mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th v-if="isAdmin" style="width: 150px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="student in students" :key="student.id">
                <td>{{ student.id }}</td>
                <td>{{ student.name }}</td>
                <td>{{ student.email }}</td>
                <td v-if="isAdmin">
                  <button @click="editStudent(student)" class="btn btn-sm btn-warning me-1">Edit</button>
                  <button @click="deleteStudent(student.id)" class="btn btn-sm btn-danger">Delete</button>
                </td>
              </tr>
              <tr v-if="students.length === 0">
                <td :colspan="isAdmin ? 4 : 3" class="text-center py-3 text-muted">No students found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// Managed State using Composition API ref()
const students = ref([]);
const currentUser = ref(null);
const token = ref(localStorage.getItem('auth_token') || '');
const loading = ref(false);
const message = ref('');
const error = ref('');
const isEditing = ref(false);

const form = ref({ id: null, name: '', email: '' });
const loginForm = ref({ email: '', password: '' });

// Computed Property for Role-based UI
const isAdmin = computed(() => {
  return currentUser.value && currentUser.value.role === 'admin';
});

// Configure Axios Bearer Token Header
const getAuthConfig = () => ({
  headers: {
    'Authorization': `Bearer ${token.value}`,
    'Accept': 'application/json'
  }
});

// Auth Handlers
const handleLogin = async () => {
  error.value = '';
  message.value = '';
  loading.value = true;
  try {
    const response = await axios.post('/api/login', loginForm.value);
    token.value = response.data.token || response.data.access_token;
    localStorage.setItem('auth_token', token.value);
    
    // Fetch logged in user profile
    await fetchUserProfile();
    await fetchStudents();
    message.value = 'Logged in successfully!';
  } catch (err) {
    error.value = err.response?.data?.message || 'Invalid credentials or login failed.';
  } finally {
    loading.value = false;
  }
};

const handleLogout = async () => {
  try {
    await axios.post('/api/logout', {}, getAuthConfig());
  } catch (err) {
    console.error('Logout error:', err);
  } finally {
    token.value = '';
    currentUser.value = null;
    students.value = [];
    localStorage.removeItem('auth_token');
    message.value = 'Logged out successfully.';
  }
};

const fetchUserProfile = async () => {
  if (!token.value) return;
  try {
    const response = await axios.get('/api/user', getAuthConfig());
    currentUser.value = response.data;
  } catch (err) {
    // If token invalid/expired, clear auth
    handleLogout();
  }
};

// Student CRUD Handlers
const fetchStudents = async () => {
  if (!token.value) return;
  loading.value = true;
  try {
    const response = await axios.get('/api/students', getAuthConfig());
    students.value = Array.isArray(response.data) ? response.data : (response.data.data || []);
  } catch (err) {
    error.value = 'Failed to fetch students. Bearer token might be invalid.';
  } finally {
    loading.value = false;
  }
};

const saveStudent = async () => {
  error.value = '';
  message.value = '';
  loading.value = true;
  try {
    const payload = { name: form.value.name, email: form.value.email, course: 'N/A' };
    if (isEditing.value) {
      await axios.put(`/api/students/${form.value.id}`, payload, getAuthConfig());
      message.value = 'Student updated successfully!';
    } else {
      await axios.post('/api/students', payload, getAuthConfig());
      message.value = 'Student created successfully!';
    }
    resetForm();
    await fetchStudents();
  } catch (err) {
    error.value = err.response?.data?.message || 'Action failed. Admin privileges required.';
  } finally {
    loading.value = false;
  }
};

const editStudent = (student) => {
  form.value = { id: student.id, name: student.name, email: student.email };
  isEditing.value = true;
};

const deleteStudent = async (id) => {
  if (!confirm('Are you sure you want to delete this record?')) return;
  loading.value = true;
  try {
    await axios.delete(`/api/students/${id}`, getAuthConfig());
    message.value = 'Student deleted successfully!';
    await fetchStudents();
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete record.';
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  form.value = { id: null, name: '', email: '' };
  isEditing.value = false;
};

// Initial Load
onMounted(() => {
  if (token.value) {
    fetchUserProfile();
    fetchStudents();
  }
});
</script>php artisan route:clear