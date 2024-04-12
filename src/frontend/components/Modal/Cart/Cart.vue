<script setup>
import {useCartStore} from '~/store/cart'

const { t } = useI18n()


const products = computed(() => {
  return useCartStore().cart
})

const closeHandler = () => {
  useModal().close()
}
</script>

<style src="./cart.scss" lang="scss" scoped />
<!-- <i18n src="./lang.yaml" lang="yaml"></i18n> -->

<template>
  <modal-wrapper :title="t('title.cart')">
    <div class="cart">
      <div class="body" scrollable>
        <div v-if="products.length">
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

      <div v-if="useCartStore().total" class="footer">
        <div class="total">
          <div class="label">{{ $t('label.total') }}:</div>
          <simple-price :value="useCartStore().total" class="price"></simple-price>
        </div>

        <NuxtLink @click="closeHandler" :to="localePath('/checkout')" class="button primary checkout-btn" type="button">
          <span>{{ $t('title.checkout') }}</span>
        </NuxtLink>
      </div>
    </div>
  </modal-wrapper>
</template>