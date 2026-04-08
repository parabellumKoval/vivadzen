<script setup lang="ts">
import { useTgCartStore } from '~/stores/cart'
import { useTgUiStore } from '~/stores/ui'

const route = useRoute()
const ui = useTgUiStore()
const cart = useTgCartStore()
const { t } = useTgI18n()
const { pathFor } = useTgRouting()

const items = computed(() => [
  {
    key: 'catalog',
    label: t('catalog'),
    icon: '⌂',
    to: pathFor(),
    active: route.name?.toString().includes('region-locale-index') || route.name?.toString().includes('region-locale-category')
  },
  {
    key: 'orders',
    label: t('orders'),
    icon: '☰',
    to: pathFor('orders'),
    active: route.path.endsWith('/orders')
  },
  {
    key: 'account',
    label: t('account'),
    icon: '○',
    to: pathFor('account'),
    active: route.path.endsWith('/account')
  }
])
</script>

<template>
  <nav class="tg-bottom-nav" aria-label="Main navigation">
    <NuxtLink
      :to="items[0].to"
      class="tg-bottom-nav__item"
      :class="{ active: items[0].active }"
      :prefetch="false"
    >
      <span class="tg-bottom-nav__icon">{{ items[0].icon }}</span>
      <span class="tg-bottom-nav__label">{{ items[0].label }}</span>
    </NuxtLink>

    <button
      type="button"
      class="tg-bottom-nav__item tg-bottom-nav__cart"
      :class="{ active: ui.cartOpen }"
      @click="ui.openCart()"
    >
      <span class="tg-bottom-nav__icon">□</span>
      <span v-if="cart.totalQty" class="tg-bottom-nav__badge">{{ cart.totalQty }}</span>
      <span class="tg-bottom-nav__label">{{ t('cart') }}</span>
    </button>

    <NuxtLink
      v-for="item in items.slice(1)"
      :key="item.key"
      :to="item.to"
      class="tg-bottom-nav__item"
      :class="{ active: item.active }"
      :prefetch="false"
    >
      <span class="tg-bottom-nav__icon">{{ item.icon }}</span>
      <span class="tg-bottom-nav__label">{{ item.label }}</span>
    </NuxtLink>
  </nav>
</template>

<style scoped>
.tg-bottom-nav {
  position: fixed;
  z-index: 100;
  right: 0;
  bottom: 0;
  left: 0;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  width: 100%;
  max-width: 480px;
  height: calc(56px + env(safe-area-inset-bottom));
  margin: 0 auto;
  border-top: 1px solid var(--color-border);
  background: var(--color-white);
  padding-bottom: env(safe-area-inset-bottom);
}

.tg-bottom-nav__item {
  position: relative;
  display: flex;
  min-width: 0;
  border: 0;
  background: transparent;
  color: var(--color-text-light);
  align-items: center;
  flex-direction: column;
  justify-content: center;
  gap: 2px;
  font-size: 11px;
  font-weight: 600;
}

.tg-bottom-nav__item.active {
  color: var(--color-primary);
}

.tg-bottom-nav__icon {
  font-size: 20px;
  line-height: 1;
}

.tg-bottom-nav__label {
  max-width: 100%;
  overflow: hidden;
  padding: 0 4px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.tg-bottom-nav__badge {
  position: absolute;
  top: 7px;
  right: calc(50% - 22px);
  min-width: 18px;
  height: 18px;
  border-radius: var(--radius-full);
  background: var(--color-accent);
  color: var(--color-white);
  padding: 0 5px;
  font-size: 10px;
  font-weight: 700;
  line-height: 18px;
}
</style>
