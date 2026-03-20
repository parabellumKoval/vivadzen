<script setup>
import {useCartStore} from '~/store/cart'

const { t } = useI18n()
const regionPath = useToLocalePath()
const {fetchCartProducts} = useCartStore()
// COMPUTEDS
const products = computed(() => {
  return useCartStore().cart
})

// HANDLERS
const closeHandler = () => {
  useModal().close()
}

await useAsyncData('cart-products-modal', async () => {
  try {
    return await fetchCartProducts()
  } catch (error) {
    console.error('[modal-cart] Failed to load cart products', error)
    return []
  }
})
</script>

<style src="./cart.scss" lang="scss" scoped />
<!-- <i18n src="./lang.yaml" lang="yaml"></i18n> -->

<template>
  <modal-wrapper :title="t('title.cart')">
    <div class="cart">
      <div class="body" scrollable>
        <div class="body-inner">
          <div v-if="products.length">
            <div class="body-label">{{ t('messages.in_your_cart', { products: products.length }) }}</div>
            <transition-group name="fade-in">
              <product-card-checkout
                v-for="product in products"
                :key="product.id"
                :item="product"
                class="product-card"
                edit
              >
              </product-card-checkout>
            </transition-group>
          </div>
          <div v-else>
            {{ $t('messages.empty_cart') }}
          </div>
        </div>
      </div>


      <div v-if="useCartStore().total" class="footer">
        
        <NuxtLink @click="closeHandler" :to="regionPath('/checkout')" class="button primary checkout-btn" type="button">
          <span>{{ $t('title.checkout') }}</span>
        </NuxtLink>

        <div class="total">
          <div class="label">{{ $t('label.total') }}:</div>
          <simple-price :value="useCartStore().total" class="price"></simple-price>
        </div>
      </div>
    </div>
  </modal-wrapper>
</template>
