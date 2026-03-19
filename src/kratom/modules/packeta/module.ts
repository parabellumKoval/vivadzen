import { defineNuxtModule, addPlugin, createResolver, addImportsDir, addTemplate } from '@nuxt/kit'


export interface PacketaModuleOptions {
  /** Публичный API‑ключ виджета Packeta (для карты). */
  widgetApiKey?: string
  /** Язык виджета: 'cs' | 'sk' | 'hu' | 'pl' | 'ro' | 'en' | ... */
  language?: string
  /** Страна для фокуса карты по умолчанию (ISO2), напр. 'CZ'. */
  defaultCountry?: string
  /** Ограничение виджета по перевозчикам (например, только сеть Packeta). */
  carriers?: string[]
}


export default defineNuxtModule<PacketaModuleOptions>({
  meta: { name: 'packeta', configKey: 'packeta' },
  defaults: {
  widgetApiKey: process.env.PACKETA_WIDGET_API_KEY || '',
  language: 'en',
  defaultCountry: 'CZ',
  carriers: ['packeta']
},

setup (options, nuxt) {
const resolver = createResolver(import.meta.url)


// Пробрасываем конфиг в runtimeConfig.public
nuxt.options.runtimeConfig.public.packeta = {
  ...(nuxt.options.runtimeConfig.public.packeta || {}),
  apiKey: options.widgetApiKey || '',
  language: options.language || 'en',
  defaultCountry: options.defaultCountry || 'CZ',
  carriers: options.carriers || ['packeta']
}


// Плагин загрузки виджета
addPlugin({
src: resolver.resolve('runtime/plugins/packeta.client'),
mode: 'client'
})


// Авто‑импорт композаблов
addImportsDir(resolver.resolve('runtime/composables'))


// Типы (для DX)
addTemplate({
filename: 'types/packeta.d.ts',
getContents: () => `
export * from '${resolver.resolve('runtime/types')}'
`
})


// Включаем типы
nuxt.hook('prepare:types', (ctx) => {
ctx.tsConfig.compilerOptions = ctx.tsConfig.compilerOptions || {}
ctx.tsConfig.compilerOptions.paths = ctx.tsConfig.compilerOptions.paths || {}
ctx.tsConfig.compilerOptions.paths['#packeta-types'] = ['.nuxt/types/packeta.d.ts']
})
}
})