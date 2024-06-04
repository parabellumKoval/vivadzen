<script setup>
import {useCard} from '~/composables/product/useCard.ts'

const { t } = useI18n()

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

// HANDLER
const deleteHandler = () => {}
</script>

<style src="./static.scss" lang="scss" scoped></style>

<template>
<div class="product-static-wrapper">
  <NuxtLink :to="localePath('/' + item.slug)" :aria-label="item.name" clickable class="image-wrapper">
    <nuxt-img
      :src = "photo"
      width="100"
      height="100"
      sizes = "mobile:100px tablet:100px desktop:100px"
      format = "webp"
      quality = "60"
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
    <simple-price :value="totalPrice" class="price price-total"></simple-price>
  </div>
</div>
</template>