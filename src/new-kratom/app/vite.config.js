import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

const viteHost = process.env.VITE_DEV_SERVER_HOST ?? 'localhost';
const vitePort = Number(process.env.VITE_DEV_SERVER_PORT ?? 5173);

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/scss/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Минификация CSS через esbuild — быстрее lightningcss, без потерь
        cssMinify: 'esbuild',
        // Hashed chunks → можно ставить immutable cache (см. Caddyfile)
        rollupOptions: {
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
            },
        },
        // sourcemap'ы оставляем для prod только в hidden-режиме (карта в файле, но HTML не ссылается)
        sourcemap: 'hidden',
        // Меньше предупреждений о крупных chunk-ах — у нас один большой CSS критичен
        chunkSizeWarningLimit: 800,
        target: 'es2020',
    },
    server: {
        host: '0.0.0.0',
        port: vitePort,
        strictPort: true,
        hmr: {
            host: viteHost,
            port: vitePort,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
            usePolling: process.env.CHOKIDAR_USEPOLLING === 'true',
        },
    },
});
