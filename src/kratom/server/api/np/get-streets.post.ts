import { getHeader } from 'h3'

export default defineEventHandler(async (event) => {
  const body = event.node.req.body || await readBody(event)
  
  const query: Record<string, string> = {}

  if (body.find) {
    query.q = body.find
  }

  if (body.settlementRef) {
    query.settlementRef = body.settlementRef
  }

  const headers: Record<string, string> = {}
  const acceptLanguage = getHeader(event, 'accept-language')
  const region = getHeader(event, 'x-region')

  if (acceptLanguage) headers['accept-language'] = acceptLanguage
  if (region) headers['x-region'] = region

  return await $fetch(`${useRuntimeConfig().public.apiBase}/np/streets`, {
    method: 'GET',
    query,
    headers
  })
})
