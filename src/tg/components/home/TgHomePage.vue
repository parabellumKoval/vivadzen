<script setup lang="ts">
const { t } = useTgI18n()
const { categoryPath } = useTgRouting()

const perks = computed(() => [
  {
    key: 'delivery',
    icon: '🚚',
    title: t('perk_delivery_title'),
    text: t('perk_delivery_text')
  },
  {
    key: 'quality',
    icon: '✓',
    title: t('perk_quality_title'),
    text: t('perk_quality_text')
  },
  {
    key: 'payment',
    icon: '💳',
    title: t('perk_payment_title'),
    text: t('perk_payment_text')
  },
  {
    key: 'support',
    icon: '💬',
    title: t('perk_support_title'),
    text: t('perk_support_text')
  }
])
</script>

<template>
  <TgLayout :title="t('home')" :show-lang="true">
    <div class="home-page">
      <TgGoogleReviewsSlider />

      <section class="tg-page home-perks">
        <div class="home-perks__head">
          <h2 class="tg-title">{{ t('perks_title') }}</h2>
        </div>
        <div class="home-perks__grid">
          <div
            v-for="perk in perks"
            :key="perk.key"
            class="home-perks__item"
          >
            <div class="home-perks__icon">{{ perk.icon }}</div>
            <div class="home-perks__body">
              <div class="home-perks__title">{{ perk.title }}</div>
              <div class="home-perks__text">{{ perk.text }}</div>
            </div>
          </div>
        </div>
        <NuxtLink :to="categoryPath()" class="tg-btn home-perks__cta" :prefetch="false">
          {{ t('home_open_catalog') }}
        </NuxtLink>
      </section>
    </div>
  </TgLayout>
</template>

<style scoped>
.home-page {
  display: grid;
  gap: 20px;
  padding-top: 16px;
}

.home-perks {
  display: grid;
  gap: 12px;
}

.home-perks__head {
  display: grid;
  gap: 4px;
}

.home-perks__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.home-perks__item {
  display: grid;
  gap: 6px;
  padding: 12px;
  border-radius: var(--radius-lg);
  background: var(--color-white);
  box-shadow: var(--shadow-card);
}

.home-perks__icon {
  display: grid;
  place-items: center;
  width: 32px;
  height: 32px;
  border-radius: var(--radius-full);
  background: var(--color-bg-card);
  font-size: 16px;
  line-height: 1;
}

.home-perks__title {
  font-size: 13px;
  font-weight: 700;
  color: var(--color-text);
}

.home-perks__text {
  font-size: 12px;
  line-height: 1.45;
  color: var(--color-text-muted);
}

.home-perks__cta {
  margin-top: 4px;
  text-align: center;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
</style>
