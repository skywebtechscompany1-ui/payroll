/**
 * Toast notification composable
 */

export const useToast = () => {
  const show = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'info') => {
    if (process.client) {
      const toast = (window as any).$toast
      if (toast) {
        toast[type](message)
      } else {
        // Fallback to console if toast not available
        console.log(`[${type.toUpperCase()}]`, message)
      }
    }
  }

  return {
    success: (message: string) => show(message, 'success'),
    error: (message: string) => show(message, 'error'),
    warning: (message: string) => show(message, 'warning'),
    info: (message: string) => show(message, 'info')
  }
}
