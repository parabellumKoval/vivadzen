<script setup lang="ts">
const route = useRoute()
const config = useRuntimeConfig()
const region = String(route.params.region || (config.public.tg as any)?.fallbackRegion || 'cz').toLowerCase()
const defaultLocale = (config.public.tg as any)?.regions?.[region]?.locale
  || (config.public.tg as any)?.localesByRegion?.[region]?.[0]
  || 'cs'

await navigateTo(`/${region}/${defaultLocale}`, { replace: true })
</script>

<template>
  <div />
</template>
