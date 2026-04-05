import { createHash } from 'node:crypto'
import { ofetch } from 'ofetch'
import type { FetchError } from 'ofetch'
import { joinURL } from 'ufo'
import { getHeader, type H3Event } from 'h3'
import { useRuntimeConfig, useStorage } from '#imports'

type MaybeString = string | null | undefined

interface CategoryModuleRuntimeOptions {
  baseUrl?: string
  slugsEndpoint?: string
  detailsEndpoint?: string
  listEndpoint?: string
  mainListEndpoint?: string
  enableTtl?: boolean
  ttlSec?: number
  languages?: string[]
  regions?: string[]
}

interface ContextDescriptor {
  locale: MaybeString
  region: MaybeString
  storefront: string
  key: string
}

interface ContextualSlugsEntry {
  slugs: string[]
  fetchedAt: number
}

interface SlugsCacheEntry {
  aggregated: string[]
  fetchedAt: number
  perContext: Record<string, ContextualSlugsEntry>
  contextSignature: string
}

interface ContextualCategoryEntry<T = unknown> {
  payload: T | null
  fetchedAt: number
}

interface CategoryDetailsCacheEntry<T = unknown> {
  slug: string
  fetchedAt: number
  perContext: Record<string, ContextualCategoryEntry<T>>
  contextSignature: string
}

interface CategoryListCacheEntry<T = unknown> {
  payload: T
  fetchedAt: number
  contextSignature: string
}

const STORAGE_NAMESPACE = 'cache'
const SLUGS_STORAGE_KEY = 'category:slugs'
const DETAILS_STORAGE_PREFIX = 'category:details:'
const LIST_STORAGE_PREFIX_V2 = 'category:list-v2:'

const FALLBACK_SLUGS_ENDPOINT = '/djini-category/slugs-simple'
const FALLBACK_DETAILS_ENDPOINT = '/category_cached/:slug'
const FALLBACK_LIST_ENDPOINT = '/category'
const FALLBACK_MAIN_LIST_ENDPOINT = '/category/main'

const now = () => Date.now()

function isNotFoundError(error: unknown) {
  if (!error || typeof error !== 'object') {
    return false
  }

  const fetchError = error as FetchError & {
    statusCode?: number
    status?: number
  }

  const status = fetchError.statusCode ?? fetchError.status ?? fetchError.response?.status
  return status === 404
}

function storage() {
  const storageInstance = useStorage(STORAGE_NAMESPACE)
  return storageInstance
}

function detailsStorageKey(slug: string) {
  return `${DETAILS_STORAGE_PREFIX}${slug}`
}

function readModuleOptions(): Required<CategoryModuleRuntimeOptions> & {
  enableTtl: boolean
  ttlMs: number
} {
  const config = useRuntimeConfig()
  const moduleConfig = (config.categoryModule || {}) as CategoryModuleRuntimeOptions
  const enableTtl = moduleConfig.enableTtl !== false
  const ttlSec =
    typeof moduleConfig.ttlSec === 'number' && !Number.isNaN(moduleConfig.ttlSec)
      ? moduleConfig.ttlSec
      : 600
  const languages = Array.isArray(moduleConfig.languages) ? moduleConfig.languages : []
  const regions = Array.isArray(moduleConfig.regions) ? moduleConfig.regions : []

  return {
    baseUrl: moduleConfig.baseUrl || (config.public?.apiBase as string | undefined) || '',
    slugsEndpoint: moduleConfig.slugsEndpoint || FALLBACK_SLUGS_ENDPOINT,
    detailsEndpoint: moduleConfig.detailsEndpoint || FALLBACK_DETAILS_ENDPOINT,
    listEndpoint: moduleConfig.listEndpoint || FALLBACK_LIST_ENDPOINT,
    mainListEndpoint: moduleConfig.mainListEndpoint || FALLBACK_MAIN_LIST_ENDPOINT,
    enableTtl,
    ttlSec,
    ttlMs: enableTtl && ttlSec > 0 ? ttlSec * 1000 : 0,
    languages,
    regions
  }
}

function resolveStorefrontCode() {
  const config = useRuntimeConfig()
  return String(config.public?.storefrontCode || 'kratom').trim()
}

