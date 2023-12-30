export const useToLocalePath = () => {
  return (path: string) => {
    const lc = useNuxtApp().$i18n.locale.value === useNuxtApp().$i18n.fallbackLocale.value ? '' : '/' + useNuxtApp().$i18n.locale.value
    return lc + path
  }
}