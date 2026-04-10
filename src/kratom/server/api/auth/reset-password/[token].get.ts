export default defineEventHandler((event) => {
  const token = getRouterParam(event, 'token')

  if (!token) {
    throw createError({
      statusCode: 400,
      statusMessage: 'Password reset token is required',
    })
  }

  const query = getQuery(event)
  const params = new URLSearchParams({
    newpassword: 'true',
    t: token,
  })

  if (typeof query.email === 'string' && query.email) {
    params.set('email', query.email)
  }

  if (typeof query.storefront === 'string' && query.storefront) {
    params.set('storefront', query.storefront)
  }

  if (typeof query.error === 'string' && query.error) {
    params.set('error', query.error)
  }

  if (typeof query.error_code === 'string' && query.error_code) {
    params.set('error_code', query.error_code)
  }

  if (typeof query.error_description === 'string' && query.error_description) {
    params.set('error_description', query.error_description)
  }

  return sendRedirect(event, `/new-password?${params.toString()}`, 302)
})
