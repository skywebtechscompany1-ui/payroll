import Toast, { TYPE } from 'vue-toastification'
import 'vue-toastification/dist/index.css'

export default defineNuxtPlugin((nuxtApp) => {
  const toastOptions = {
    timeout: 5000,
    position: 'top-right',
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: 'button',
    icon: true,
    rtl: false,
    toastClassName: 'custom-toast',
    bodyClassName: 'custom-toast-body',
    transition: 'Vue-Toastification__fade'
  }

  nuxtApp.vueApp.use(Toast, toastOptions)

  // Add global toast methods
  nuxtApp.vueApp.config.globalProperties.$toast = {
    success: (message: string, options?: any) => {
      if (process.client) {
        useToast().success(message, options)
      }
    },
    error: (message: string, options?: any) => {
      if (process.client) {
        useToast().error(message, options)
      }
    },
    warning: (message: string, options?: any) => {
      if (process.client) {
        useToast().warning(message, options)
      }
    },
    info: (message: string, options?: any) => {
      if (process.client) {
        useToast().info(message, options)
      }
    }
  }

  return {
    provide: {
      toast: nuxtApp.vueApp.config.globalProperties.$toast
    }
  }
})