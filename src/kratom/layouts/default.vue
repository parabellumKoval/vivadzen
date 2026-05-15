<script setup lang="ts">
import FloatingButtons from '~/components/Section/Landing/FloatingButtons/FloatingButtons.vue'

const route = useRoute()
const runtimeConfig = useRuntimeConfig()
const { htmlAttrs } = useKratomLocaleHead()
const modal = useModal()
const siteUrl = String(runtimeConfig.public.site?.url || runtimeConfig.public.siteUrl || 'https://kratom.vivadzen.com').replace(/\/+$/, '')

const background = computed(() => route.meta?.pageBackground || route.meta?.bg || '#f8f1e6')
const pageTheme = computed(() => route.meta?.pageTheme || 'light')
const pageThemeStyles = computed(() => ({
  background: String(background.value),
  '--kratom-page-theme': String(pageTheme.value),
  '--kratom-page-background': String(background.value),
  '--kratom-page-text': pageTheme.value === 'dark' ? '#f7edde' : '#1f2b1d',
  '--kratom-page-muted': pageTheme.value === 'dark' ? 'rgba(247, 237, 222, 0.72)' : 'rgba(31, 43, 29, 0.68)',
  '--kratom-page-surface': pageTheme.value === 'dark' ? '#10231e' : '#fff7ec',
}))

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
      <Body :style="pageThemeStyles" :data-page-theme="pageTheme">
        <SectionLandingHeroHeader />
        <main class="kratom-main" :style="pageThemeStyles">
          <slot />
        </main>
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

<style scoped lang="scss">
.kratom-main {
  min-height: calc(100vh - 108px);
  padding-top: 60px;

  @include desktop {
    min-height: calc(100vh - 80px);
    padding-top: 80px;
  }
}
</style>
