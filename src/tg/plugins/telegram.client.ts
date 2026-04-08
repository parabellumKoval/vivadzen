import WebApp from '@twa-dev/sdk'

export default defineNuxtPlugin(() => {
  const setViewportHeight = () => {
    const height = WebApp?.viewportHeight || window.innerHeight
    document.documentElement.style.setProperty('--tg-viewport-height', `${height}px`)
  }

  try {
    WebApp.expand()
    WebApp.ready()
    WebApp.setHeaderColor('#ffffff')
    WebApp.setBackgroundColor('#fff7ed')
    WebApp.onEvent('viewportChanged', setViewportHeight)
    setViewportHeight()
  } catch {
    document.documentElement.style.setProperty('--tg-viewport-height', `${window.innerHeight}px`)
  }

  return {
    provide: {
      tg: WebApp
    }
  }
})
