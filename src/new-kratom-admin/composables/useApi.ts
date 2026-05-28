import { ofetch, type $Fetch } from 'ofetch'
import { useAuthStore } from '~/stores/auth'

let cached: $Fetch | null = null

/**
 * Один централизованный $fetch инстанс с baseURL и Bearer-токеном.
 * Автоматически делает logout при 401 (токен протух или невалиден).
 */
export function useApi(): $Fetch {
  if (cached) return cached

  const config = useRuntimeConfig()

  cached = ofetch.create({
    baseURL: config.public.apiBase,
    onRequest({ options }) {
      const auth = useAuthStore()
      if (auth.token) {
        options.headers = new Headers(options.headers)
        options.headers.set('Authorization', `Bearer ${auth.token}`)
        options.headers.set('Accept', 'application/json')
      }
    },
    onResponseError({ response }) {
      if (response.status === 401) {
        const auth = useAuthStore()
        auth.clear()
        if (typeof window !== 'undefined' && !window.location.pathname.endsWith('/login')) {
          navigateTo('/login')
        }
      }
    },
  })

  return cached
}
