<script setup>
import {useCart} from '~/composables/product/useCart.ts'
const {t} = useI18n()

const props = defineProps({
  product: {
    type: Object
  }
})

console.log('product brand', props.product)
const {toCartHandler} = useCart(props.product)

// HANDLERS
const oneClickHandler = () => {
  useModal().open(resolveComponent('Modal1Click'), props.product, null, {width: {min: 420, max: 420}})
}
</script>

<style src="./block.scss" lang="scss" scoped></style>

<template>
  <div class="block">
    <div v-if="product.brand" class="brand">
      <span class="brand-label">{{ t('label.brand') }}:</span>
      <NuxtLink :to="localePath('/brands/' + product.brand.slug)" class="brand-value text-link">
        <span>{{ product.brand.name }}</span>
      </NuxtLink>
    </div>

    <div class="available">
      <product-available :in-stock="product.inStock" type="full"></product-available>
      <!-- <span class="text">
        <span class="status">Есть в наличие</span>
        <span class="comment"> / заканчивается</span>
      </span> -->
    </div>
    
    <div class="sale">

      <product-price
        :price="product.price"
        :old-price="product.oldPrice"
        dir="left"
        class="price-block"
      ></product-price>
      <!-- <div class="price-block">
        <simple-price :value="product.oldPrice" class="price-old"></simple-price>
        <simple-price :value="product.price" class="price-base"></simple-price>
      </div> -->

      <button @click="toCartHandler" class="button primary">{{ t('button.buy') }}</button>
      
      <button @click="oneClickHandler" class="button color-primary inline-icon">
        <IconCSS name="iconoir:flash" class="icon"></IconCSS>
        <span>{{ t('button.1_click_buy') }}</span>
      </button>
    </div>
  </div>
</template>