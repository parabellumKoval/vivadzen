<script setup lang="ts">
import FloatingButtons from '~/components/Section/Landing/FloatingButtons/FloatingButtons.vue'

const route = useRoute()
const runtimeConfig = useRuntimeConfig()
const { htmlAttrs } = useKratomLocaleHead()
const modal = useModal()
const siteUrl = String(runtimeConfig.public.site?.url || runtimeConfig.public.siteUrl || 'https://kratom.vivadzen.com').replace(/\/+$/, '')

watch(
  () => route.fullPath,
  () => {
    if (modal.active?.options?.closeOnRouteChange === false) {
      return
    }

    modal.close()
  },
  { immediate: true },
)

useSchemaOrg([
  defineWebSite({
    url: siteUrl,
    name: 'VivaDzen Kratom',
  }),
  defineWebPage(),
])
</script>

<template>
  <div>
    <Html :lang="htmlAttrs.lang" :dir="htmlAttrs.dir">
      <Body>
        <slot />
        <FloatingButtons />
        <KratomSiteFooter />
        <!-- <lazy-kratom-region-switcher-modal /> -->
        <lazy-modal-noty />
        <modal-transition :is-show="Boolean(modal.active?.show)" mode="out-in">
          <component v-if="modal.active?.component" :is="modal.active.component"></component>
        </modal-transition>
      </Body>
    </Html>
  </div>
</template>
