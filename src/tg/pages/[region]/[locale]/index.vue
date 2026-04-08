<script setup lang="ts">
definePageMeta({
  key: (route) => `${route.params.region}-${route.params.locale}-catalog`
})
</script>

<template>
  <TgCatalogPage />
</template>
