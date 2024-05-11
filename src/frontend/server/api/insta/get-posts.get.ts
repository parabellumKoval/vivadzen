export default defineEventHandler(async (event) => {
  // const body = await readBody(event)
  const accessToken = useRuntimeConfig().public.instagramToken
  const apiVersion = 19.0
  const igUserId = 'me'
  const per_page = 10

  const url = `https://graph.instagram.com/${igUserId}/media?fields=permalink,media_url&limit=${per_page}&access_token=${accessToken}`

  // await useFetch(url, {
    
  // })
  return await $fetch(url, {
    method: 'GET'
  })
})