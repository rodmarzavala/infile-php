import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), tailwindcss()],
  base: '/vendor/fel-studio/',
  build: {
    outDir: '../laravel/resources/studio-ui',
    emptyOutDir: true
  }
})
