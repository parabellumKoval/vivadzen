
<script setup>
const props = defineProps({
  item: {
    type: Object
  },
  index: {
    type: Number,
    default: null
  }
})

const {t} = useI18n()
const regionPath = useToLocalePath()

const loading = computed(() => {
  if(props.index === null || props.index !== 0)
    return 'lazy'
  else {
    return null
  }
})
</script>

<style src="./card.scss" lang="scss" scoped></style>

<template>
  <NuxtLink :to="regionPath('/blog/' + item.slug)" class="article-card">
    <div class="article-image-wrapper">
      <nuxt-img
        :src = "item.image?.src"
        :alt = "item.image?.alt || item.name"
        :title = "item.image?.title || item.name"
        :class = "item.image?.size"
        width = "290"
        height = "290"
        sizes = "mobile:70vw tablet:230px desktop:300px"
        format = "avif"
        quality = "60"
        :loading = "loading"
        fit = "outside"
        :placeholder="useImg().noImage"
        class = "article-image"
      />

    </div>

    <div class="article-title">{{ item.title }}</div>

    <div class="article-time">
      <IconCSS name="iconoir:clock" size="16" class="icon"></IconCSS>
      <span v-if="item.time" class="label">{{ parseFloat(item.time).toFixed(0) }} {{ t('label.min_read') }}</span>
    </div>
  </NuxtLink>
</template>
