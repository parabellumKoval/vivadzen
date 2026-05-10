export interface TgGoogleReview {
  id: number
  review_id: string
  rating: number
  comment: string
  reviewer: {
    name: string
    photo_url: string | null
    is_anonymous: boolean
  } | null
  reply: {
    comment: string
    updated_at: string
  } | null
  review_created_at: string
  review_updated_at: string
}

export interface TgGoogleReviewsMeta {
  total: number
  avg_rating: number | null
  current_page?: number
  last_page?: number
  per_page?: number
}

export interface TgGoogleReviewsQueryParams {
  page?: number
  per_page?: number
  rating?: number
  has_comment?: boolean
}

const collectFiniteNumbers = (value: unknown): number[] => {
  if (Array.isArray(value)) return value.flatMap(collectFiniteNumbers)
  if (typeof value === 'number') return Number.isFinite(value) ? [value] : []
  if (typeof value === 'string') {
    const parsed = Number(value)
    return Number.isFinite(parsed) ? [parsed] : []
  }
  return []
}

const normalizeMeta = (meta: unknown): TgGoogleReviewsMeta | null => {
  if (!meta || typeof meta !== 'object') return null
  const raw = meta as Record<string, unknown>
  const totals = collectFiniteNumbers(raw.total)
  const avgs = collectFiniteNumbers(raw.avg_rating)
  return {
    ...raw,
    total: totals.length ? Math.max(...totals) : 0,
    avg_rating: avgs.length ? avgs[0] : null,
  } as TgGoogleReviewsMeta
}

export const useTgGoogleReviews = () => {
  const { $api } = useNuxtApp()

  const reviews = ref<TgGoogleReview[]>([])
  const meta = ref<TgGoogleReviewsMeta | null>(null)
  const loading = ref(false)
  const error = ref<unknown>(null)

  const fetchReviews = async (params: TgGoogleReviewsQueryParams = {}) => {
    loading.value = true
    error.value = null

    try {
      const query: Record<string, string> = {}
      if (params.page) query.page = String(params.page)
      if (params.per_page) query.per_page = String(params.per_page)
      if (params.rating !== undefined) query.rating = String(params.rating)
      if (params.has_comment !== undefined) query.has_comment = params.has_comment ? '1' : '0'

      const response: any = await $api('/google-reviews', { method: 'GET', query })
      reviews.value = Array.isArray(response?.data) ? response.data : []
      meta.value = normalizeMeta(response?.meta)
    } catch (err) {
      error.value = err
      reviews.value = []
      meta.value = null
    } finally {
      loading.value = false
    }

    return { reviews: reviews.value, meta: meta.value }
  }

  return {
    reviews,
    meta,
    loading,
    error,
    fetchReviews
  }
}
