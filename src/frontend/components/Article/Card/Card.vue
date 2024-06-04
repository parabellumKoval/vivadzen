
<script setup>
const props = defineProps({
  item: {
    type: Object
  }
})

const {t} = useI18n()

const photo = computed(() => {
  if(props.item.image) {
    return '/server/' + props.item.image
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
      quality = "40"
      loading = "lazy"
      fit="outside"
      placeholder="./images/noimage.png"
      class="article-image"
    />

    <div class="article-title">{{ item.title }}</div>

    <div class="article-time">
      <IconCSS name="iconoir:clock" size="16" class="icon"></IconCSS>
      <span v-if="item.time" class="label">{{ parseFloat(item.time).toFixed(0) }} {{ t('label.min_read') }}</span>
    </div>
  </NuxtLink>
</template>