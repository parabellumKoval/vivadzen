import { defineNuxtPlugin } from '#app'
import { addRouteMiddleware, navigateTo } from '#app'

export default defineNuxtPlugin(() => {
  const localePrefixFromPath = (value: string) => {
    const normalized = String(value || '/')
    const match = normalized.match(/^\/(en|ru|uk)(?=\/|$)/)
    return match ? `/${match[1]}` : ''
  }

  const withLocalePrefix = (basePath: string, sourcePath: string) => {
    const prefix = localePrefixFromPath(sourcePath)
    const normalizedBasePath = basePath.startsWith('/') ? basePath : `/${basePath}`
    return prefix ? `${prefix}${normalizedBasePath}` : normalizedBasePath
  }

  // защищённые страницы
  addRouteMiddleware('auth-bridge-auth', async (to) => {
    const { init, isAuthenticated } = useAuth()
    await init()
    if (!isAuthenticated.value) {
      return navigateTo({
        path: withLocalePrefix('/auth/login', to.fullPath),
        query: { redirect: to.fullPath },
      })
    }
  })

  // защищённые страницы с проверкой подтверждения email
  addRouteMiddleware('auth-bridge-auth-verified', async (to) => {
    const { init, isAuthenticated, user } = useAuth()
    await init()
    if (!isAuthenticated.value) {
      return navigateTo({
        path: withLocalePrefix('/auth/login', to.fullPath),
        query: { redirect: to.fullPath },
      })
    }
    
    // If email verification is required and email is not verified, redirect to verify page or show message
    if (!user.value?.email_verified_at) {
      return navigateTo({
        path: withLocalePrefix('/auth/verify-email', to.fullPath),
        query: { redirect: to.fullPath },
      })
    }
  })

  // гостевые страницы (login/register)
  addRouteMiddleware('auth-bridge-guest', async (to) => {
    const { init, isAuthenticated } = useAuth()
    await init()

    if (!isAuthenticated.value) {
      return
    }

    if (typeof to.query.redirect === 'string' && to.query.redirect.startsWith('/')) {
      return navigateTo(to.query.redirect)
    }

    return navigateTo(withLocalePrefix('/account/orders', to.fullPath))
  })
})
