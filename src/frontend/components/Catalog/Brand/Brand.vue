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
const titleRef = ref(null)
const imageRef = ref(null)
const innerRef = ref(null)

// COMPUTEDS
const image = computed(() => {
  return props.item?.images[0] || null
})

const isOverlay = computed(() => {

  if(contentRef.value && titleRef.value && imageRef.value && innerRef.value) {
    const offset = imageRef.value.offsetHeight - innerRef.value.offsetHeight - titleRef.value.offsetHeight

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

// HANDLERS
// WATCHERS
</script>

<style src='./brand.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="brand-wrapper">
    <div class="brand-image-wrapper" ref="imageRef">
      <nuxt-img
        v-if='image.src'
        :src='image?.src'
        :alt='image?.alt || item.name'
        :title='image?.title || item.name'
        width='100'
        height='100'
        sizes='mobile:100vw tablet:100px desktop:100px'
        format='webp'
        quality='60'
        loading='lazy'
        fit='outside'
        class='brand-image'
      />
    </div>
    <div>
      <div ref="titleRef" class="brand-name title-secondary">{{ item.name }}</div>
      <div ref="contentRef" :class="[{active: isOpen}, {full: !isOverlay}]" class="brand-desc">
        <div ref="innerRef" v-html="item.content"></div>
      </div>
      <button v-if="isOverlay" @click="toggleHandler" class="text-link more-btn">
        <transition name="fade-in">
          <span v-if="isOpen">Свернуть</span>
          <span v-else>Развернуть</span>
        </transition>
        <transition name="fade-in">
          <IconCSS v-if="isOpen" name="iconoir:nav-arrow-left"></IconCSS>
          <IconCSS v-else name="iconoir:nav-arrow-right"></IconCSS>
        </transition>
      </button>
    </div>
  </div>
</template>
