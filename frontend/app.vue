<template>
  <NuxtLayout>
    <NuxtPage />
    <ClientOnly>
      <ToastContainer />
    </ClientOnly>
    <UNotifications />
  </NuxtLayout>
</template>

<script setup lang="ts">
// Set up page metadata
useHead({
  titleTemplate: '%s - Payroll Management System',
  htmlAttrs: {
    lang: 'en'
  },
  meta: [
    { charset: 'utf-8' },
    { name: 'viewport', content: 'width=device-width, initial-scale=1' },
    { hid: 'description', name: 'description', content: 'Modern Payroll Management System' },
    { name: 'format-detection', content: 'telephone=no' }
  ],
  link: [
    { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
    { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
    { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: true },
    { href: 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', rel: 'stylesheet' }
  ]
})

// Initialize app
onMounted(async () => {
  // Check for saved theme preference
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }

  // Check for authentication status
  const auth = useAuth()
  if (process.client) {
    await auth.checkAuthStatus()
  }
})
</script>

<style>
/* Global styles are imported in assets/css/main.css */
</style>