function assertBaseUrl(baseUrl: MaybeString) {
  if (!baseUrl) {
    throw new Error(
      'categoryModule.baseUrl is not configured and runtimeConfig.public.apiBase is empty'
    )
  }
  return baseUrl
}

function buildContextKey(locale: MaybeString, region: MaybeString, storefront: string) {
  return `${locale ?? ''}::${region ?? ''}::${storefront}`
}

function collectContexts(): ContextDescriptor[] {
  const { languages, regions } = readModuleOptions()
  const storefront = resolveStorefrontCode()
  const languageList = languages.length ? languages : [null]
  const regionList = regions.length ? regions : [null]

  return languageList.flatMap((locale) =>
    regionList.map((region) => ({
      locale: locale ?? null,
      region: region ?? null,
      storefront,
      key: buildContextKey(locale, region, storefront)
    }))
  )
}

function makeContextSignature(contexts: ContextDescriptor[]) {
  return contexts.map((ctx) => ctx.key).join('|')
}

function getContextsSnapshot() {
  const contexts = collectContexts()
  return {
    contexts,
    signature: makeContextSignature(contexts)
  }
}

function isFresh(entryTimestamp: number | undefined, ttlMs: number, enableTtl: boolean) {
  if (!entryTimestamp) return false
  if (!enableTtl) return true
  if (ttlMs <= 0) return false
  return now() - entryTimestamp < ttlMs
}

async function readSlugsEntry(): Promise<SlugsCacheEntry | null> {
  const entry = await storage().getItem<SlugsCacheEntry>(SLUGS_STORAGE_KEY)
  return entry
}

async function writeSlugsEntry(entry: SlugsCacheEntry) {
  await storage().setItem(SLUGS_STORAGE_KEY, entry)
}

async function readCategoryEntry(slug: string): Promise<CategoryDetailsCacheEntry | null> {
  const entry = await storage().getItem<CategoryDetailsCacheEntry>(detailsStorageKey(slug))
  return entry
}

async function writeCategoryEntry(slug: string, entry: CategoryDetailsCacheEntry) {
  await storage().setItem(detailsStorageKey(slug), entry)
}

function resolveBaseUrl() {
  const { baseUrl } = readModuleOptions()
  return assertBaseUrl(baseUrl)
}

function resolveSlugsUrl() {
  const { slugsEndpoint } = readModuleOptions()
  return joinURL(resolveBaseUrl(), slugsEndpoint || '')
}

function resolveDetailsUrl(slug: string) {
  const { detailsEndpoint } = readModuleOptions()
  const endpoint = (detailsEndpoint || '').replace(':slug', encodeURIComponent(slug))
  return joinURL(resolveBaseUrl(), endpoint)
}

function resolveListUrl() {
  const { listEndpoint } = readModuleOptions()
  return joinURL(resolveBaseUrl(), listEndpoint || '')
}

function resolveMainListUrl() {
  const { mainListEndpoint } = readModuleOptions()
  return joinURL(resolveBaseUrl(), mainListEndpoint || '')
}

async function fetchSlugsForContext(context: ContextDescriptor) {
  const url = resolveSlugsUrl()
  const headers: Record<string, string> = {
    Accept: 'application/json',
    'X-Storefront': context.storefront,
  }
  if (context.locale) headers['Accept-Language'] = context.locale
  if (context.region) headers['X-Region'] = context.region
  const payload = await ofetch<string[]>(url, { retry: 0, headers })
  return Array.isArray(payload) ? payload : []
}

async function fetchCategoryDetailsForContext(slug: string, context: ContextDescriptor) {
  const url = resolveDetailsUrl(slug)
  const headers: Record<string, string> = {
    Accept: 'application/json',
    'X-Storefront': context.storefront,
  }
  if (context.locale) headers['Accept-Language'] = context.locale
  if (context.region) headers['X-Region'] = context.region
  return await ofetch(url, { retry: 0, headers })
}

