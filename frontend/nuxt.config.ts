// https://nuxt.com/docs/api/configuration/nuxt-config
import { defineNuxtConfig } from 'nuxt/config'

export default defineNuxtConfig({
  devtools: { enabled: true },

  // Modules
  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
    '@vueuse/nuxt',
    'nuxt-icon'
  ],

  // App configuration
  app: {
    head: {
      title: 'Payroll Management System',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { hid: 'description', name: 'description', content: 'Modern Payroll Management System' }
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
      ]
    }
  },

  // CSS
  css: [
    '~/assets/css/main.css'
  ],

  // Runtime config
  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL || 'http://localhost:8000/api/v1',
      appName: 'Payroll Management System',
      appVersion: '1.0.0'
    }
  },

  // Server middleware
  serverMiddleware: [],

  // Plugins
  plugins: [
    '~/plugins/axios.client.ts',
    '~/plugins/toastification.client.ts',
    '~/plugins/chart.client.ts'
  ],

  // Auto imports
  imports: {
    dirs: ['composables', 'utils']
  },

  // Build configuration
  build: {
    transpile: ['vue-chartjs']
  },

  // TypeScript configuration
  typescript: {
    strict: true,
    typeCheck: true
  },

  // Tailwind CSS configuration
  tailwindcss: {
    configPath: './tailwind.config.js'
  },

  // Nitro configuration
  nitro: {
    experimental: {
      wasm: true
    }
  },

  // PostCSS configuration
  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {}
    }
  },

  // Vite configuration
  vite: {
    css: {
      preprocessorOptions: {
        scss: {
          additionalData: `@import "@/assets/css/variables.scss";`
        }
      }
    }
  },

  // Compatibility mode
  compatibilityMode: false
})