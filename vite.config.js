import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '192.168.4.25',
        port: 5173,
        cors: true,            // 🔥 ESTO ES CLAVE
        strictPort: true,
        hmr: {
            host: '192.168.4.25',
        },
    },
})