async function fetchCategoryListForContext(
  context: ContextDescriptor,
  query: Record<string, any> | undefined
) {
  const url = resolveListUrl()
  const headers: Record<string, string> = {
    Accept: 'application/json',
    'X-Storefront': context.storefront,
  }
  if (context.locale) headers['Accept-Language'] = context.locale
  if (context.region) headers['X-Region'] = context.region
  const requestQuery = {
    ...(query || {}),
    storefront: context.storefront,
  }
  return await ofetch(url, { retry: 0, headers, query: requestQuery })
}

async function fetchCategoryMainListForContext(
  context: ContextDescriptor
) {
  const url = resolveMainListUrl()
  const headers: Record<string, string> = {
    Accept: 'application/json',
    'X-Storefront': context.storefront,
  }
  if (context.locale) headers['Accept-Language'] = context.locale
  if (context.region) headers['X-Region'] = context.region
  return await ofetch(url, { retry: 0, headers })
}

function cloneQueryObject<T extends Record<string, any> | undefined | null>(value: T) {
  if (!value) return undefined
  const hasKeys = Object.keys(value).length > 0
  const cloned = JSON.parse(JSON.stringify(value))
  if (!hasKeys || (cloned && typeof cloned === 'object' && Object.keys(cloned).length === 0)) {
    return undefined
  }
  return cloned as Record<string, any>
}

function normalizeQueryValue(value: any): any {
  if (Array.isArray(value)) {
    return value.map((item) => normalizeQueryValue(item))
  }
  if (value && typeof value === 'object') {
    return Object.keys(value)
      .sort()
      .reduce<Record<string, any>>((acc, key) => {
        acc[key] = normalizeQueryValue(value[key])
        return acc
      }, {})
  }
  return value
}

function hashQueryObject(query: Record<string, any> | undefined) {
  if (!query || Object.keys(query).length === 0) return 'default'
  const normalized = normalizeQueryValue(query)
  const json = JSON.stringify(normalized)
  return createHash('sha1').update(json).digest('hex')
}

function listStorageKey(contextKey: string, queryHash: string) {
  const safeKey = createHash('sha1')
    .update(`${contextKey}::${queryHash}`)
    .digest('hex')

  return `${LIST_STORAGE_PREFIX_V2}${safeKey}`
}

async function readListEntry(
  contextKey: string,
  queryHash: string
): Promise<CategoryListCacheEntry | null> {
  return await storage().getItem<CategoryListCacheEntry>(listStorageKey(contextKey, queryHash))
}

async function writeListEntry(
  contextKey: string,
  queryHash: string,
  entry: CategoryListCacheEntry
) {
  await storage().setItem(listStorageKey(contextKey, queryHash), entry)
}

let slugsInflight: Promise<SlugsCacheEntry> | null = null
const categoryInflight = new Map<string, Promise<CategoryDetailsCacheEntry>>()
const listInflight = new Map<string, Promise<CategoryListCacheEntry>>()

async function fetchAndStoreSlugs(): Promise<SlugsCacheEntry> {
  if (!slugsInflight) {
    const request = (async () => {
      const { contexts, signature } = getContextsSnapshot()
      const aggregated = new Set<string>()
      const perContext: SlugsCacheEntry['perContext'] = {}

      for (const context of contexts) {
        try {
          const slugs = await fetchSlugsForContext(context)
          perContext[context.key] = { slugs, fetchedAt: now() }
          for (const slug of slugs) {
            aggregated.add(String(slug))
          }
        } catch (error) {
          console.error('[category-module] Failed to fetch slugs for context', context, error)
          throw error
        }
      }

      const entry: SlugsCacheEntry = {
        aggregated: Array.from(aggregated),
        fetchedAt: now(),
        perContext,
        contextSignature: signature
      }
      await writeSlugsEntry(entry)
      return entry
    })()

    slugsInflight = request
    request.finally(() => {
      if (slugsInflight === request) {
        slugsInflight = null
      }
    })
  }

  return slugsInflight
}

export async function refreshCategorySlugs(): Promise<SlugsCacheEntry> {
  return await fetchAndStoreSlugs()
}

