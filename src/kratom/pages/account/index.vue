<script setup lang="ts">
const regionPath = useToLocalePath()

await navigateTo(regionPath('/account/orders'), { replace: true })
</script>

<template>
  <div></div>
</template>
