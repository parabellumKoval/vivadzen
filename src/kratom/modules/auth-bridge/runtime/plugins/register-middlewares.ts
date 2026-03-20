import { defineNuxtPlugin } from '#app'
import { addRouteMiddleware, navigateTo } from '#app'

export default defineNuxtPlugin(() => {
  // защищённые страницы
  addRouteMiddleware('auth-bridge-auth', async (to) => {
    const { init, isAuthenticated } = useAuth()
    await init()
    if (!isAuthenticated.value) {
      return navigateTo({ path: '/auth/login', query: { redirect: to.fullPath } })
    }
  })

  // защищённые страницы с проверкой подтверждения email
  addRouteMiddleware('auth-bridge-auth-verified', async (to) => {
    const { init, isAuthenticated, user } = useAuth()
    await init()
    if (!isAuthenticated.value) {
      return navigateTo({ path: '/auth/login', query: { redirect: to.fullPath } })
    }
    
    // If email verification is required and email is not verified, redirect to verify page or show message
    if (!user.value?.email_verified_at) {
      return navigateTo({ path: '/auth/verify-email', query: { redirect: to.fullPath } })
    }
  })

  // гостевые страницы (login/register)
  addRouteMiddleware('auth-bridge-guest', async () => {
    const { init, isAuthenticated } = useAuth()
    await init()
    if (isAuthenticated.value) return navigateTo('/')
  })
})
