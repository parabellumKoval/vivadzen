<script setup>
import {useCard} from '~/composables/product/useCard.ts'

const { t } = useI18n()
const regionPath = useToLocalePath()
const props = defineProps({
  item: {
    type: Object
  }
})

const {photo} = useCard(props.item)
const amount = ref(1)

// COMPUTED
const totalPrice = computed(() => {
  return props.item.amount * props.item.price
})

// const photo = computed(() => {
//   const config = useRuntimeConfig()
//   return props.item.image? config.public.serverBase + '/' + props.item.image : '/images/noimage.png'
// })
// HANDLER
const deleteHandler = () => {}
</script>

<style src="./static.scss" lang="scss" scoped></style>

<template>
<div class="product-static-wrapper">
  <NuxtLink :to="regionPath('/' + item.slug)" :aria-label="item.name" clickable class="image-wrapper">
    <nuxt-img
      :src = "photo"
      width="100"
      height="100"
      sizes = "mobile:100px tablet:100px desktop:120px"
      format = "webp"
      provider = "bunny"
      quality = "80"
      loading = "lazy"
      fit="outside"
      placeholder="/images/noimage.png"
      class="image"
    >
    </nuxt-img> 
  </NuxtLink>
  <div class="body">
    <span v-if="item.code" class="code label">
      {{ t('label.product_code') }}: 
      <span class="value">{{ item.code }}</span>
    </span>
    <NuxtLink
      :to="regionPath('/' + item.slug)"
      :aria-label="item.name"
      clickable
      class="name"
    >{{ item.name }}</NuxtLink>
  </div>
  <div class="footer">
    <div class="price-ditails">
      <simple-price v-if="+item.price" :value="+item.price" :currency-code="item.currency" class="price"></simple-price>
      <span class="price-delimiter">X</span>
      <span class="price-amount">{{ item.amount }}</span>
    </div>
    <simple-price :value="totalPrice" class="price price-total"></simple-price>
  </div>
</div>
</template>
