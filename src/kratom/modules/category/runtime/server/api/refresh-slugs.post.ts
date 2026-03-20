import { defineEventHandler } from 'h3'
import { refreshCategorySlugs } from '../utils/cache'

export default defineEventHandler(async () => {
  const entry = await refreshCategorySlugs()
  return {
    success: true,
    slugs: entry.aggregated,
    fetchedAt: entry.fetchedAt
  }
})
