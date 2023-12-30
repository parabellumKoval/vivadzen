<script setup>
import { useCartStore } from '~~/store/cart';

const { t } = useI18n()

const openCartHandler = () => {
  useModal().open(resolveComponent('ModalCart'), null, null, {width: {
    min: 968, max: 968
  }})
}

const cartLength = computed(() => {
  return useCartStore().cart.length
})

</script>

<style src="./cart.scss" lang="scss" scoped />

<template>
  <button @click="openCartHandler" class="header-btn cart-btn" type="button" clickable>
    <IconCSS name="iconoir:shopping-bag" size="30px" class="icon"></IconCSS>
    <span class="hint">{{ t('cart') }}</span>
    <transition name="zoom">
      <div v-if="cartLength" class="budge">{{ cartLength }}</div>
    </transition>
  </button>
</template>

<i18n lang="yaml">
uk:
  cart: "Кошик"
ru:
  cart: "Корзина"
</i18n>