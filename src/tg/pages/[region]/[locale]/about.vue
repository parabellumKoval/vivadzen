<script setup lang="ts">
const { t } = useTgI18n()
const config = useRuntimeConfig()
</script>

<template>
  <TgLayout :title="t('about')" :show-back="true" :show-lang="true">
    <section class="tg-page about-page">
      <div class="about-page__logo">Vivadzen</div>
      <div class="tg-card about-page__card">
        <h1>{{ t('about') }}</h1>
        <p>
          Vivadzen offers botanicals, tea, CBD, kratom and related wellness products with a focus on verified quality and clear service.
        </p>
        <p>
          Orders are handled by the same Vivadzen team, with regional delivery options and support after purchase.
        </p>
        <a :href="config.public.siteUrl" target="_blank" rel="noopener" class="tg-btn tg-btn--outline">
          {{ t('open_site') }}
        </a>
      </div>
    </section>
  </TgLayout>
</template>

<style scoped>
.about-page {
  display: grid;
  gap: 16px;
}

.about-page__logo {
  border-radius: var(--radius-lg);
  background: var(--color-primary);
  color: var(--color-white);
  padding: 24px 16px;
  text-align: center;
  font-size: 24px;
  font-weight: 900;
}

.about-page__card {
  display: grid;
  gap: 12px;
  padding: 16px;
}

.about-page__card h1,
.about-page__card p {
  margin: 0;
}

.about-page__card p {
  color: var(--color-text-muted);
  font-size: 14px;
  line-height: 1.55;
}
</style>
