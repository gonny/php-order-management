import { defineConfig } from 'vitest/config';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import path from 'path';

export default defineConfig({
  plugins: [svelte({ hot: !process.env.VITEST })],
  test: {
    globals: true,
    environment: 'happy-dom',
    include: ['resources/js/**/*.{test,spec}.{js,ts}'],
    setupFiles: ['resources/js/tests/setup.ts'],
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
      '$lib': path.resolve(__dirname, './resources/js/lib'),
    },
  },
});