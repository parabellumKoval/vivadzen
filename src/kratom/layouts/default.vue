<script setup lang="ts">
const route = useRoute()
const { htmlAttrs } = useKratomLocaleHead()

const background = computed(() => route.meta?.bg || '#f8f1e6')

watch(
  () => route.fullPath,
  () => {
    if (useModal().active?.options?.closeOnRouteChange === false) {
      return
    }

    useModal().close()
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
        <KratomSiteHeader />
        <main class="kratom-main" :style="{ background }">
          <slot />
        </main>
        <KratomSiteFooter />
        <lazy-modal-noty />
        <modal-transition :is-show="useModal().show" mode="out-in">
          <component :is="useModal().active.component"></component>
        </modal-transition>
      </Body>
    </Html>
  </div>
</template>

<style scoped lang="scss">
.kratom-main {
  min-height: calc(100vh - 78px);
}
</style>
