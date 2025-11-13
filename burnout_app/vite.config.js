import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/dashboard.js',
                'resources/js/export.js',
                'resources/js/files.js',
                'resources/js/records.js',
                'resources/js/assessment.js',
                'resources/js/view.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
