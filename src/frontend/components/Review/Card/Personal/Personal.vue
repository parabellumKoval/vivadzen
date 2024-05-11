<script setup>
const props = defineProps({
  item: {
    type: Object,
    required: true
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
  <div class="card">
    <div class="card-content">
      <simple-stars
        :amount="item.rating" 
        mobile="large"
      ></simple-stars>
      <div class="card-text">{{ item.text }}</div>
    </div>
    <div class="author">
      <nuxt-img
        :src = "photo"
        :alt = "item.author.name"
        :title = "item.author.name"
        width="50"
        height="50"
        sizes = "mobile:60px tablet:60px desktop:60px"
        format = "webp"
        quality = "60"
        loading = "lazy"
        fit="outside"
        class="author-image"
      >
      </nuxt-img>
      <div>
        <div class="author-name">{{ item.author.name }}</div>
        <review-social v-if="link" :source="link" class="author-source"></review-social>
      </div>
    </div>
  </div>
</template>