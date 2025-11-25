<template>
  <div class="min-h-screen relative overflow-hidden flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-blue-900 dark:to-purple-900">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute top-20 left-10 w-72 h-72 bg-blue-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
      <div class="absolute top-40 right-10 w-72 h-72 bg-purple-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
      <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>
    </div>

    <div class="relative z-10 w-full max-w-md mx-4">
      <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/20 p-8 md:p-10 transform transition-all duration-500">
        <!-- Header -->
        <div class="text-center mb-8">
          <div class="relative mx-auto w-20 h-20 mb-6">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl animate-pulse-slow"></div>
            <div class="absolute inset-0.5 bg-white dark:bg-gray-800 rounded-2xl flex items-center justify-center">
              <Icon name="heroicons:key" class="h-10 w-10 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
          <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
            Reset Password
          </h1>
          <p class="text-gray-600 dark:text-gray-300 text-sm">
            Enter your email to receive a password reset link
          </p>
        </div>

      <!-- Success Message -->
      <div v-if="emailSent" class="rounded-md bg-green-50 dark:bg-green-900/20 p-4">
        <div class="flex">
          <Icon name="heroicons:check-circle" class="h-5 w-5 text-green-400" />
          <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
              Email sent successfully!
            </h3>
            <p class="mt-2 text-sm text-green-700 dark:text-green-300">
              We've sent a password reset link to <strong>{{ form.email }}</strong>. 
              Please check your inbox and follow the instructions.
            </p>
          </div>
        </div>
      </div>

        <!-- Form -->
        <form v-if="!emailSent" @submit.prevent="handleSubmit" class="space-y-6">
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

          <!-- Submit button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full relative py-3 px-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
          >
            <span v-if="!isLoading" class="flex items-center justify-center gap-2">
              <Icon name="heroicons:paper-airplane" class="h-5 w-5" />
              Send Reset Link
            </span>
            <span v-else class="flex items-center justify-center gap-2">
              <Icon name="heroicons:arrow-path" class="animate-spin h-5 w-5" />
              Sending...
            </span>
          </button>

          <!-- Back to login -->
          <div class="text-center mt-6">
            <NuxtLink
              to="/login"
              class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
            >
              <Icon name="heroicons:arrow-left" class="h-4 w-4" />
              Back to login
            </NuxtLink>
          </div>
        </form>

        <!-- After email sent -->
        <div v-else class="space-y-4">
          <button
            @click="resetForm"
            class="w-full py-3 px-4 bg-white/50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-semibold rounded-xl hover:bg-white/80 dark:hover:bg-gray-700/80 transition-all duration-300"
          >
            Send another reset link
          </button>
          <div class="text-center">
            <NuxtLink
              to="/login"
              class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
            >
              <Icon name="heroicons:arrow-left" class="h-4 w-4" />
              Back to login
            </NuxtLink>
          </div>
        </div>

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

const api = useApi()
const toast = useToast()

const form = reactive({
  email: ''
})

const isLoading = ref(false)
const emailSent = ref(false)
const errors = ref<Record<string, string>>({})

const validateForm = (): boolean => {
  errors.value = {}

  if (!form.email) {
    errors.value.email = 'Email is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.value.email = 'Please enter a valid email address'
  }

  return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  isLoading.value = true
  try {
    const { error } = await api.post('/auth/forgot-password', { email: form.email })
    
    if (error) {
      toast.error(error)
    } else {
      emailSent.value = true
      toast.success('Password reset link sent!')
    }
  } catch (error: any) {
    toast.error(error.message || 'Failed to send reset link')
  } finally {
    isLoading.value = false
  }
}

const resetForm = () => {
  form.email = ''
  emailSent.value = false
  errors.value = {}
}
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
