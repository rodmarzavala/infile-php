import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), tailwindcss()],
  base: '__FEL_STUDIO_BASE__',
  build: {
    outDir: 'dist',
    emptyOutDir: true
  }
})
