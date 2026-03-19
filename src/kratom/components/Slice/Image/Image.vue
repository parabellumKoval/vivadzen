<script setup>
const props = defineProps({
  src: {
    type: String,
    required: true
  },
  alt: {
    type: String,
    default: ''
  },
  title: {
    type: String,
    default: ''
  },
  width: {
    type: [String, Number],
    default: null
  },
  height: {
    type: [String, Number],
    default: null
  },
  sizes: {
    type: String,
    default: 'mobile:100vw tablet:80vw desktop:60vw'
  },
  fit: {
    type: String,
    default: 'cover'
  },
  quality: {
    type: [String, Number],
    default: 90
  },
  loading: {
    type: String,
    default: 'lazy'
  }
})
</script>

<template>
  <figure class="slice-image">
    <nuxt-img
      :src = "props.src"
      :alt = "props.alt || props.title"
      :title = "props.title || props.alt"
      :width = "props.width ?? undefined"
      :height = "props.height ?? undefined"
      :sizes = "props.sizes"
      format = "webp"
      :fit = "props.fit"
      :quality = "props.quality"
      :loading = "props.loading"
      class="slice-image__media"
    />
    <!-- <figcaption v-if = "props.title" class="slice-image__caption">
      {{ props.title }}
    </figcaption> -->
  </figure>
</template>

<style src="./image.scss" lang="scss" scoped />
