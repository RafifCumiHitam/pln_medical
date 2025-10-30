import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'public/css/sb-admin-2.min.css',
                'public/js/sb-admin-2.min.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
            '~jquery': path.resolve(__dirname, 'node_modules/jquery'),
            '~popper.js': path.resolve(__dirname, 'node_modules/popper.js'),
            'moment': path.resolve(__dirname, 'node_modules/moment'),
        },
    },
    define: {
        'process.env': {},
    },
    server: {
        host: 'localhost',
        port: process.env.VITE_PORT || 5173,
        hmr: { host: 'localhost' },
        watch: { usePolling: true },
    },
    build: {
        outDir: 'public/build',
        assetsDir: 'assets',
        manifest: true,
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                'sb-admin-2': 'public/js/sb-admin-2.min.js',
            },
        },
    },
});
