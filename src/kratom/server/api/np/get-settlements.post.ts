import { getHeader } from 'h3'

export default defineEventHandler(async (event) => {
  const body = event.node.req.body || await readBody(event)

  const query: Record<string, string | boolean> = {}

  if (body.find) {
    query.q = body.find
  }

  if (body.ref) {
    query.ref = body.ref
  }

  if (!body.find && !body.ref) {
    query.popular = true
  }

  const headers: Record<string, string> = {}
  const acceptLanguage = getHeader(event, 'accept-language')
  const region = getHeader(event, 'x-region')

  if (acceptLanguage) headers['accept-language'] = acceptLanguage
  if (region) headers['x-region'] = region

  return await $fetch(`${useRuntimeConfig().public.apiBase}/np/settlements`, {
    method: 'GET',
    query,
    headers
  })
})
