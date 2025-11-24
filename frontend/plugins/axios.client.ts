export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const auth = useAuth()

  // Configure axios defaults
  const $axios = $fetch.create({
    baseURL: config.public.apiBase,
    onRequest({ request, options }) {
      // Add auth token to headers
      const token = auth.token
      if (token) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${token}`
        }
      }
    },
    onResponseError({ response }) {
      // Handle 401 unauthorized
      if (response?.status === 401) {
        auth.logout()
        navigateTo('/login')
      }

      // Handle other errors
      if (response?.status >= 500) {
        showError({
          statusCode: response.status,
          statusMessage: 'Server error. Please try again later.'
        })
      }
    }
  })

  // Make $axios available globally
  return {
    provide: {
      axios: $axios
    }
  }
})