<script setup>
const {t} = useI18n()

const props = defineProps({
  product: {
    type: Object
  }
})

// HANDLERS
const oneClickHandler = () => {
  useModal().open(resolveComponent('Modal1Click'), props.product)
}
</script>

<style src="./block.scss" lang="scss" scoped></style>

<template>
  <div class="block">
    <div class="brand">
      <span class="brand-label">{{ t('label.brand') }}:</span>
      <NuxtLink :to="localePath('/')" class="brand-value text-link">
        <span>All be Ukraine</span>
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
      <div class="price-block">
        <simple-price :value="product.oldPrice" class="price-old"></simple-price>
        <simple-price :value="product.price" class="price-base"></simple-price>
      </div>
      <button class="button primary">{{ t('button.buy') }}</button>
      <button @click="oneClickHandler" class="button color-primary inline-icon">
        <IconCSS name="iconoir:flash" class="icon"></IconCSS>
        <span>{{ t('button.1_click_buy') }}</span>
      </button>
    </div>
  </div>
</template>