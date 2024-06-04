<script setup>
import {useCart} from '~/composables/product/useCart.ts'

const props = defineProps({
  product: {
    type: Object
  }
})

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
      <span class="brand-label">{{ $t('label.brand') }}:</span>
      <NuxtLink :to="localePath('/brands/' + product.brand.slug)" class="brand-value text-link">
        <span>{{ product.brand.name }}</span>
      </NuxtLink>
    </div>

    <div class="available">
      <product-available :in-stock="product.inStock" type="full"></product-available>
    </div>
    
    <div class="sale">

      <product-price
        :price="product.price"
        :old-price="product.oldPrice"
        dir="left"
        class="price-block"
      ></product-price>

      <button @click="toCartHandler" class="button primary">{{ $t('button.buy') }}</button>
      
      <button @click="oneClickHandler" class="button color-primary inline-icon">
        <IconCSS name="iconoir:flash" class="icon"></IconCSS>
        <span>{{ $t('button.1_click_buy') }}</span>
      </button>
    </div>
  </div>
</template>