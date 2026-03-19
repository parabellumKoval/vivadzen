<script setup lang="ts">
const route = useRoute()
const { htmlAttrs } = useKratomLocaleHead()

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
</script>

<template>
  <div>
    <Html :lang="htmlAttrs.lang" :dir="htmlAttrs.dir">
      <Body>
        <slot />
        <KratomSiteFooter />
        <lazy-modal-noty />
        <modal-transition :is-show="useModal().show" mode="out-in">
          <component :is="useModal().active.component"></component>
        </modal-transition>
      </Body>
    </Html>
  </div>
</template>
