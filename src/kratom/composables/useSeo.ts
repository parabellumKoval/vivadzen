export const useSeo = () => {
  const {t} = useI18n({useScope: 'global'})
  const runtimeConfig = useRuntimeConfig()
  const siteUrl = String(runtimeConfig.public.site?.url || runtimeConfig.public.siteUrl || '').replace(/\/+$/, '')

  type SeoParamValue = string | number | null | undefined
  type SeoParams = Record<string, SeoParamValue>
  type SeoValue = string | null | undefined | (() => string | null | undefined)

  type SeoOptions = {
    params?: SeoParams | (() => SeoParams)
    title?: SeoValue
    description?: SeoValue
    fallbackTitle?: SeoValue
    fallbackDescription?: SeoValue
    robots?: string
    image?: SeoValue
    ogType?: string
  }

  const resolveValue = (value: SeoValue) => {
    return typeof value === 'function' ? value() : value
  }

  const normalizeSeoText = (value: unknown, maxLength?: number) => {
    const text = String(value || '')
      .replace(/\\r\\n|\\n|\\r/g, ' ')
      .replace(/<[^>]*>/g, ' ')
      .replace(/&nbsp;/gi, ' ')
      .replace(/&amp;/gi, '&')
      .replace(/&quot;/gi, '"')
      .replace(/&#39;|&apos;/gi, "'")
      .replace(/&lt;/gi, '<')
      .replace(/&gt;/gi, '>')
      .replace(/[ \t\r\n]+/g, ' ')
      .trim()

    if (!maxLength || text.length <= maxLength) {
      return text
    }

    return text
      .slice(0, maxLength - 1)
      .replace(/\s+\S*$/, '')
      .trim()
  }

  const translateIfExists = (key: string, params: SeoParams) => {
    const value = t(key, params)
    return value === key ? '' : value
  }

  const resolveParams = (options: SeoOptions) => {
    return typeof options.params === 'function' ? options.params() : (options.params || {})
  }

  const resolveAbsoluteUrl = (value: string) => {
    if (!value || /^https?:\/\//i.test(value)) {
      return value
    }

    if (value.startsWith('//')) {
      return `https:${value}`
    }

    if (siteUrl && value.startsWith('/')) {
      return `${siteUrl}${value}`
    }

    return value
  }

  const setPageSeo = (pageKey: string, options: SeoOptions = {}) => {
    useHead(() => {
      const params = resolveParams(options)
      const legacyFallbackTitle = pageKey
      const fallbackTitle = normalizeSeoText(resolveValue(options.fallbackTitle) || legacyFallbackTitle)
      const title = normalizeSeoText(
        resolveValue(options.title)
          || translateIfExists(`seo.pages.${pageKey}.title`, params)
          || t('seo.page.title', {title: fallbackTitle}),
        70,
      )
      const description = normalizeSeoText(
        resolveValue(options.description)
          || translateIfExists(`seo.pages.${pageKey}.description`, params)
          || resolveValue(options.fallbackDescription)
          || t('seo.page.desc', {title: fallbackTitle}),
        165,
      )
      const image = resolveAbsoluteUrl(normalizeSeoText(resolveValue(options.image)))
      const meta = [
        {
          name: 'description',
          content: description,
        },
        {
          property: 'og:title',
          content: title,
        },
        {
          property: 'og:description',
          content: description,
        },
        {
          property: 'og:type',
          content: options.ogType || 'website',
        },
        {
          name: 'twitter:card',
          content: image ? 'summary_large_image' : 'summary',
        },
        {
          name: 'twitter:title',
          content: title,
        },
        {
          name: 'twitter:description',
          content: description,
        },
      ]

      if (options.robots) {
        meta.push({
          name: 'robots',
          content: options.robots,
        } as any)
      }

      if (image) {
        meta.push(
          {
            property: 'og:image',
            content: image,
          } as any,
          {
            name: 'twitter:image',
            content: image,
          } as any,
        )
      }

      return {
        title,
        meta,
      }
    })
  }

  return {
    setPageSeo,
    normalizeSeoText,
  }
}
