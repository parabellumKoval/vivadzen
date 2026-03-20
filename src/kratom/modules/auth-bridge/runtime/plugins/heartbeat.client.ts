import { defineNuxtPlugin } from '#app'

export default defineNuxtPlugin(() => {
  const { public: { authBridge } } = useRuntimeConfig() as any
  const { me } = useAuth()
  const tick = () => me().catch(() => {})

  window.addEventListener('focus', tick)
  const ms = authBridge?.heartbeat?.intervalMs ?? 5 * 60 * 1000
  setInterval(tick, ms)
})
