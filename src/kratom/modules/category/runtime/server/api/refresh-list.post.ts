import { defineEventHandler, readBody } from 'h3'
import { refreshCategoryList } from '../utils/cache'

const WEBHOOK_ENVELOPE_KEYS = new Set([
  'timestamp',
  'source',
  'origin',
  'unit',
  'event',
  'meta',
  'items',
])

function normalizeQueryCandidate(
  candidate: unknown
): Record<string, any> | undefined {
  if (!candidate || typeof candidate !== 'object' || Array.isArray(candidate)) {
    return undefined
  }

  return Object.keys(candidate).length
    ? (candidate as Record<string, any>)
    : undefined
}

export default defineEventHandler(async (event) => {
  const body = await readBody<Record<string, any> | undefined>(event).catch(() => undefined)
  let query = normalizeQueryCandidate(body?.query)

  if (!query) {
    const bodyRecord = normalizeQueryCandidate(body)
    const looksLikeWebhookEnvelope = Boolean(
      bodyRecord &&
      Object.keys(bodyRecord).some((key) => WEBHOOK_ENVELOPE_KEYS.has(key))
    )

    if (!looksLikeWebhookEnvelope) {
      query = bodyRecord
    }
  }

  const result = await refreshCategoryList(query)

  return {
    success: true,
    ...result
  }
})
