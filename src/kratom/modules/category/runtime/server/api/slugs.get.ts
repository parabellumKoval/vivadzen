import { defineEventHandler, getQuery } from 'h3'
import { getCategorySlugs } from '../utils/cache'

export default defineEventHandler(async (event) => {
  const query = getQuery(event)
  const force =
    typeof query.force === 'string'
      ? ['true', '1', 'yes'].includes(query.force.toLowerCase())
      : false
  const slugsSet = await getCategorySlugs({ force })

  return {
    slugs: Array.from(slugsSet),
    count: slugsSet.size
  }
})
