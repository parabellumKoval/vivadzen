/**
 * Возвращает абсолютный URL для медиа-файла, который лежит на бекенде Laravel.
 *
 *  - http(s)://… или protocol-relative //…  → возвращаем как есть (Bunny / внешний CDN).
 *  - /…                                      → префиксуем siteBase из runtimeConfig
 *                                              (по умолчанию http://localhost:8002).
 *  - пустая строка                           → пустая строка.
 *
 * Используется в админке: галерея товара, главное изображение, редактор контента.
 */
export function useMediaUrl() {
  const config = useRuntimeConfig()
  const siteBase = (config.public.siteBase as string)?.replace(/\/$/, '') || ''

  function resolve(url?: string | null): string {
    if (!url) return ''
    if (/^(https?:)?\/\//i.test(url)) return url
    if (url.startsWith('/')) return siteBase + url
    return siteBase + '/' + url
  }

  return { resolve, siteBase }
}
