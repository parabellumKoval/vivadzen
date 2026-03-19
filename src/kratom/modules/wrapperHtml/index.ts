import { defineNuxtModule, addImportsDir, addComponent, createResolver } from '@nuxt/kit'

export default defineNuxtModule({
  meta: {
    name: 'wrapper-html',
    configKey: 'wrapperHtml',
  },
  defaults: {},
  setup(options, nuxt) {
    const resolver = createResolver(import.meta.url)
    
    // Add composables
    addImportsDir(resolver.resolve('./composables'))
    
    // Add components
    addComponent({
      name: 'WrapperHtml',
      filePath: resolver.resolve('./components/WrapperHtml.vue')
    })
  }
})