function ensureSlugsEntryFresh(
  entry: SlugsCacheEntry | null,
  signature: string,
  ttlMs: number,
  enableTtl: boolean
) {
  if (!entry) return false
  if (entry.contextSignature !== signature) return false
  if (!enableTtl) {
    return entry.aggregated.length > 0
  }
  if (ttlMs <= 0) return false
  return isFresh(entry.fetchedAt, ttlMs, enableTtl)
}

export async function getCategorySlugs(options: { force?: boolean } = {}) {
  const { contexts, signature } = getContextsSnapshot()
  const { ttlMs, enableTtl } = readModuleOptions()
  if (!options.force) {
    const cached = await readSlugsEntry()
    if (ensureSlugsEntryFresh(cached, signature, ttlMs, enableTtl) && cached) {
      return new Set(cached.aggregated)
    }
    if (cached) {
      void fetchAndStoreSlugs().catch((error) => {
        console.error('[category-module] background slugs refresh failed', error)
      })
      return new Set(cached.aggregated)
    }
  }

  const entry = await fetchAndStoreSlugs()
  return new Set(entry.aggregated)
}

function resolveRequestedContext(
  requestedLocale: MaybeString,
  requestedRegion: MaybeString,
  requestedStorefront: MaybeString,
  contexts: ContextDescriptor[]
) {
  const normalizedLocale = (requestedLocale || '').toLowerCase()
  const normalizedRegion = (requestedRegion || '').toLowerCase()
  const normalizedStorefront = (requestedStorefront || '').toLowerCase()

  const exact =
    contexts.find((ctx) => {
      const localeMatch =
        !normalizedLocale ||
        (ctx.locale || '').toLowerCase() === normalizedLocale ||
        (ctx.locale || '').toLowerCase() === normalizedLocale.split('-')[0]
      const regionMatch =
        !normalizedRegion || (ctx.region || '').toLowerCase() === normalizedRegion
      const storefrontMatch =
        !normalizedStorefront || ctx.storefront.toLowerCase() === normalizedStorefront
      return localeMatch && regionMatch && storefrontMatch
    }) || null

  return exact || contexts[0] || null
}

async function fetchAndStoreCategory(slug: string): Promise<CategoryDetailsCacheEntry> {
  let inflight = categoryInflight.get(slug)
  if (!inflight) {
    const request = (async () => {
      const { contexts, signature } = getContextsSnapshot()
      const perContext: CategoryDetailsCacheEntry['perContext'] = {}

      for (const context of contexts) {
        try {
          const payload = await fetchCategoryDetailsForContext(slug, context)
          perContext[context.key] = { payload, fetchedAt: now() }
        } catch (error) {
          if (isNotFoundError(error)) {
            console.warn(
              '[category-module] Category is missing for context, storing null payload',
              { slug, context }
            )
            perContext[context.key] = { payload: null, fetchedAt: now() }
            continue
          }

          console.error(
            '[category-module] Failed to fetch category details',
            { slug, context },
            error
          )
          throw error
        }
      }

      const entry: CategoryDetailsCacheEntry = {
        slug,
        fetchedAt: now(),
        perContext,
        contextSignature: signature
      }

      await writeCategoryEntry(slug, entry)
      return entry
    })()

    inflight = request
    categoryInflight.set(slug, request)
    request.finally(() => {
      if (categoryInflight.get(slug) === request) {
        categoryInflight.delete(slug)
      }
    })
  }

  return inflight
}

async function fetchAndStoreCategoryList(
  context: ContextDescriptor,
  query: Record<string, any> | undefined,
  queryHash: string,
  signature: string
): Promise<CategoryListCacheEntry> {
  const inflightKey = `${context.key}::${queryHash}`
  let inflight = listInflight.get(inflightKey)
  if (!inflight) {
    const request = (async () => {
      const payload = await fetchCategoryListForContext(context, query)
      const entry: CategoryListCacheEntry = {
        payload,
        fetchedAt: now(),
        contextSignature: signature
      }
      await writeListEntry(context.key, queryHash, entry)
      return entry
    })()

    listInflight.set(inflightKey, request)
    request.finally(() => {
      if (listInflight.get(inflightKey) === request) {
        listInflight.delete(inflightKey)
      }
    })
    inflight = request
  }

  return inflight
}

