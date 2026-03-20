<script setup lang="ts">
const route = useRoute()
const { htmlAttrs } = useKratomLocaleHead()
const modal = useModal()

const background = computed(() => route.meta?.bg || '#f8f1e6')

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
    url: 'https://kratom.vivadzen.com',
    name: 'VivaDzen Kratom',
  }),
  defineWebPage(),
])
</script>

<template>
  <div>
    <Html :lang="htmlAttrs.lang" :dir="htmlAttrs.dir">
      <Body :style="{ background }">
        <SectionLandingHeroHeader />
        <main class="kratom-main" :style="{ background }">
          <slot />
        </main>
        <KratomSiteFooter />
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
  min-height: calc(100vh - 60px);
  padding-top: 60px;

  @include desktop {
    min-height: calc(100vh - 80px);
    padding-top: 80px;
  }
}
</style>
