import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path' // Нужно для корректных путей, если потребуется

export default defineConfig({
  plugins: [vue()],
  
  // Явно прописываем переменную, чтобы исключить любые проблемы с .env файлами
  define: {
    'import.meta.env.VITE_API_BASE_URL': JSON.stringify('/api'),
  },

  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'), // Чтобы работал @/components...
    },
  },

  build: {
    outDir: 'dist', // Папка куда собирать
    assetsDir: 'assets',
    emptyOutDir: true, // Очищать папку перед сборкой
  }
})