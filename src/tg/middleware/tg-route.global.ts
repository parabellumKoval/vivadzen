export default defineNuxtRouteMiddleware((to) => {
  const config = useRuntimeConfig()
  const tgConfig = config.public.tg as any
  const segments = to.path.split('/').filter(Boolean)

  const fallbackRegion = tgConfig?.fallbackRegion || 'cz'
  const regions = tgConfig?.regions || {}
  const localesByRegion = tgConfig?.localesByRegion || {}

  if (!segments.length) {
    const locale = regions[fallbackRegion]?.locale || localesByRegion[fallbackRegion]?.[0] || 'cs'
    return navigateTo(`/${fallbackRegion}/${locale}`, { replace: true })
  }

  const region = String(segments[0] || fallbackRegion).toLowerCase()

  if (!regions[region]) {
    const locale = regions[fallbackRegion]?.locale || localesByRegion[fallbackRegion]?.[0] || 'cs'
    return navigateTo(`/${fallbackRegion}/${locale}`, { replace: true })
  }

  if (segments.length === 1) {
    const locale = regions[region]?.locale || localesByRegion[region]?.[0] || 'en'
    return navigateTo(`/${region}/${locale}`, { replace: true })
  }

  const requestedLocale = String(segments[1] || '').toLowerCase()
  const allowedLocales = localesByRegion[region] || [regions[region]?.locale || 'en']

  if (!allowedLocales.includes(requestedLocale)) {
    segments[1] = regions[region]?.locale || allowedLocales[0] || 'en'
    return navigateTo(`/${segments.join('/')}`, { replace: true })
  }
})
