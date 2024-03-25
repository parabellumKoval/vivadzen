import type { RouterConfig } from '@nuxt/schema'
import fetchCategories from './helpers/fetchCategories'

export default <RouterConfig> {
  routes: (_routes) => {
    console.log('RouterConfig')
    return fetchCategories().getRoutes()
  }
}