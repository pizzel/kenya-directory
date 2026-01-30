import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.jsx'],
            ssr: 'resources/js/ssr.jsx',
            refresh: true,
        }),
        react(),
    ],
    build: {
        outDir: 'public/build',
    },
    server: {
        host: '127.0.0.1',
        port: 5177,
        strictPort: true,
        cors: true,
        hmr: {
            host: '127.0.0.1',
        },
    },
});