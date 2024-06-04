import fetch from 'node-fetch'
import {createResolver} from '@nuxt/kit'

export default async () => {

  const getData = async () => {
    const response = await fetch(process.env.SERVER_URL + '/api/djini-category/slugs')
    const data = await response.json();
    return data?.data
  }

  const getRoutes = async () => {

    const resolver = createResolver(import.meta.url)
    const data = await getData()

    const categories = data.map((category) => {
      return {
        name: 'category-' + category.id,
        path: '/' + category.slug,
        file: resolver.resolve('./../extra_pages/category/index.vue')
        // params: {category: category.slug},
        // component: () => import('~/pages/category/index.vue').then(r => r.default || r)
      }
    })

    return categories
  }

  return [
    ...await getRoutes()
  ]
}