<script setup lang="ts">
const route = useRoute()
const category = computed(() => String(route.params.category || ''))

definePageMeta({
  key: (route) => `${route.params.region}-${route.params.locale}-${route.params.category}`
})
</script>

<template>
  <TgCatalogPage :category="category" />
</template>
