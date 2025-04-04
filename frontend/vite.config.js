import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import { resolve } from 'node:path'

// https://vite.dev/config/
export default defineConfig({
  base: '/vite/',
  optimizeDeps: {
    exclude: ['vue', 'vue-email-editor']
  },
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    hmr: {
      path: '/wss',
    }
  },
  build: {
    outDir: '../public/dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: resolve(__dirname, 'src/main.js'),
      output: {
        manualChunks(id) {
          // all third-party code will be in vendor chunk
          if (id.includes('node_modules')) {
            return 'vendor'
          }
          // example on how to create another chunk
          // if (id.includes('src/'components')) {
          //   return 'components'
          // }
          // console.log(id)
        },
      },
    }
  },
})
