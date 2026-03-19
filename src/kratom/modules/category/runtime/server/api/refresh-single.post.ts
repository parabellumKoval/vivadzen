import { defineEventHandler, createError } from 'h3'
import { refreshCategory } from '../utils/cache'

export default defineEventHandler(async (event) => {
  const slug = event.context.params?.slug
  if (!slug) {
    throw createError({ statusCode: 400, statusMessage: 'Category slug is required' })
  }

  const entry = await refreshCategory(slug)
  return {
    success: true,
    slug,
    contexts: Object.keys(entry.perContext),
    fetchedAt: entry.fetchedAt
  }
})
