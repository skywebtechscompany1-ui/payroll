export default defineNuxtRouteMiddleware(() => {
  const auth = useAuth()

  // If user is already authenticated, redirect to dashboard
  if (auth.isAuthenticated) {
    return navigateTo('/dashboard')
  }
})