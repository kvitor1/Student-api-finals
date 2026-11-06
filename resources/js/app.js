import { createApp } from 'vue';
import StudentManagement from './components/StudentManagement.vue';

// Lumikha ng app gamit ang StudentManagement bilang root component
const app = createApp(StudentManagement);

// I-mount sa div#app
app.mount('#app');