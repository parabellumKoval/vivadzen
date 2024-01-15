<script setup>
const {t} = useI18n()
const props = defineProps({
  item: {
    type: Object
  }
})

const isOpen = ref(false)
// COMPUTEDS
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
    <nuxt-img
      v-if='item.image'
      :src='item.image'
      :alt='item.name'
      :title='item.name'
      width='100'
      height='100'
      sizes='mobile:100vw tablet:100px desktop:100px'
      format='webp'
      quality='60'
      loading='lazy'
      fit='outside'
      class='brand-image'
    />
    <div>
      <div class="brand-name title-secondary">{{ item.name }}</div>
      <div :class="{active: isOpen}" class="brand-desc" v-html="item.desc"></div>
      <button @click="toggleHandler" class="text-link more-btn">
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
