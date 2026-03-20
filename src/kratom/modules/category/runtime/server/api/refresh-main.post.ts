import { defineEventHandler } from 'h3'
import { refreshCategoryMainList } from '../utils/cache'

export default defineEventHandler(async () => {
  const result = await refreshCategoryMainList()
  return {
    success: true,
    ...result
  }
})
