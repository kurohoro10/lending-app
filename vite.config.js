import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/applications/documents.js',
                'resources/js/applications/e-signature.js',
                'resources/js/applications/employmentDetails.js',
                'resources/js/applications/livingExpenses.js',
                'resources/js/applications/loan-details.js',
                'resources/js/applications/personalDetails.js',
                'resources/js/applications/residential.js',
                'resources/js/applications/application.edit.js',
                'resources/js/applications/creditSense.js',
            ],
            refresh: true,
        }),
    ],
});
