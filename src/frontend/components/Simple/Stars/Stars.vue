<script setup>
  const props = defineProps({
    amount: {
      type: Number,
      default: 5
    },
    size: {
      type: Number,
      default: 16
    },
    desktop: {
      type: String,
      default: 'medium' // small, medium, large
    },
    tablet: {
      type: String,
      default: 'medium' // small, medium, large
    },
    mobile: {
      type: String,
      default: 'small' // small, medium, large
    }
  })

  const showMedium = computed(() => {
    return useDevice().isMobile && props.mobile === 'medium' ||
            useDevice().isTablet && props.tablet === 'medium' ||
              useDevice().isDesktop && props.desktop === 'medium'
  })

  const showSmall = computed(() => {
    return useDevice().isMobile && props.mobile === 'small' ||
            useDevice().isTablet && props.tablet === 'small' ||
              useDevice().isDesktop && props.desktop === 'small'
  })

  const showLarge = computed(() => {
    return useDevice().isMobile && props.mobile === 'large' ||
            useDevice().isTablet && props.tablet === 'large' ||
              useDevice().isDesktop && props.desktop === 'large'
  })

</script>

<style src="./stars.scss" lang="scss" scoped />

<template>
  <div v-if="showMedium" class="stars-medium">
    <IconCSS v-for="i in 5" :key="i" name="bxs:star" :class="{active: i <= amount }" class="star" />
  </div>
  <div v-else-if="showSmall" class="stars-small">
    <IconCSS name="bxs:star" class="star active" />
    <span class="amount">{{ amount }}</span>
  </div>
  <div v-else-if="showLarge" class="stars-large">
    <IconCSS v-for="i in 5" :key="i" name="bxs:star" :class="{active: i <= amount }" class="star" />
    <span class="amount">{{ amount.toFixed(1) }}</span>
  </div>
</template>