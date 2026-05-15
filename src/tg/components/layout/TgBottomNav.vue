<script setup lang="ts">
import { useTgCartStore } from '~/stores/cart'
import { useTgUiStore } from '~/stores/ui'

const route = useRoute()
const ui = useTgUiStore()
const cart = useTgCartStore()
const { t } = useTgI18n()
const { homePath, catalogPath, pathFor } = useTgRouting()

const items = computed(() => [
  {
    key: 'home',
    label: t('home'),
    icon: 'home',
    to: homePath(),
    active: route.name?.toString().includes('region-locale-index')
  },
  {
    key: 'catalog',
    label: t('catalog'),
    icon: 'grid',
    to: catalogPath(),
    active: route.name?.toString().includes('region-locale-catalog')
  },
  {
    key: 'orders',
    label: t('orders'),
    icon: 'package',
    to: pathFor('orders'),
    active: route.path.endsWith('/orders')
  },
  {
    key: 'account',
    label: t('account'),
    icon: 'user',
    to: pathFor('account'),
    active: route.path.endsWith('/account')
  }
])
</script>

<template>
  <nav class="tg-bottom-nav" aria-label="Main navigation">
    <NuxtLink
      v-for="item in items.slice(0, 2)"
      :key="item.key"
      :to="item.to"
      class="tg-bottom-nav__item"
      :class="{ active: item.active }"
      :prefetch="false"
    >
      <span class="tg-bottom-nav__icon">
        <TgIcon :name="item.icon" :size="22" :stroke="2.2" />
      </span>
      <span class="tg-bottom-nav__label">{{ item.label }}</span>
    </NuxtLink>

    <button
      type="button"
      class="tg-bottom-nav__item tg-bottom-nav__cart"
      :class="{ active: ui.cartOpen }"
      @click="ui.openCart()"
    >
      <span class="tg-bottom-nav__cart-disk">
        <TgIcon name="bag" :size="26" :stroke="2.4" />
      </span>
      <span v-if="cart.totalQty" class="tg-bottom-nav__badge">{{ cart.totalQty }}</span>
      <span class="tg-bottom-nav__label">{{ t('cart') }}</span>
    </button>

    <NuxtLink
      v-for="item in items.slice(2)"
      :key="item.key"
      :to="item.to"
      class="tg-bottom-nav__item"
      :class="{ active: item.active }"
      :prefetch="false"
    >
      <span class="tg-bottom-nav__icon">
        <TgIcon :name="item.icon" :size="22" :stroke="2.2" />
      </span>
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
  grid-template-columns: repeat(5, 1fr);
  width: 100%;
  max-width: 480px;
  height: calc(64px + env(safe-area-inset-bottom));
  margin: 0 auto;
  border-top: 2px solid var(--color-ink);
  background: var(--color-white);
  padding-bottom: env(safe-area-inset-bottom);
}

.tg-bottom-nav__item {
  position: relative;
  display: flex;
  min-width: 0;
  border: 0;
  background: transparent;
  color: #555;
  align-items: center;
  flex-direction: column;
  justify-content: center;
  gap: 3px;
  font-family: var(--font-display);
  font-size: 10px;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.tg-bottom-nav__item.active {
  color: var(--color-ink);
}

.tg-bottom-nav__icon {
  display: grid;
  place-items: center;
  height: 24px;
}

.tg-bottom-nav__item.active .tg-bottom-nav__icon::after {
  content: '';
  position: absolute;
  top: 6px;
  width: 32px;
  height: 6px;
  border-radius: var(--radius-full);
  background: var(--color-lime);
  z-index: -1;
}

.tg-bottom-nav__label {
  max-width: 100%;
  overflow: hidden;
  padding: 0 4px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.tg-bottom-nav__cart {
  position: relative;
}

.tg-bottom-nav__cart-disk {
  display: grid;
  width: 52px;
  height: 52px;
  margin-top: -22px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-accent);
  color: var(--color-white);
  place-items: center;
  box-shadow: var(--shadow-card-sm);
}

.tg-bottom-nav__cart.active .tg-bottom-nav__cart-disk {
  background: var(--color-ink);
}

.tg-bottom-nav__badge {
  position: absolute;
  top: -28px;
  right: calc(50% - 28px);
  min-width: 22px;
  height: 22px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-lime);
  color: var(--color-ink);
  padding: 0 5px;
  font-family: var(--font-display);
  font-size: 11px;
  line-height: 18px;
}
</style>
