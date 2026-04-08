<script setup lang="ts">
import { useTgUiStore } from '~/stores/ui'

const ui = useTgUiStore()
</script>

<template>
  <NuxtPage />
  <TgCartSheet v-model="ui.cartOpen" />
  <TgToast />
</template>
