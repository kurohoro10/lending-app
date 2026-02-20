// app.js
import './bootstrap';
import './script';

// Auto-load all application modules
import.meta.glob('./applications/**/*.js', { eager: true });

// Auto-load all admin modules
import.meta.glob('./admin/**/*.js', { eager: true });
