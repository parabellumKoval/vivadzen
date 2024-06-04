<script setup>
const {t} = useI18n()

const items = computed(() => {
  return useModal().active?.data?.actions || []
})

const description = computed(() => {
  return useModal().active?.data?.description || null
})

// HANDLERS
</script>

<template>
  <modal-wrapper :title="t('actions')" :description="description">
    <list :items="items" type="nav"></list>
  </modal-wrapper>
</template>