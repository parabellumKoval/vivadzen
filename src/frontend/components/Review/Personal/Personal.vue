<script setup>
const props = defineProps({
  item: {
    type: Object
  }
})

const photo = computed(() => {
  if(props.item.author?.photo) {
    return props.item.author.photo
    // return '/server/' + props.item.image.src
  } else {
    return null
  }
})
</script>

<style src="./personal.scss" lang="scss" scoped></style>

<template>
  <div class="personal-wrapper">
    <nuxt-img
      v-if="photo"
      :src = "photo"
      :alt = "item.author.name"
      :title = "item.author.name"
      width="130"
      height="130"
      sizes = "mobile:100vw tablet:130px desktop:130px"
      format = "webp"
      quality = "60"
      loading = "lazy"
      fit="outside"
      class="image"
    >
    </nuxt-img> 

    <a href="/" target="_blank" class="link">
      <IconCSS name="basil:instagram-outline" class="icon"></IconCSS>
      <span class="name">{{ item.author.name }}</span>
    </a>

    <div class="content">
      <div class="text">{{ item.text }}</div>
      <div class="date">{{ $d(item.created_at, 'short') }}</div>
    </div>
  </div>
</template>