import fetch from 'node-fetch'

export default async () => {

  const getData = async () => {
    const response = await fetch(process.env.SERVER_URL + '/api/djini-category/slugs')
    const data = await response.json();
    return data?.data
  }

  const getRoutes = async () => {
    const data = await getData()

    const categories = data.map((category) => {
      console.log(category.slug)
      return {
        name: 'category-' + category.id,
        path: '/' + category.slug,
        // params: {category: category.slug},
        file: '~/extra_pages/category/index.vue'
        // component: () => import('~/pages/category/index.vue').then(r => r.default || r)
      }
    })

    // console.log('new categories', categories);  

    return categories
  }

  return [
    ...await getRoutes()
  ]
}