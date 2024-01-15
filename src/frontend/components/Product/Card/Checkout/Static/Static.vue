<script setup>
const { t } = useI18n()

const props = defineProps({
  item: {
    type: Object
  }
})

const amount = ref(1)

const photo = computed(() => {
  if(props.item.image?.src) {
    return props.item.image.src
    // return '/server/' + props.item.image.src
  } else {
    return null
  }
})

const deleteHandler = () => {}
</script>

<style src="./static.scss" lang="scss" scoped></style>

<template>
<div class="product-static-wrapper">
  <NuxtLink :to="localePath('/' + item.slug)" :aria-label="item.name" clickable class="image-wrapper">
    <nuxt-img
      v-if="photo"
      :src = "photo"
      :alt = "item.image.alt || item.name"
      :title = "item.image.title || item.name"
      :class="item.image.size"
      width="100"
      height="100"
      sizes = "mobile:100px tablet:100px desktop:100px"
      format = "webp"
      quality = "60"
      loading = "lazy"
      fit="outside"
      class="image"
    >
    </nuxt-img> 
  </NuxtLink>
  <div class="body">
    <span v-if="item.code" class="code label">
      код товара: 
      <span class="value">{{ item.code }}</span>
    </span>
    <NuxtLink
      :to="localePath('/' + item.slug)"
      :aria-label="item.name"
      clickable
      class="name"
    >{{ item.name }}</NuxtLink>
  </div>
  <div class="footer">
    <div class="price-ditails">
      <simple-price v-if="+item.price" :value="+item.price" class="price"></simple-price>
      <span class="price-delimiter">X</span>
      <span class="price-amount">{{ item.amount }}</span>
    </div>
    <simple-price :value="+item.price" class="price price-total"></simple-price>
  </div>
</div>
</template>