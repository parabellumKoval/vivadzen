<script setup>
const { t } = useI18n()
const searchInput = ref(null)
</script>

<style src="./search.scss" lang="scss" scoped />

<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div class="search" type="button" clickable>
    <IconCSS name="iconoir:search" size="20px" class="icon"></IconCSS>
    <input v-model="searchInput" :placeholder="t('search')" class="search-input">
    <button class="search-btn">{{ t('find') }}</button>
  </div>
</template>