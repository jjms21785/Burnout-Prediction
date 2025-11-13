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
                'resources/js/report.js',
                'resources/js/assessment.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
