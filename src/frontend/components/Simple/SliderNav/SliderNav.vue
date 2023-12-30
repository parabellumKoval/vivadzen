<script setup>

const props = defineProps({
  isPrevDisabled: {
    type: Boolean,
    default: true
  },
  isNextDisabled: {
    type: Boolean,
    default: false
  },
  moreText: {
    default: 'Смотреть все'
  }
})

const emit = defineEmits([
  'prev', 'next'
])

const prevHandler = () => {
  emit('prev')
}

const nextHandler = () => {
  emit('next')
}

const more = computed(() => {
  return {
    link: '/',
    text: 'Смотреть все'
  }
})
</script>

<style src="./slider-nav.scss" lang="scss" scoped />

<template>
  <div class="nav">
    <button @click="prevHandler" :class="{disabled: isPrevDisabled}" type="button" class="nav-btn prev">
      <IconCSS name="fluent:chevron-left-48-filled" size="24px" class="icon"></IconCSS>
    </button>
    <NuxtLink :to="localePath('/' + more.link)" :aria-label="more.text" clickable type="button" class="button secondary">
      {{ more.text }}
    </NuxtLink>
    <button @click="nextHandler" :class="{disabled: isNextDisabled}" type="button" class="nav-btn next">
      <IconCSS name="fluent:chevron-right-48-filled" size="24px" class="icon"></IconCSS>
    </button>
  </div>
</template>