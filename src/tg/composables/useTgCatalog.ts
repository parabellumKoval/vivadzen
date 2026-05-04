import type { TgCategory, TgProduct } from '~/types/tg'
import { useTgUiStore } from '~/stores/ui'

export const asArray = <T = any>(value: any): T[] => {
  if (Array.isArray(value)) return value
  if (Array.isArray(value?.data)) return value.data
  if (Array.isArray(value?.categories)) return value.categories
  if (Array.isArray(value?.categories?.data)) return value.categories.data
  return []
}

export const normalizeCatalog = (payload: any) => {
  const root = payload?.data?.products ? payload.data : payload
  const productsNode = root?.products || root
  const products = Array.isArray(productsNode?.data)
    ? productsNode.data
    : Array.isArray(root?.data)
      ? root.data
      : []

  return {
    products,
    meta: productsNode?.meta || root?.meta || {}
  }
}

export const normalizeCategoryId = (value: unknown) => {
  if (value === null || value === undefined || value === '') return null
  return String(value).trim()
}

export const normalizeCategorySlug = (value: unknown) => {
  const normalized = String(value || '').trim().toLowerCase()
  return normalized || null
}

export const getCategoryChildren = (category: TgCategory | null | undefined) => {
  return Array.isArray(category?.children) ? category.children : []
}

export const findCategoryInTree = (
  categories: TgCategory[],
  predicate: (category: TgCategory) => boolean
): TgCategory | null => {
  for (const category of categories) {
    if (predicate(category)) return category

    const nested = findCategoryInTree(getCategoryChildren(category), predicate)
    if (nested) return nested
  }

  return null
}

export const resolveScopedCategoryRoot = (
  categoryTree: TgCategory[],
  baseCategoryId: string | null,
  baseCategorySlug: string | null
) => {
  if (!baseCategoryId && !baseCategorySlug) return null

  return findCategoryInTree(categoryTree, (category) => {
    const categoryId = normalizeCategoryId(category.id)
    const categoryTreeSlug = normalizeCategorySlug(category.slug)

    return (baseCategoryId && categoryId === baseCategoryId)
      || (baseCategorySlug && categoryTreeSlug === baseCategorySlug)
  })
}

export const resolveScopedCatalogCategories = (
  categoryTree: TgCategory[],
  baseCategoryId: string | null,
  baseCategorySlug: string | null
) => {
  const scopedRoot = resolveScopedCategoryRoot(categoryTree, baseCategoryId, baseCategorySlug)
  return scopedRoot ? getCategoryChildren(scopedRoot) : categoryTree
}

export const useTgCatalog = (categorySlug: Ref<string | null> | ComputedRef<string | null>) => {
  const { $api } = useNuxtApp()
  const config = useRuntimeConfig()
  const { t } = useTgI18n()
  const ui = useTgUiStore()
  const tgCatalogConfig = (config.public.tg as any)?.catalog || {}
  const baseCategoryId = normalizeCategoryId(tgCatalogConfig.baseCategoryId)
  const baseCategorySlug = normalizeCategorySlug(tgCatalogConfig.baseCategorySlug)

  const categoryTree = ref<TgCategory[]>([])
  const categories = ref<TgCategory[]>([])
  const products = ref<TgProduct[]>([])
  const meta = ref<Record<string, any>>({})
  const page = ref(1)
  const loading = ref(false)
  const categoriesLoading = ref(false)
  const error = ref<unknown>(null)
  const perPage = 12

  const scopedCategoryRoot = computed(() => {
    return resolveScopedCategoryRoot(categoryTree.value, baseCategoryId, baseCategorySlug)
  })

  const scopedCategoryTree = computed(() => {
    return scopedCategoryRoot.value ? [scopedCategoryRoot.value] : categoryTree.value
  })

  const activeCategory = computed(() => {
    if (!categorySlug.value) return null
    return findCategoryInTree(scopedCategoryTree.value, (category) => category.slug === categorySlug.value) || null
  })

  const hasMore = computed(() => {
    const currentPage = Number(meta.value?.current_page || page.value)
    const lastPage = Number(meta.value?.last_page || 0)
    if (lastPage) return currentPage < lastPage
    return products.value.length >= perPage && products.value.length % perPage === 0
  })

  const loadCategories = async () => {
    categoriesLoading.value = true
    try {
      const response = await $api('/category', {
        method: 'GET',
        query: {
          with_count: true
        }
      })
      categoryTree.value = asArray<TgCategory>(response)
      categories.value = resolveScopedCatalogCategories(categoryTree.value, baseCategoryId, baseCategorySlug)
    } catch (err) {
      categoryTree.value = []
      categories.value = []
      ui.showToast(t('loading_error'), 'error')
    } finally {
      categoriesLoading.value = false
    }
  }

  const loadProducts = async (reset = true) => {
    loading.value = true
    error.value = null

    if (reset) {
      page.value = 1
    }

    try {
      const requestedCategorySlug = categorySlug.value
      const selectedBaseRoot = scopedCategoryRoot.value
      const isBaseRootRoute = Boolean(
        requestedCategorySlug
        && baseCategorySlug
        && requestedCategorySlug.toLowerCase() === baseCategorySlug
      )

      if (requestedCategorySlug && selectedBaseRoot && !isBaseRootRoute) {
        const selectedCategory = findCategoryInTree([selectedBaseRoot], (category) => category.slug === requestedCategorySlug)
        if (!selectedCategory) {
          products.value = []
          meta.value = {}
          return
        }
      }

      const response = await $api('/catalog', {
        method: 'GET',
        query: {
          with_products: true,
          per_page: perPage,
          page: page.value,
          cache: true,
          ...(requestedCategorySlug && !isBaseRootRoute
            ? { category_slug: requestedCategorySlug }
            : {
                ...(baseCategoryId ? { category_id: baseCategoryId } : {}),
                ...(baseCategorySlug ? { category_slug: baseCategorySlug } : {})
              })
        }
      })
      const normalized = normalizeCatalog(response)
      products.value = reset ? normalized.products : [...products.value, ...normalized.products]
      meta.value = normalized.meta
    } catch (err) {
      error.value = err
      if (reset) products.value = []
      ui.showToast(t('loading_error'), 'error')
    } finally {
      loading.value = false
    }
  }

  const loadMore = async () => {
    if (loading.value || !hasMore.value) return
    page.value = Number(meta.value?.current_page || page.value) + 1
    await loadProducts(false)
  }

  const refresh = async () => {
    await loadCategories()
    await loadProducts(true)
  }

  return {
    categories,
    products,
    meta,
    activeCategory,
    loading,
    categoriesLoading,
    error,
    hasMore,
    loadCategories,
    loadProducts,
    loadMore,
    refresh
  }
}
