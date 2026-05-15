<script setup lang="ts">
import { useTgCartStore } from '~/stores/cart'

const route = useRoute()
const cart = useTgCartStore()
const { t } = useTgI18n()
const { pathFor, catalogPath } = useTgRouting()
const { haptic, showAlert } = useTelegram()

const orderCode = computed(() => {
  return String(route.query.order || cart.flashOrder?.code || cart.flashOrder?.id || '')
})

onMounted(() => {
  haptic('success')
  showAlert(t('order_success'))
})
</script>

<template>
  <TgLayout :title="t('thank_you')" :show-lang="true">
    <section class="thank-page">
      <div class="thank-page__icon"><TgIcon name="check" :size="38" :stroke="3" /></div>
      <h1>{{ t('order_success') }}</h1>
      <p v-if="orderCode" class="thank-page__code">#{{ orderCode }}</p>
      <p>{{ t('order_success_text') }}</p>
      <div class="thank-page__actions">
        <NuxtLink :to="pathFor('orders')" class="tg-btn">{{ t('go_to_orders') }}</NuxtLink>
        <NuxtLink :to="catalogPath()" class="tg-btn tg-btn--ghost">{{ t('back_to_catalog') }}</NuxtLink>
      </div>
    </section>
  </TgLayout>
</template>

<style scoped>
.thank-page {
  display: grid;
  min-height: calc(var(--tg-viewport-height, 100dvh) - 140px);
  padding: 32px 24px;
  place-content: center;
  text-align: center;
}

.thank-page__icon {
  display: grid;
  width: 80px;
  height: 80px;
  margin: 0 auto 18px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-lime);
  color: var(--color-ink);
  place-items: center;
  box-shadow: var(--shadow-card);
}

.thank-page h1 {
  margin: 0 0 8px;
  font-size: 22px;
}

.thank-page p {
  margin: 0 auto 18px;
  max-width: 300px;
  color: var(--color-text-muted);
  font-size: 14px;
  line-height: 1.5;
}

.thank-page__code {
  color: var(--color-accent) !important;
  font-size: 18px !important;
  font-weight: 800;
}

.thank-page__actions {
  display: grid;
  gap: 8px;
}
</style>
