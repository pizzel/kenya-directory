import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    // This forces Vite to use relative paths (./) for fonts/images inside CSS
    // instead of absolute paths (/). This fixes the issue on localhost subdirectories.
    base: './', 

    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        outDir: 'public/build',
    },
});