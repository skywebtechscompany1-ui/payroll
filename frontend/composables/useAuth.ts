interface User {
  id: number
  email: string
  name: string
  role: string
  employee_id?: string
  is_active: boolean
  access_label: number
  designation_id?: number
}

interface LoginCredentials {
  email: string
  password: string
  remember_me?: boolean
}

interface RegisterData {
  name: string
  email: string
  password: string
  employee_id?: string
  role?: string
}

interface AuthResponse {
  access_token: string
  refresh_token: string
  token_type: string
  expires_in: number
  user: User
}

export const useAuth = () => {
  // State
  const user = useState<User | null>('auth-user', () => null)
  const token = useState<string>('auth-token', () => '')
  const refreshToken = useState<string>('auth-refresh-token', () => '')
  const isLoading = useState<boolean>('auth-loading', () => false)
  const isAuthenticated = computed(() => !!user.value && !!token.value)

  // Initialize from localStorage
  const initializeAuth = () => {
    if (process.client) {
      const savedToken = localStorage.getItem('auth-token')
      const savedRefreshToken = localStorage.getItem('auth-refresh-token')
      const savedUser = localStorage.getItem('auth-user')

      if (savedToken && savedUser) {
        token.value = savedToken
        refreshToken.value = savedRefreshToken || ''
        try {
          user.value = JSON.parse(savedUser)
        } catch (e) {
          console.error('Failed to parse saved user data')
          logout()
        }
      }
    }
  }

  // Login
  const login = async (credentials: LoginCredentials): Promise<AuthResponse> => {
    isLoading.value = true
    try {
      const config = useRuntimeConfig()
      
      // Create FormData for OAuth2 compatibility
      const formData = new FormData()
      formData.append('username', credentials.email)
      formData.append('password', credentials.password)
      
      const response = await $fetch<AuthResponse>(`${config.public.apiBase}/auth/login`, {
        method: 'POST',
        body: formData
      })

      // Set authentication state
      token.value = response.access_token
      refreshToken.value = response.refresh_token
      user.value = response.user

      // Save to localStorage
      if (process.client) {
        localStorage.setItem('auth-token', response.access_token)
        localStorage.setItem('auth-refresh-token', response.refresh_token)
        localStorage.setItem('auth-user', JSON.stringify(response.user))
      }

      return response
    } catch (error: any) {
      throw new Error(error.data?.error || 'Login failed')
    } finally {
      isLoading.value = false
    }
  }

  // Register
  const register = async (data: RegisterData): Promise<AuthResponse> => {
    isLoading.value = true
    try {
      const config = useRuntimeConfig()
      const response = await $fetch<AuthResponse>(`${config.public.apiBase}/auth/register`, {
        method: 'POST',
        body: data
      })

      // Set authentication state
      token.value = response.access_token
      refreshToken.value = response.refresh_token
      user.value = response.user

      // Save to localStorage
      if (process.client) {
        localStorage.setItem('auth-token', response.access_token)
        localStorage.setItem('auth-refresh-token', response.refresh_token)
        localStorage.setItem('auth-user', JSON.stringify(response.user))
      }

      return response
    } catch (error: any) {
      throw new Error(error.data?.error || 'Registration failed')
    } finally {
      isLoading.value = false
    }
  }

  // Logout
  const logout = async () => {
    try {
      const config = useRuntimeConfig()
      await $fetch(`${config.public.apiBase}/auth/logout`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token.value}`
        }
      })
    } catch (error) {
      // Ignore logout errors
      console.warn('Logout request failed:', error)
    }

    // Clear state
    token.value = ''
    refreshToken.value = ''
    user.value = null

    // Clear localStorage
    if (process.client) {
      localStorage.removeItem('auth-token')
      localStorage.removeItem('auth-refresh-token')
      localStorage.removeItem('auth-user')
    }
  }

  // Refresh token
  const refreshAccessToken = async (): Promise<boolean> => {
    if (!refreshToken.value) {
      return false
    }

    try {
      const config = useRuntimeConfig()
      const response = await $fetch<AuthResponse>(`${config.public.apiBase}/auth/refresh`, {
        method: 'POST',
        body: {
          refresh_token: refreshToken.value
        }
      })

      // Update tokens
      token.value = response.access_token
      refreshToken.value = response.refresh_token

      // Save to localStorage
      if (process.client) {
        localStorage.setItem('auth-token', response.access_token)
        localStorage.setItem('auth-refresh-token', response.refresh_token)
      }

      return true
    } catch (error) {
      console.error('Token refresh failed:', error)
      logout()
      return false
    }
  }

  // Check authentication status
  const checkAuthStatus = async () => {
    if (token.value && !user.value) {
      initializeAuth()
    }

    if (token.value && user.value) {
      // Verify token is still valid
      try {
        const config = useRuntimeConfig()
        await $fetch(`${config.public.apiBase}/auth/me`, {
          headers: {
            Authorization: `Bearer ${token.value}`
          }
        })
      } catch (error) {
        // Token might be expired, try to refresh
        const refreshed = await refreshAccessToken()
        if (!refreshed) {
          logout()
        }
      }
    }
  }

  // Update user profile
  const updateProfile = async (data: Partial<User>): Promise<User> => {
    if (!isAuthenticated.value) {
      throw new Error('User not authenticated')
    }

    try {
      const config = useRuntimeConfig()
      const response = await $fetch<User>(`${config.public.apiBase}/users/${user.value!.id}`, {
        method: 'PUT',
        headers: {
          Authorization: `Bearer ${token.value}`
        },
        body: data
      })

      // Update user state
      user.value = response

      // Save to localStorage
      if (process.client) {
        localStorage.setItem('auth-user', JSON.stringify(response))
      }

      return response
    } catch (error: any) {
      throw new Error(error.data?.error || 'Profile update failed')
    }
  }

  // Change password
  const changePassword = async (currentPassword: string, newPassword: string): Promise<void> => {
    if (!isAuthenticated.value) {
      throw new Error('User not authenticated')
    }

    try {
      const config = useRuntimeConfig()
      await $fetch(`${config.public.apiBase}/users/${user.value!.id}/change-password`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token.value}`
        },
        body: {
          current_password: currentPassword,
          new_password: newPassword
        }
      })
    } catch (error: any) {
      throw new Error(error.data?.error || 'Password change failed')
    }
  }

  // Role-based access control
  const hasPermission = (permission: string): boolean => {
    if (!user.value) return false

    const role = user.value.role
    const permissions = {
      superadmin: ['*'],
      admin: ['users:create', 'users:read', 'users:update', 'employees:*', 'payroll:*', 'reports:*', 'settings:*'],
      hr: ['employees:read', 'employees:create', 'employees:update', 'attendance:*', 'leave:*', 'payroll:read'],
      manager: ['employees:read', 'attendance:read', 'leave:read', 'payroll:read', 'reports:read'],
      employee: ['attendance:create', 'leave:create', 'leave:read', 'profile:*']
    }

    const userPermissions = permissions[role as keyof typeof permissions] || []
    return userPermissions.includes('*') || userPermissions.includes(permission)
  }

  const hasRole = (role: string): boolean => {
    return user.value?.role === role
  }

  const isAdmin = computed(() => hasRole('superadmin') || hasRole('admin'))
  const isHR = computed(() => hasRole('hr') || isAdmin.value)
  const isManager = computed(() => hasRole('manager') || isHR.value)

  // Initialize on first use
  if (process.client && !token.value) {
    initializeAuth()
  }

  return {
    // State
    user: computed(() => user.value),
    token: readonly(token),
    isLoading: readonly(isLoading),
    isAuthenticated: readonly(isAuthenticated),
    isAdmin,
    isHR,
    isManager,

    // Methods
    initializeAuth,
    login,
    register,
    logout,
    refreshAccessToken,
    checkAuthStatus,
    updateProfile,
    changePassword,
    hasPermission,
    hasRole
  }
}