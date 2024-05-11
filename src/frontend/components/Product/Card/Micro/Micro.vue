<script setup>
import {useCard} from '~/composables/product/useCard.ts'

const props = defineProps({
  item: {
    type: Object
  }
})

const {photo, photoAlt, photoTitle, photoSize} = useCard(props.item)

</script>

<style src="./micro.scss" lang="scss" scoped></style>

<template>
<div v-if="item" class="wrapper">
  <NuxtLink :to="localePath('/' + item.slug)" :aria-label="item.name" clickable class="image-wrapper">
    <nuxt-img
      :src = "photo"
      :alt = "photoAlt"
      :title = "photoTitle"
      :class = "photoSize"
      width="85"
      height="110"
      sizes = "mobile:100vw tablet:85px desktop:85px"
      format = "webp"
      quality = "60"
      loading = "lazy"
      fit="outside"
      placeholder="./images/noimage.png"
      class="image"
    >
    </nuxt-img> 
  </NuxtLink>
  <div class="body">
    <NuxtLink
      v-if="item.category"
      :to="localePath('/' + item.category.slug)"
      :aria-label="item.category.name"
      clickable
      class="category"
    >{{ item.category.name }}</NuxtLink>
    <NuxtLink
      :to="localePath('/' + item.slug)"
      :aria-label="item.name"
      clickable
      class="name"
    >{{ item.name }}</NuxtLink>
    <div class="footer">
      <simple-stars :amount="item.rating" mobile="medium"></simple-stars>

      <product-price :price="item.price" :old-price="item.oldPrice" class="price-block"></product-price>
    </div>
  </div>
</div>
</template>