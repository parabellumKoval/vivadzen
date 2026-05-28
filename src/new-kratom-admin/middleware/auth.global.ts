export default defineNuxtRouteMiddleware((to) => {
  const auth = useAuthStore()
  const isLogin = to.path === '/login'

  if (!auth.isAuthenticated && !isLogin) {
    return navigateTo('/login')
  }
  if (auth.isAuthenticated && isLogin) {
    return navigateTo('/')
  }
})
