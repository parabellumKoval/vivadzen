import { messages } from '~/lang'
import type { TgLocaleMessages } from '~/lang/types'

const dictionaries = messages as Record<string, TgLocaleMessages>

export const useTgI18n = () => {
  const { locale } = useTgRouting()

  const t = (key: string, params: Record<string, string | number> = {}) => {
    const dictionary = dictionaries[locale.value] || messages.en
    let text = dictionary[key] || messages.en[key] || key

    for (const [param, value] of Object.entries(params)) {
      text = text.replaceAll(`{${param}}`, String(value))
    }

    return text
  }

  return {
    t,
    locale
  }
}
