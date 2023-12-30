<script setup>
const {t} = useI18n()

const props = defineProps({
  items: {
    type: Array
  }
})

const image = computed(() => {
  return props.items[0]
})

const images = computed(() => {
  return props.items
})
</script>

<style src="./gallery.scss" lang="scss" scoped></style>

<template>
  <div class="gallery">
    <div class="thumbnail">
      <button v-for="(item, index) in images" :key="index" class="thumbnail-btn">
        <nuxt-img
          v-if="item.src"
          :src = "item.src"
          :alt = "item.alt"
          :title = "item.title"
          :class="item.size"
          width="85"
          height="85"
          sizes = "mobile:100px tablet:100px desktop:100px"
          format = "webp"
          quality = "60"
          loading = "lazy"
          fit="outside"
          class="thumbnail-image"
        >
        </nuxt-img> 
      </button>
    </div>
    <div class="main">
      <nuxt-img
        v-if="image.src"
        :src = "image.src"
        :alt = "image.alt"
        :title = "image.title"
        :class="image.size"
        width="575"
        height="530"
        sizes = "mobile:100vw tablet:600px desktop:600px"
        format = "webp"
        quality = "60"
        loading = "lazy"
        fit="outside"
        class="main-image"
      >
      </nuxt-img> 
    </div>
  </div>
</template>