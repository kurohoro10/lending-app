// app.js
import './bootstrap';
import './script';

// const page = document.body.dataset.page;
// if (page === 'admin/dashboard') {

// }

// Auto-load all application modules
import.meta.glob('./applications/**/*.js', { eager: true });

// Auto-load all admin modules
import.meta.glob('./admin/**/*.js', { eager: true });
