/**
 * API composable for making HTTP requests
 */

export const useApi = () => {
  const config = useRuntimeConfig()
  const auth = useAuth()

  const apiCall = async (endpoint: string, options: any = {}) => {
    const headers: any = {
      ...options.headers
    }

    // Only set Content-Type if not FormData (browser sets it automatically for FormData with boundary)
    if (!(options.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json'
    }

    if (auth.token.value) {
      headers['Authorization'] = `Bearer ${auth.token.value}`
    }

    try {
      const response = await $fetch(`${config.public.apiBase}${endpoint}`, {
        ...options,
        headers
      })
      return { data: response, error: null }
    } catch (error: any) {
      console.error('API Error:', error)
      return { data: null, error: error.data?.detail || error.message || 'An error occurred' }
    }
  }

  return {
    get: (endpoint: string, options = {}) => apiCall(endpoint, { method: 'GET', ...options }),
    post: (endpoint: string, body: any, options = {}) => apiCall(endpoint, { method: 'POST', body, ...options }),
    put: (endpoint: string, body: any, options = {}) => apiCall(endpoint, { method: 'PUT', body, ...options }),
    delete: (endpoint: string, options = {}) => apiCall(endpoint, { method: 'DELETE', ...options })
  }
}