function isCategoryEntryFresh(
  entry: CategoryDetailsCacheEntry | null,
  signature: string,
  contextKey: string,
  ttlMs: number,
  enableTtl: boolean
) {
  if (!entry) return false
  if (entry.contextSignature !== signature) return false
  const contextEntry = entry.perContext[contextKey]
  if (!contextEntry) return false
  return isFresh(contextEntry.fetchedAt, ttlMs, enableTtl)
}

function isListEntryFresh(
  entry: CategoryListCacheEntry | null,
  signature: string,
  ttlMs: number,
  enableTtl: boolean
) {
  if (!entry) return false
  if (entry.contextSignature !== signature) return false
  return isFresh(entry.fetchedAt, ttlMs, enableTtl)
}

export async function refreshCategory(slug: string): Promise<CategoryDetailsCacheEntry> {
  return await fetchAndStoreCategory(slug)
}

export async function refreshCategoryList(query?: Record<string, any>) {
  const { contexts, signature } = getContextsSnapshot()
  const queryPayload = cloneQueryObject(query)
  const queryHash = hashQueryObject(queryPayload)
  const results = await Promise.all(
    contexts.map(async (context) => {
      const entry = await fetchAndStoreCategoryList(context, queryPayload, queryHash, signature)
      return {
        context: context.key,
        fetchedAt: entry.fetchedAt
      }
    })
  )
  return { contexts: results, queryHash }
}

export async function refreshCategoryMainList() {
  const { contexts, signature } = getContextsSnapshot()
  const queryHash = 'main'
  const results = await Promise.all(
    contexts.map(async (context) => {
      const payload = await fetchCategoryMainListForContext(context)
      const entry: CategoryListCacheEntry = {
        payload,
        fetchedAt: now(),
        contextSignature: signature
      }
      await writeListEntry(context.key, queryHash, entry)
      return {
        context: context.key,
        fetchedAt: entry.fetchedAt
      }
    })
  )
  return { contexts: results, queryHash }
}

export async function refreshAllCategories(): Promise<{ slugs: string[]; list: {
  contexts: Array<{ context: string; fetchedAt: number }>
  queryHash: string
} }> {
  const slugsSet = await getCategorySlugs({ force: true })
  const slugs = Array.from(slugsSet)
  for (const slug of slugs) {
    await refreshCategory(slug)
  }
  const list = await refreshCategoryList()
  return { slugs, list }
}

function parseAcceptLanguage(value: MaybeString) {
  if (!value) return null
  const first = value.split(',')[0]?.trim()
  return first || null
}

export async function getCategoryFromCache(
  slug: string,
  options: {
    locale?: MaybeString
    region?: MaybeString
    force?: boolean
    event?: H3Event
  } = {}
) {
  const { ttlMs, enableTtl } = readModuleOptions()
  const { contexts, signature } = getContextsSnapshot()
  if (!contexts.length) {
    throw new Error(
      'categoryModule.languages or categoryModule.regions must include at least one value'
    )
  }

  const event = options.event

  const headerLocale = event ? getHeader(event, 'accept-language') : undefined
  const locale =
    options.locale != null
      ? parseAcceptLanguage(options.locale) ?? options.locale
      : parseAcceptLanguage(headerLocale)
  const region =
    options.region != null ? options.region : event ? getHeader(event, 'x-region') : undefined
  const storefront = event ? getHeader(event, 'x-storefront') : undefined

  const selectedContext = resolveRequestedContext(locale, region, storefront, contexts)
  if (!selectedContext) {
    throw new Error('[category-module] Failed to resolve locale/region context')
  }

  if (!options.force) {
    const cached = await readCategoryEntry(slug)
    if (isCategoryEntryFresh(cached, signature, selectedContext.key, ttlMs, enableTtl) && cached) {
      return cached.perContext[selectedContext.key]?.payload
    }
    if (cached?.perContext[selectedContext.key]) {
      void fetchAndStoreCategory(slug).catch((error) => {
        console.error('[category-module] background category refresh failed', { slug }, error)
      })
      return cached.perContext[selectedContext.key]?.payload
    }
  }

  const entry = await fetchAndStoreCategory(slug)
  const contextEntry = entry.perContext[selectedContext.key]
  if (!contextEntry) {
    throw new Error(
      `[category-module] Category cache missing context ${selectedContext.key} for slug ${slug}`
    )
  }
  return contextEntry.payload
}

