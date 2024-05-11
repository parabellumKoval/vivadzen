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

const imageSrc = computed(() => {
  if(image.value?.src)
    return '/server/images/brands/' + image.value.src
  else
    return null
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
<style src='~/assets/scss/_rich-text.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="brand-wrapper">
    <div class="brand-image-wrapper" ref="imageRef">
      <nuxt-img
        :src='imageSrc'
        :alt='image?.alt || item.name'
        :title='image?.title || item.name'
        width='100'
        height='100'
        sizes='mobile:100vw tablet:100px desktop:100px'
        format='webp'
        quality='60'
        loading='lazy'
        fit='outside'
        placeholder="./images/noimage.png"
        class='brand-image'
      />
    </div>
    <div>
      <div ref="titleRef" class="brand-name title-secondary">{{ item.name }}</div>
      <div ref="contentRef" :class="[{active: isOpen}, {full: !isOverlay}]" class="brand-desc">
        <div ref="innerRef" class="rich-text" v-html="item.content"></div>
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
  </div>
</template>
