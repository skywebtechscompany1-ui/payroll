<template>
  <div class="min-h-screen relative overflow-hidden flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-blue-900 dark:to-purple-900">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
      <!-- Floating Circles -->
      <div class="absolute top-20 left-10 w-72 h-72 bg-blue-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
      <div class="absolute top-40 right-10 w-72 h-72 bg-purple-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
      <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Login Card -->
    <div class="relative z-10 w-full max-w-md mx-4">
      <!-- Glassmorphism Card -->
      <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/20 p-8 md:p-10 transform transition-all duration-500 hover:scale-[1.02]">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
          <!-- Animated Logo -->
          <div class="relative mx-auto w-20 h-20 mb-6">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl animate-pulse-slow"></div>
            <div class="absolute inset-0.5 bg-white dark:bg-gray-800 rounded-2xl flex items-center justify-center">
              <Icon name="heroicons:building-office-2" class="h-10 w-10 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
          
          <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">
            Welcome Back
          </h1>
          <p class="text-gray-600 dark:text-gray-300 text-sm">
            Sign in to access your payroll dashboard
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Email -->
          <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
              Email address
            </label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <Icon name="heroicons:envelope" class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" />
              </div>
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                class="w-full pl-12 pr-4 py-3 bg-white/50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 placeholder-gray-400"
                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500/20': errors.email }"
                placeholder="you@example.com"
              />
            </div>
            <p v-if="errors.email" class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
              <Icon name="heroicons:exclamation-circle" class="h-4 w-4" />
              {{ errors.email }}
            </p>
          </div>

          <!-- Password -->
          <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
              Password
            </label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <Icon name="heroicons:lock-closed" class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" />
              </div>
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                class="w-full pl-12 pr-12 py-3 bg-white/50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 placeholder-gray-400"
                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500/20': errors.password }"
                placeholder="••••••••"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-4 flex items-center hover:text-blue-500 transition-colors"
              >
                <Icon :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-5 w-5 text-gray-400" />
              </button>
            </div>
            <p v-if="errors.password" class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
              <Icon name="heroicons:exclamation-circle" class="h-4 w-4" />
              {{ errors.password }}
            </p>
          </div>

          <!-- Remember me & Forgot Password -->
          <div class="flex items-center justify-between">
            <label class="flex items-center cursor-pointer group">
              <input
                id="remember-me"
                v-model="form.remember_me"
                type="checkbox"
                class="w-4 h-4 text-blue-600 bg-white/50 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 transition-all"
              />
              <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 transition-colors">
                Remember me
              </span>
            </label>

            <NuxtLink
              to="/forgot-password"
              class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
            >
              Forgot password?
            </NuxtLink>
          </div>

          <!-- Submit button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full relative py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
          >
            <span v-if="!isLoading" class="flex items-center justify-center gap-2">
              <Icon name="heroicons:arrow-right-on-rectangle" class="h-5 w-5" />
              Sign in
            </span>
            <span v-else class="flex items-center justify-center gap-2">
              <Icon name="heroicons:arrow-path" class="animate-spin h-5 w-5" />
              Signing in...
            </span>
          </button>
        </form>

        <!-- Footer -->
        <div class="mt-8 text-center">
          <p class="text-xs text-gray-500 dark:text-gray-400">
            Made by <span class="font-semibold text-blue-600">Jafasol Systems</span>
          </p>
        </div>
      </div>
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
    console.log('Attempting login with:', form.email)
    
    const response = await auth.login({
      email: form.email,
      password: form.password,
      remember_me: form.remember_me
    })

    console.log('Login response:', response)
    console.log('Auth state:', {
      isAuthenticated: auth.isAuthenticated.value,
      user: auth.user.value,
      isAdmin: auth.isAdmin.value,
      isHR: auth.isHR.value
    })

    toast.success('Login successful!')

    // Simple redirect to dashboard for all users
    console.log('Redirecting to dashboard...')
    await navigateTo('/dashboard', { replace: true })
    
  } catch (error: any) {
    console.error('Login error:', error)
    toast.error(error.message || 'Login failed')
    isLoading.value = false
  }
}

// Redirect if already authenticated
onMounted(() => {
  if (auth.isAuthenticated.value) {
    console.log('Already authenticated, redirecting to dashboard...')
    navigateTo('/dashboard', { replace: true })
  }
})
</script>

<style scoped>
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

@keyframes pulse-slow {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse-slow {
  animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>