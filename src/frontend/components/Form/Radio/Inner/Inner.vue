<script setup>
const props = defineProps({
  image: {
    type: String,
    required: false
  },
  title: {
    type: String,
    required: true
  }
})
</script>
<style src="./inner.scss" lang="scss" scoped />

<template>
  <div class="inner">
    <nuxt-img
      v-if="image"
      :src = "image"
      :alt = "title"
      :title = "title"
      width = "70"
      height = "20"
      sizes = "mobile:70px tablet:70px desktop:100px"
      format = "webp"
      fit = "outside"
      quality = "100"
      loading = "lazy"
      class="image"
     />
    <div class="title">{{ title }}</div>
  </div>
</template>