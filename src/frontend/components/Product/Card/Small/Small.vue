<script setup>
import {useCard} from '~/composables/product/useCard.ts'

const { t } = useI18n()

const props = defineProps({
  item: {
    type: Object
  }
})

const amount = ref(1)
const {photo, photoAlt, photoTitle, photoSize} = useCard(props.item)

const deleteHandler = () => {}
</script>

<style src="./small.scss" lang="scss" scoped></style>

<template>
<div class="product">
  <div class="product-inner">
    <nuxt-img
      :src = "photo"
      :alt = "photoAlt"
      :title = "photoTitle"
      :class = "photoSize"
      width="100"
      height="100"
      sizes = "mobile:100px tablet:100px desktop:100px"
      format = "webp"
      quality = "60"
      loading = "lazy"
      fit="outside"
      placeholder="./images/noimage.png"
      class="image"
    >
    </nuxt-img>

    <div class="body">
      <!-- <span v-if="item.code" class="code label">
        {{ t('label.product_code') }}: 
        <span class="value">{{ item.code }}</span>
      </span> -->
      <span class="name">{{ item.name }}</span>
      <product-price
        :price="item.price"
        :old-price="item.oldPrice"
        class="price-block"
      ></product-price>
    </div>
    
  </div>

</div>
</template>