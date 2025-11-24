<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-secondary-50 dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-md w-full space-y-8 p-8">
      <!-- Header -->
      <div class="text-center">
        <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900">
          <Icon name="heroicons:building-office-2" class="h-8 w-8 text-primary-600 dark:text-primary-400" />
        </div>
        <h2 class="mt-6 text-3xl font-bold text-gray-900 dark:text-white">
          Sign in to Payroll System
        </h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          Enter your credentials to access your account
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="mt-8 space-y-6">
        <div class="space-y-4">
          <!-- Email -->
          <div>
            <label for="email" class="form-label">
              Email address
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Icon name="heroicons:envelope" class="h-5 w-5 text-gray-400" />
              </div>
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                class="form-input pl-10"
                :class="{ 'border-error-300 focus:border-error-500 focus:ring-error-500': errors.email }"
                placeholder="Enter your email"
              />
            </div>
            <p v-if="errors.email" class="mt-1 text-sm text-error-600">{{ errors.email }}</p>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="form-label">
              Password
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Icon name="heroicons:lock-closed" class="h-5 w-5 text-gray-400" />
              </div>
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                class="form-input pl-10 pr-10"
                :class="{ 'border-error-300 focus:border-error-500 focus:ring-error-500': errors.password }"
                placeholder="Enter your password"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
              >
                <Icon :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-5 w-5 text-gray-400" />
              </button>
            </div>
            <p v-if="errors.password" class="mt-1 text-sm text-error-600">{{ errors.password }}</p>
          </div>

          <!-- Remember me -->
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember-me"
                v-model="form.remember_me"
                type="checkbox"
                class="form-checkbox"
              />
              <label for="remember-me" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                Remember me
              </label>
            </div>

            <div class="text-sm">
              <NuxtLink
                to="/forgot-password"
                class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
              >
                Forgot your password?
              </NuxtLink>
            </div>
          </div>
        </div>

        <!-- Submit button -->
        <div>
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full btn-primary relative"
          >
            <span v-if="!isLoading">Sign in</span>
            <span v-else class="flex items-center justify-center">
              <Icon name="heroicons:arrow-path" class="animate-spin -ml-1 mr-3 h-5 w-5" />
              Signing in...
            </span>
          </button>
        </div>

        <!-- Register link -->
        <div class="text-center">
          <span class="text-sm text-gray-600 dark:text-gray-400">
            Don't have an account?
            <NuxtLink
              to="/register"
              class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
            >
              Sign up
            </NuxtLink>
          </span>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: 'guest'
})

const auth = useAuth()
const toast = useToast()

// Reactive state
const form = reactive({
  email: '',
  password: '',
  remember_me: false
})

const isLoading = ref(false)
const showPassword = ref(false)
const errors = ref<Record<string, string>>({})

// Methods
const validateForm = (): boolean => {
  errors.value = {}

  if (!form.email) {
    errors.value.email = 'Email is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.value.email = 'Please enter a valid email address'
  }

  if (!form.password) {
    errors.value.password = 'Password is required'
  }

  return Object.keys(errors.value).length === 0
}

const handleLogin = async () => {
  if (!validateForm()) {
    return
  }

  isLoading.value = true
  try {
    await auth.login({
      email: form.email,
      password: form.password,
      remember_me: form.remember_me
    })

    toast.success('Login successful!')

    // Redirect based on user role
    if (auth.isAdmin) {
      await navigateTo('/dashboard')
    } else if (auth.isHR) {
      await navigateTo('/employees')
    } else {
      await navigateTo('/profile')
    }
  } catch (error: any) {
    toast.error(error.message || 'Login failed')
  } finally {
    isLoading.value = false
  }
}

// Redirect if already authenticated
onMounted(() => {
  if (auth.isAuthenticated) {
    navigateTo('/dashboard')
  }
})
</script>