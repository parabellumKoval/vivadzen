<script setup>
const props = defineProps({
  item: {
    type: Object
  }
})

// COMPUTEDS
const photo = computed(() => {
  return props.item?.author?.photo || '/images/account.png'
})

const link = computed(() => {
  return props.item?.extras?.link || null
})
</script>

<style src="./personal.scss" lang="scss" scoped></style>

<template>
  <div class="personal-wrapper">
    <nuxt-img
      :src = "photo"
      :alt = "item?.author?.name"
      :title = "item?.author?.name"
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

    <review-social v-if="link" :source="link" :name="item?.author?.name"></review-social>

    <div class="content">
      <div class="text">{{ item.text }}</div>
      <div class="date">{{ $d(item.created_at, 'short') }}</div>
    </div>
  </div>
</template>