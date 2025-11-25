export default defineNuxtRouteMiddleware(() => {
  const auth = useAuth()

  // If user is already authenticated, redirect to dashboard (access computed ref value)
  if (auth.isAuthenticated.value) {
    console.log('Guest middleware: User authenticated, redirecting to dashboard')
    return navigateTo('/dashboard')
  }
  
  console.log('Guest middleware: Not authenticated, allowing access to login')
})