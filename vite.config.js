import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import { fileURLToPath, URL } from 'node:url';

const isTest = process.env.VITEST === 'true';

export default defineConfig({
    plugins: [
        react(),
        tailwindcss(),
        ...(isTest
            ? []
            : [
                  laravel({
                      input: ['resources/css/app.css', 'resources/js/main.tsx'],
                      refresh: true,
                      fonts: [bunny('Instrument Sans', { weights: [400, 500, 600] })],
                  }),
              ]),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    test: {
        globals: true,
        environment: 'jsdom',
        setupFiles: ['./resources/js/test/setup.ts'],
        css: false,
    },
});
