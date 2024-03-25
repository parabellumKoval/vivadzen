<script setup>
import { useCategoryStore } from '~/store/category'

const categories = computed(() => {
  return useCategoryStore().list
})


const getPhotoSrc = (image) => {
  if(image?.src) {
    return image.src
    // return '/server/' + image.src
  } else {
    return null
  }
}

// console.log('categories', categories.value)
</script>

<style src="./category.scss" lang="scss" scoped></style>

<template>
  <section class="main-section">
    <div class="section-title">категории</div>
    <div class="category-wrapper">
      <NuxtLink
        v-for="category in categories"
        :key="category.id"
        :to="localePath('/' + category.slug)"
        :aria-label="category.name"
        clickable
        class="category"
      >
        <nuxt-img
          v-if="category.image.src"
          :src = "getPhotoSrc(category.image)"
          :alt = "category.image.alt || category.name"
          :title = "category.image.title || category.name"
          :class="category.image.size"
          width="360"
          height="200"
          sizes = "mobile:100vw tablet:360px desktop:360px"
          format = "webp"
          quality = "60"
          loading = "lazy"
          fit="outside"
          class="category-image"
        >
        </nuxt-img>
        <IconCSS name="ph:caret-right-fill" class="category-icon"></IconCSS>
        <div class="category-content">
          <div class="category-name">{{ category.name }}</div>
          <div class="category-children">Подкатегорий: {{ category.children.length }}</div>
        </div>
      </NuxtLink>
    </div>
  </section>
</template>