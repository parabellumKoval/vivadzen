import { defineStore } from "pinia"

export interface GoogleReview {
  id: number
  review_id: string
  review_name: string
  is_active?: boolean
  sort_order?: number
  rating: number
  comment: string
  reviewer: {
    name: string
    photo_url: string | null
    photo_path?: string | null
    is_anonymous: boolean
  }
  reply: {
    comment: string
    updated_at: string
  } | null
  location: {
    id: number
    title: string
    account_id: string
    location_name: string
  } | null
  review_created_at: string
  review_updated_at: string
}

export interface GoogleReviewsMeta {
  total: number
  avg_rating: number | null
  current_page?: number
  last_page?: number
  per_page?: number
}

export interface GoogleReviewsState {
  reviews: GoogleReview[]
  meta: GoogleReviewsMeta | null
  loading: boolean
  error: string | null
}

export interface GoogleReviewsQueryParams {
  page?: number
  per_page?: number
  location_id?: number
  location_name?: string
  rating?: number
  has_comment?: boolean
  include_inactive?: boolean
}

const collectFiniteNumbers = (value: unknown): number[] => {
  if (Array.isArray(value)) {
    return value.flatMap(collectFiniteNumbers)
  }

  if (typeof value === 'number') {
    return Number.isFinite(value) ? [value] : []
  }

  if (typeof value === 'string') {
    const parsed = Number(value)
    return Number.isFinite(parsed) ? [parsed] : []
  }

  return []
}

const normalizeMeta = (meta: unknown): GoogleReviewsMeta | null => {
  if (!meta || typeof meta !== 'object') {
    return null
  }

  const rawMeta = meta as Record<string, unknown>
  const totalValues = collectFiniteNumbers(rawMeta.total)
  const avgRatingValues = collectFiniteNumbers(rawMeta.avg_rating)

  return {
    ...rawMeta,
    total: totalValues.length ? Math.max(...totalValues) : 0,
    avg_rating: avgRatingValues.length ? avgRatingValues[0] : null,
  } as GoogleReviewsMeta
}

export const useGoogleReviewsStore = defineStore('googleReviewsStore', {
  state: (): GoogleReviewsState => ({
    reviews: [],
    meta: null,
    loading: false,
    error: null
  }),

  getters: {
    getReviews: (state) => state.reviews,
    getMeta: (state) => state.meta,
    isLoading: (state) => state.loading,
    getError: (state) => state.error,
    getAverageRating: (state) => state.meta?.avg_rating ?? null,
    getTotalCount: (state) => state.meta?.total ?? 0
  },

  actions: {
    async fetchReviews(params: GoogleReviewsQueryParams = {}) {
      this.loading = true
      this.error = null

      try {
        const runtimeConfig = useRuntimeConfig()
        const queryParams = new URLSearchParams()
        
        if (params.page) {
          queryParams.set('page', String(params.page))
        }
        if (params.per_page) {
          queryParams.set('per_page', String(params.per_page))
        }
        if (params.location_id) {
          queryParams.set('location_id', String(params.location_id))
        }
        if (params.location_name) {
          queryParams.set('location_name', params.location_name)
        }
        if (params.rating !== undefined) {
          queryParams.set('rating', String(params.rating))
        }
        if (params.has_comment !== undefined) {
          queryParams.set('has_comment', params.has_comment ? '1' : '0')
        }
        if (params.include_inactive !== undefined) {
          queryParams.set('include_inactive', params.include_inactive ? '1' : '0')
        }

        const queryString = queryParams.toString()
        const url = `${runtimeConfig.public.apiBase}/google-reviews${queryString ? '?' + queryString : ''}`

        const { data, error } = await useApiFetch(url)

        if (error.value) {
          throw new Error(error.value.message || 'Failed to fetch Google reviews')
        }

        if (data.value) {
          const response = data.value as any
          this.reviews = response.data || []
          this.meta = normalizeMeta(response.meta)
        }

        return { reviews: this.reviews, meta: this.meta }
      } catch (err: any) {
        this.error = err.message || 'An error occurred'
        console.error('Error fetching Google reviews:', err)
        return { reviews: [], meta: null }
      } finally {
        this.loading = false
      }
    },

    async fetchReviewsLazy(params: GoogleReviewsQueryParams = {}) {
      const runtimeConfig = useRuntimeConfig()
      const queryParams = new URLSearchParams()
      
      if (params.page) {
        queryParams.set('page', String(params.page))
      }
      if (params.per_page) {
        queryParams.set('per_page', String(params.per_page))
      }
      if (params.location_id) {
        queryParams.set('location_id', String(params.location_id))
      }
      if (params.location_name) {
        queryParams.set('location_name', params.location_name)
      }
      if (params.rating !== undefined) {
        queryParams.set('rating', String(params.rating))
      }
      if (params.has_comment !== undefined) {
        queryParams.set('has_comment', params.has_comment ? '1' : '0')
      }
      if (params.include_inactive !== undefined) {
        queryParams.set('include_inactive', params.include_inactive ? '1' : '0')
      }

      const queryString = queryParams.toString()
      const url = `${runtimeConfig.public.apiBase}/google-reviews${queryString ? '?' + queryString : ''}`

      return useApiFetch(url, null, 'GET', { lazy: true })
    },

    clearReviews() {
      this.reviews = []
      this.meta = null
      this.error = null
    }
  }
})
