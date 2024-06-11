<script setup>

const props = defineProps({
  categories: {
    type: Array
  }
})
// COMPUTEDS
// METHODS
const getPhotoSrc = (image) => {
  if(image?.src) {
    return '/server/images/categories/' + image.src
  } else {
    return '/images/noimage.png'
  }
}
// HANDLERS
// WATCHERS
</script>

<style src='./categories.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="category-wrapper">
    <NuxtLink v-for="category in categories" :key="category.id" :to="localePath('/' + category.slug)" class="category">
      <nuxt-img
        :src = "getPhotoSrc(category.image)"
        :alt = "category.image.alt || category.name"
        :title = "category.image.title || category.name"
        width="100"
        height="100"
        sizes = "mobile:100px"
        format = "webp"
        quality = "60"
        loading = "lazy"
        fit="outside"
        placeholder="/images/noimage.png"
        class="category-image"
      >
      </nuxt-img>
      <div class="category-name">
        {{ category.name }}
      </div>
    </NuxtLink>
  </div>
</template>