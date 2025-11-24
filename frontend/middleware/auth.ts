export default defineNuxtRouteMiddleware((to) => {
  const auth = useAuth()

  // Check if user is authenticated
  if (!auth.isAuthenticated) {
    // Redirect to login page
    return navigateTo('/login')
  }

  // Optional: Check if user has permission to access this route
  // This would require defining route permissions somewhere
  // You could add route meta fields like: { meta: { permission: 'dashboard:read' } }
  if (to.meta.permission && !auth.hasPermission(to.meta.permission as string)) {
    throw createError({
      statusCode: 403,
      statusMessage: 'Insufficient permissions'
    })
  }
})