export async function getCategoryListFromCache(
  query: Record<string, any> | undefined,
  options: {
    locale?: MaybeString
    region?: MaybeString
    force?: boolean
    event?: H3Event
  } = {}
) {
  const { ttlMs, enableTtl } = readModuleOptions()
  const { contexts, signature } = getContextsSnapshot()
  if (!contexts.length) {
    throw new Error(
      'categoryModule.languages or categoryModule.regions must include at least one value'
    )
  }

  const event = options.event

  const headerLocale = event ? getHeader(event, 'accept-language') : undefined
  const locale =
    options.locale != null
      ? parseAcceptLanguage(options.locale) ?? options.locale
      : parseAcceptLanguage(headerLocale)
  const region =
    options.region != null ? options.region : event ? getHeader(event, 'x-region') : undefined
  const storefront = event ? getHeader(event, 'x-storefront') : undefined

  const selectedContext = resolveRequestedContext(locale, region, storefront, contexts)
  if (!selectedContext) {
    throw new Error('[category-module] Failed to resolve locale/region context for list cache')
  }

  const queryPayload = cloneQueryObject(query)
  const queryHash = hashQueryObject(queryPayload)

  if (!options.force) {
    const cached = await readListEntry(selectedContext.key, queryHash)
    if (isListEntryFresh(cached, signature, ttlMs, enableTtl) && cached) {
      return cached.payload
    }
    if (cached && cached.contextSignature === signature) {
      void fetchAndStoreCategoryList(selectedContext, queryPayload, queryHash, signature).catch(
        (error) => {
          console.error('[category-module] background category list refresh failed', error)
        }
      )
      return cached.payload
    }
  }

  const entry = await fetchAndStoreCategoryList(selectedContext, queryPayload, queryHash, signature)
  return entry.payload
}

export async function getCategoryMainListFromCache(
  options: {
    locale?: MaybeString
    region?: MaybeString
    force?: boolean
    event?: H3Event
  } = {}
) {
  const { contexts, signature } = getContextsSnapshot()
  if (!contexts.length) {
    throw new Error(
      'categoryModule.languages or categoryModule.regions must include at least one value'
    )
  }

  const event = options.event

  const headerLocale = event ? getHeader(event, 'accept-language') : undefined
  const locale =
    options.locale != null
      ? parseAcceptLanguage(options.locale) ?? options.locale
      : parseAcceptLanguage(headerLocale)
  const region =
    options.region != null ? options.region : event ? getHeader(event, 'x-region') : undefined
  const storefront = event ? getHeader(event, 'x-storefront') : undefined

  const selectedContext = resolveRequestedContext(locale, region, storefront, contexts)
  if (!selectedContext) {
    throw new Error('[category-module] Failed to resolve locale/region context for main list cache')
  }

  const queryHash = 'main'

  const { ttlMs, enableTtl } = readModuleOptions()

  if (!options.force) {
    const cached = await readListEntry(selectedContext.key, queryHash)
    if (isListEntryFresh(cached, signature, ttlMs, enableTtl) && cached) {
      return cached.payload
    }
    if (cached && cached.contextSignature === signature) {
      void (async () => {
        try {
          const payload = await fetchCategoryMainListForContext(selectedContext)
          const entry: CategoryListCacheEntry = {
            payload,
            fetchedAt: now(),
            contextSignature: signature
          }
          await writeListEntry(selectedContext.key, queryHash, entry)
        } catch (error) {
          console.error('[category-module] background main list refresh failed', error)
        }
      })()
      return cached.payload
    }
  }

  const payload = await fetchCategoryMainListForContext(selectedContext)
  const entry: CategoryListCacheEntry = {
    payload,
    fetchedAt: now(),
    contextSignature: signature
  }
  await writeListEntry(selectedContext.key, queryHash, entry)
  return entry.payload
}

export async function deleteCategoryFromCache(slug: string) {
  await storage().removeItem(detailsStorageKey(slug))
}
