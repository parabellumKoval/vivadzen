<script setup lang="ts">
const config = useRuntimeConfig()
const fallbackRegion = (config.public.tg as any)?.fallbackRegion || 'cz'
const defaultLocale = (config.public.tg as any)?.regions?.[fallbackRegion]?.locale || 'cs'

await navigateTo(`/${fallbackRegion}/${defaultLocale}`, { replace: true })
</script>

<template>
  <div />
</template>
