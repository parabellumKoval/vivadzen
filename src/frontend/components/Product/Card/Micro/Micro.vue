<script setup>
const props = defineProps({
  item: {
    type: Object
  }
})

const photo = computed(() => {
  if(props.item.image?.src) {
    return props.item.image.src
    // return '/server/' + props.item.image.src
  } else {
    return null
  }
})
</script>

<style src="./micro.scss" lang="scss" scoped></style>

<template>
<div class="wrapper">
  <NuxtLink :to="localePath('/' + item.slug)" :aria-label="item.name" clickable class="image-wrapper">
    <nuxt-img
      v-if="photo"
      :src = "photo"
      :alt = "item.image.alt || item.name"
      :title = "item.image.title || item.name"
      :class="item.image.size"
      width="85"
      height="110"
      sizes = "mobile:100vw tablet:85px desktop:85px"
      format = "webp"
      quality = "60"
      loading = "lazy"
      fit="outside"
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
      <simple-price v-if="+item.price" :value="+item.price"></simple-price>
    </div>
  </div>
</div>
</template>