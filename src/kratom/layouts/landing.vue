<script setup lang="ts">
const route = useRoute()
const { htmlAttrs } = useKratomLocaleHead()
const modal = useModal()

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
</script>

<template>
  <div>
    <Html :lang="htmlAttrs.lang" :dir="htmlAttrs.dir">
      <Body>
        <slot />
        <KratomSiteFooter />
        <lazy-modal-noty />
        <modal-transition :is-show="Boolean(modal.active?.show)" mode="out-in">
          <component v-if="modal.active?.component" :is="modal.active.component"></component>
        </modal-transition>
      </Body>
    </Html>
  </div>
</template>
