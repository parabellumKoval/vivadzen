<script setup>
const props = defineProps({
  items: {
    type: Array,
    default: () => []
  },
  speed: {
    type: Number,
    default: 80
  }
})

const duplicatedItems = computed(() => {
  const repeats = 20
  return Array(repeats).fill(props.items).flat()
})
</script>

<style src="./marquee-ticker.scss" lang="scss" scoped></style>

<template>
  <div class="marquee-ticker">
    <div class="marquee-ticker__track">
      <div 
        class="marquee-ticker__content"
        :style="{ animationDuration: `${speed}s` }"
      >
        <template v-for="(item, index) in duplicatedItems" :key="index">
          <span class="marquee-ticker__item">{{ item }}</span>
          <span class="marquee-ticker__separator">★</span>
        </template>
      </div>
    </div>
  </div>
</template>
