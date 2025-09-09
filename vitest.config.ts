import { defineConfig } from 'vitest/config';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import path from 'path';

export default defineConfig({
  plugins: [svelte({ hot: !process.env.VITEST })],
  test: {
    globals: true,
    environment: 'jsdom',
    include: ['resources/js/**/*.{test,spec}.{js,ts,svelte}'],
    setupFiles: ['resources/js/tests/setup.ts'],
  },
  resolve: process.env.VITEST ? {
    conditions: ['browser'],
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
      '$lib': path.resolve(__dirname, './resources/js/lib'),
    },
  } : {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
      '$lib': path.resolve(__dirname, './resources/js/lib'),
    },
  },
});