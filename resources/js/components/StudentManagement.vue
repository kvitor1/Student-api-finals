<template>
  <div class="container mt-4">
    <h2 class="mb-4">Student Management System</h2>

    <!-- Alert Notifications (v-if) -->
    <div v-if="message" class="alert alert-success alert-dismissible fade show" role="alert">
      {{ message }}
      <button type="button" class="btn-close" @click="message = ''"></button>
    </div>
    <div v-if="error" class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ error }}
      <button type="button" class="btn-close" @click="error = ''"></button>
    </div>

    <!-- Add / Edit Student Form -->
    <div class="card mb-4">
      <div class="card-header font-weight-bold">
        {{ isEditing ? 'Edit Student' : 'Add New Student' }}
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
              <button type="submit" class="btn btn-primary w-100">
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

    <!-- Student List Table (v-for & v-if) -->
    <div class="card">
      <div class="card-header">Student List</div>
      <div class="card-body p-0">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th style="width: 150px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in students" :key="student.id">
              <td>{{ student.id }}</td>
              <td>{{ student.name }}</td>
              <td>{{ student.email }}</td>
              <td>
                <button @click="editStudent(student)" class="btn btn-sm btn-warning me-1">Edit</button>
                <button @click="deleteStudent(student.id)" class="btn btn-sm btn-danger">Delete</button>
              </td>
            </tr>
            <tr v-if="students.length === 0">
              <td colspan="4" class="text-center py-3 text-muted">No students found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      students: [],
      form: { id: null, name: '', email: '' },
      isEditing: false,
      message: '',
      error: ''
    };
  },
  mounted() {
    this.fetchStudents();
  },
  methods: {
    async fetchStudents() {
      try {
        const response = await axios.get('/api/students');
        this.students = response.data;
      } catch (err) {
        this.error = 'Failed to load students from API.';
      }
    },
    async saveStudent() {
      this.message = '';
      this.error = '';
      try {
        if (this.isEditing) {
          await axios.put(`/api/students/${this.form.id}`, this.form);
          this.message = 'Student updated successfully!';
        } else {
          await axios.post('/api/students', this.form);
          this.message = 'Student added successfully!';
        }
        this.resetForm();
        this.fetchStudents();
      } catch (err) {
        this.error = 'Error saving student. Check inputs or API.';
      }
    },
    editStudent(student) {
      this.form = { ...student };
      this.isEditing = true;
    },
    async deleteStudent(id) {
      if (!confirm('Are you sure you want to delete this student?')) return;
      try {
        await axios.delete(`/api/students/${id}`);
        this.message = 'Student deleted successfully!';
        this.fetchStudents();
      } catch (err) {
        this.error = 'Failed to delete student.';
      }
    },
    resetForm() {
      this.form = { id: null, name: '', email: '' };
      this.isEditing = false;
    }
  }
};
</script>