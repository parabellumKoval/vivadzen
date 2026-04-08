export const useTelegram = () => {
  const nuxtApp = useNuxtApp()

  const webApp = computed(() => {
    if (process.server) return null
    return nuxtApp.$tg || (window as any).Telegram?.WebApp || null
  })

  const isTelegram = computed(() => Boolean(webApp.value?.initData))

  const user = computed(() => webApp.value?.initDataUnsafe?.user || null)

  const haptic = (type: 'light' | 'medium' | 'success' | 'error' | 'selection' = 'light') => {
    if (!isTelegram.value) return

    try {
      if (type === 'success' || type === 'error') {
        webApp.value.HapticFeedback.notificationOccurred(type)
      } else if (type === 'selection') {
        webApp.value.HapticFeedback.selectionChanged()
      } else {
        webApp.value.HapticFeedback.impactOccurred(type)
      }
    } catch {
      // Telegram SDK is optional in a regular browser.
    }
  }

  const showAlert = (message: string) => {
    if (!isTelegram.value) return

    try {
      webApp.value.showAlert(message)
    } catch {
      // ignore outside Telegram
    }
  }

  return {
    webApp,
    isTelegram,
    user,
    haptic,
    showAlert
  }
}
