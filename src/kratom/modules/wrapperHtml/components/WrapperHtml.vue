<template>
  <span v-html="processedHtml" />
</template>

<script setup lang="ts">
import { useWrapperHtml } from '../composables/useWrapperHtml'

interface Props {
  string: string
  wrappers?: string[]
}

const props = withDefaults(defineProps<Props>(), {
  string: '',
  wrappers: () => []
})

const { wrapperHtml } = useWrapperHtml()

const processedHtml = computed(() => {
  return wrapperHtml(props.string, props.wrappers)
})
</script>