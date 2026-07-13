import { defineConfig } from 'vitest/config';

// The unit suite covers the framework-agnostic core (API client, token store,
// push registration, formatting) — none of which import 'react-native', so it
// runs headlessly in Node without the native toolchain or a simulator.
export default defineConfig({
    resolve: {
        alias: { '@': new URL('./src', import.meta.url).pathname },
    },
    test: {
        globals: true,
        environment: 'node',
        include: ['src/**/*.test.ts'],
    },
});
