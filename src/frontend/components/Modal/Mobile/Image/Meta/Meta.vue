<script setup>
const {t} = useI18n()

const image = computed(() => {
  return useModal().active?.data?.image || null
})

const description = computed(() => {
  return useModal().active?.data?.description || null
})

// HANDLERS
</script>

<style src="./meta.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('actions')" :description="description">
    <div class="form-wrapper">
      <form-label :label="t('alt')">
        <form-text v-model="image.alt" :placeholder="t('alt')"></form-text>
      </form-label>

      <form-label :label="t('title')">
        <form-text v-model="image.alt" :placeholder="t('title')"></form-text>
      </form-label>
    </div>
  </modal-wrapper>
</template>