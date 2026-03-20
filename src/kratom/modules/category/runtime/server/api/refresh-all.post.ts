import { defineEventHandler } from 'h3'
import { refreshAllCategories } from '../utils/cache'

export default defineEventHandler(async () => {
  const { slugs, list } = await refreshAllCategories()
  return {
    success: true,
    total: slugs.length,
    slugs,
    list
  }
})
