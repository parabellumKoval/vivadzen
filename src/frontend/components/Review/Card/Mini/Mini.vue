<script setup>
const props = defineProps({
  item: {
    type: Object
  }
})

const {t} = useI18n()
const isOpen = ref(false)

// Refs
const contentRef = ref(null)

const isOverlay = computed(() => {

  if(contentRef.value) {

    const offset = 80 - contentRef.value.offsetHeight

    if(offset < 0) {
      return true
    }else{
      return false
    }

  }else {
    return true
  }
})

// METHODS
const toggleHandler = () => {
  isOpen.value = !isOpen.value
}
</script>

<style src="./mini.scss" lang="scss" scoped></style>

<template>
  <div class="review">
    <div class="review-header">
      <div class="review-name">{{ item.author.name }}</div>
      <div class="review-date">{{ $d(item.created_at, 'short') }}</div>
    </div>
    
    <div :class="[{active: isOpen}]" class="review-content-wrapper">
      <div ref="contentRef" class="review-content">{{ item.text }}</div>
    </div>

    <button v-if="isOverlay" @click="toggleHandler" class="text-link more-btn">
      <transition name="fade-in">
        <span v-if="isOpen">{{ t('button.collapse') }}</span>
        <span v-else>{{ t('button.expand') }}</span>
      </transition>
      <transition name="fade-in">
        <IconCSS v-if="isOpen" name="iconoir:nav-arrow-left"></IconCSS>
        <IconCSS v-else name="iconoir:nav-arrow-right"></IconCSS>
      </transition>
    </button>

  </div>
</template>