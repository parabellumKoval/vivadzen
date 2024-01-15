
import type { localePath } from 'vue-i18n-routing';
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

<style src="./card.scss" lang="scss" scoped></style>

<template>
  <NuxtLink :to="localePath('/blog/' + item.slug)" class="article-card">
    <nuxt-img
      v-if="photo"
      :src = "photo"
      :alt = "item.image.alt || item.name"
      :title = "item.image.title || item.name"
      :class="item.image.size"
      width="290"
      height="260"
      sizes = "mobile:100vw tablet:230px desktop:300px"
      format = "webp"
      quality = "60"
      loading = "lazy"
      fit="outside"
      class="article-image"
    />

    <div class="article-title">{{ item.title }}</div>

    <div class="article-time">
      <IconCSS name="iconoir:clock" size="16" class="icon"></IconCSS>
      <span class="label">{{ item.time }} мин. чтения</span>
    </div>
  </NuxtLink>
</template>