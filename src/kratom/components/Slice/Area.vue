<script setup>
import { computed } from 'vue'

import SliceHtml from './Html/Html.vue'
import SliceImage from './Image/Image.vue'

const props = defineProps({
  slices: {
    type: Array,
    default: () => []
  }
})

const sliceComponentMap = {
  html: SliceHtml,
  image: SliceImage
}

const resolvedSlices = computed(() => props.slices
  .map((slice, index) => ({
    key: slice?.id ?? `${slice?.type ?? 'slice'}-${index}`,
    component: sliceComponentMap[slice?.type ?? ''],
    payload: slice
  }))
  .filter((entry) => entry.component && entry.payload)
)
</script>

<template>
  <section class="slice-area">
    <component
      v-for="entry in resolvedSlices"
      :key="entry.key"
      :is="entry.component"
      class="slice-area__item"
      v-bind="entry.payload"
    />
  </section>
</template>

<style scoped lang="scss">
  @use './assets/scss/rich-text' as *;
.slice-area {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.slice-area__item {
  width: 100%;
}
</style>