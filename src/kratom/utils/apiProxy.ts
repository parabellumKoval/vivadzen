const INTERNAL_API_PROXY_BASE = '/api/_backend'

const trimTrailingSlash = (value: string) => value.replace(/\/+$/, '')

const looksLikeAbsoluteUrl = (value: string) => /^https?:\/\//i.test(value)

export const getInternalApiProxyBase = () => INTERNAL_API_PROXY_BASE

type ResolveMode = 'fetch' | 'api-client'

const asApiClientPath = (value: string) => {
  if (!value || value === '/') {
    return '/'
  }
  return value.startsWith('/') ? value : `/${value}`
}

const buildResolvedValue = (suffix: string, mode: ResolveMode) => {
  const normalizedSuffix = asApiClientPath(suffix || '/')
  if (mode === 'api-client') {
    return normalizedSuffix
  }
  return `${INTERNAL_API_PROXY_BASE}${normalizedSuffix}`
}

export const resolveApiRequestUrl = (url: string, apiBase: string = '', mode: ResolveMode = 'fetch') => {
  if (!url || typeof url !== 'string') {
    return url
  }

  if (!process.client) {
    return url
  }

  if (url.startsWith(INTERNAL_API_PROXY_BASE)) {
    const suffix = url.slice(INTERNAL_API_PROXY_BASE.length)
    return buildResolvedValue(suffix, mode)
  }

  const normalizedApiBase = trimTrailingSlash(String(apiBase || ''))

  if (normalizedApiBase && url.startsWith(normalizedApiBase)) {
    const suffix = url.slice(normalizedApiBase.length)
    return buildResolvedValue(suffix || '/', mode)
  }

  if (looksLikeAbsoluteUrl(url)) {
    try {
      const parsed = new URL(url)
      if (parsed.pathname === '/api' || parsed.pathname.startsWith('/api/')) {
        const suffix = `${parsed.pathname.slice('/api'.length) || '/'}${parsed.search || ''}`
        return buildResolvedValue(suffix, mode)
      }
    } catch {
      return url
    }
  }

  return url
}
