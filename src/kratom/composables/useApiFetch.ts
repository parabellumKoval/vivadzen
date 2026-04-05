// composables/useApiFetch.ts
import { isRef, unref, isReactive, toRaw } from 'vue'
import { resolveApiRequestUrl } from '~/utils/apiProxy'

type Options = { lazy?: boolean; server?: boolean }

// deep-unwrap reactive/refs → plain JS
function toPlain(input: any): any {
  const v = isRef(input) ? unref(input) : (isReactive(input) ? toRaw(input) : input)
  if (Array.isArray(v)) return v.map(toPlain)
  if (v && typeof v === 'object' && !(v instanceof Date) && !(v instanceof File) && !(v instanceof Blob)) {
    return Object.fromEntries(Object.entries(v).map(([k, val]) => [k, toPlain(val)]))
  }
  return v
}

// object → Record<string, string | string[]> с bracket-нотацией; массивы как key[]=a&key[]=b
function toBracketQuery(obj: Record<string, any>): Record<string, string | string[]> {
  const out: Record<string, string | string[]> = {}
  const push = (k: string, val: any) => {
    const str = val == null ? '' : String(val)
    if (k in out) {
      const existing = out[k]
      if (Array.isArray(existing)) {
        existing.push(str)
      } else {
        out[k] = [existing, str]
      }
    } else {
      out[k] = str
    }
  }
  const walk = (val: any, path: string[]) => {
    if (val == null) return
    if (Array.isArray(val)) {
      // массив → key[]=
      const base = path[0] + path.slice(1).map(p => `[${p}]`).join('')
      const key = `${base}[]`
      for (const item of val) {
        if (typeof item === 'object' && !(item instanceof Date) && !(item instanceof File) && !(item instanceof Blob)) {
          // массив объектов → key[][a]=... (поддержка сложных фильтров)
          const idxKey = key
          // для объектов создадим псевдо-группу: key[][sub]=...
          Object.entries(item).forEach(([k, v]) => walk(v, [idxKey, k]))
        } else {
          push(key, item)
        }
      }
      return
    }
    if (typeof val === 'object' && !(val instanceof Date) && !(val instanceof File) && !(val instanceof Blob)) {
      Object.entries(val).forEach(([k, v]) => walk(v, [...path, k]))
      return
    }
    const key = path[0] + path.slice(1).map(p => `[${p}]`).join('')
    push(key, val)
  }
  Object.entries(obj || {}).forEach(([k, v]) => walk(v, [k]))
  return out
}

/**
 * Совместимая обёртка поверх $api:
 *   useApiFetch(url, body, method, options)
 */
export const useApiFetch = async (
  url: string,
  body: Record<string, any> | null = null,
  method: string = 'GET',
  options: Options = {}
) => {
  const { $api } = useNuxtApp()
  const runtimeConfig = useRuntimeConfig()
  const { regionAlias } = useRegion()
  const locale = useNuxtApp().$i18n?.locale
  const storefrontCode = String(runtimeConfig.public.storefrontCode || 'kratom').trim()
  const m = (method || 'GET').toUpperCase() as 'GET'|'POST'|'PUT'|'PATCH'|'DELETE'
  const requestUrl = resolveApiRequestUrl(url, runtimeConfig.public.apiBase, 'api-client')

  // deep-unwrap перед сериализацией
  const plain = body ? toPlain(body) : null

  const req: {
    method: typeof m
    query?: Record<string, string | string[]>
    body?: any
  } = { method: m }

  if (m === 'GET') {
    // собираем query с bracket-нотацией — плагин НЕ будет его пересобирать
    const query = plain ? toBracketQuery(plain) : undefined
    if (query && Object.keys(query).length > 0) {
      req.query = query
    }
  } else if (plain !== null && plain !== undefined) {
    req.body = plain // плагин только добавит country, не ломая FormData
  }

  const key = `${m}:${requestUrl}:${JSON.stringify({
    q: plain ? Object.keys(plain).sort().map(k => [k, plain[k]]) : null,
    b: m === 'GET' ? 0 : (plain ? 1 : 0),
    r: regionAlias?.value || '',
    l: locale?.value || '',
    s: storefrontCode,
  })}`

  const asyncData = useAsyncData(
    key,
    () => $api(requestUrl, req),
    { lazy: !!options.lazy, server: options.server ?? true }
  )

  return options.lazy ? asyncData : await asyncData
}
