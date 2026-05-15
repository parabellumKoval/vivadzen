<script setup lang="ts">
const { t } = useTgI18n()
const config = useRuntimeConfig()

const stats = computed(() => [
  { icon: 'leaf', value: '120+', label: t('about_stat_cbd') },
  { icon: 'mushroom', value: '40+', label: t('about_stat_mushrooms') },
  { icon: 'sparkles', value: '25+', label: t('about_stat_entheogens') }
])

const values = computed(() => [
  { icon: 'shield', title: t('about_value_lab_title'), text: t('about_value_lab_text') },
  { icon: 'truck', title: t('about_value_ship_title'), text: t('about_value_ship_text') },
  { icon: 'heart', title: t('about_value_care_title'), text: t('about_value_care_text') },
  { icon: 'globe', title: t('about_value_worldwide_title'), text: t('about_value_worldwide_text') }
])
</script>

<template>
  <TgLayout :title="t('about')" :show-back="true" :show-lang="true">
    <section class="tg-page about-page">
      <div class="about-hero">
        <span class="about-hero__pill">
          <TgIcon name="flame" :size="12" :stroke="2.4" />
          {{ t('about_hero_pill') }}
        </span>
        <h1 class="about-hero__title">{{ t('about_hero_title_1') }}<br><span>{{ t('about_hero_title_2') }}</span></h1>
        <p class="about-hero__sub">
          {{ t('about_hero_sub') }}
        </p>
      </div>

      <div class="about-stats">
        <div v-for="stat in stats" :key="stat.label" class="about-stats__cell">
          <span class="about-stats__icon">
            <TgIcon :name="stat.icon" :size="22" :stroke="2.2" />
          </span>
          <strong>{{ stat.value }}</strong>
          <span class="about-stats__label">{{ stat.label }}</span>
        </div>
      </div>

      <div class="about-values">
        <article v-for="value in values" :key="value.title" class="value-card">
          <span class="value-card__icon">
            <TgIcon :name="value.icon" :size="22" :stroke="2.2" />
          </span>
          <strong>{{ value.title }}</strong>
          <p>{{ value.text }}</p>
        </article>
      </div>

      <a :href="config.public.siteUrl" target="_blank" rel="noopener" class="tg-btn tg-btn--ink">
        <TgIcon name="arrow-right" :size="16" :stroke="2.2" />
        {{ t('open_site') }}
      </a>
    </section>
  </TgLayout>
</template>

<style scoped>
.about-page {
  display: grid;
  gap: 18px;
}

.about-hero {
  position: relative;
  overflow: hidden;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-xl);
  background: var(--color-ink);
  color: var(--color-white);
  padding: 24px 20px;
  box-shadow: var(--shadow-card);
}

.about-hero::before {
  content: '';
  position: absolute;
  top: -40px;
  right: -50px;
  width: 180px;
  height: 180px;
  border-radius: 50%;
  background: radial-gradient(circle, var(--color-lime) 0%, transparent 70%);
  opacity: 0.45;
  pointer-events: none;
}

.about-hero__pill {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 2px solid var(--color-lime);
  border-radius: var(--radius-full);
  background: rgba(198, 244, 50, 0.12);
  color: var(--color-lime);
  padding: 5px 11px;
  font-family: var(--font-display);
  font-size: 10px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.about-hero__title {
  position: relative;
  margin: 14px 0 10px;
  font-family: var(--font-display);
  font-size: 38px;
  letter-spacing: -0.02em;
  line-height: 0.92;
  text-transform: uppercase;
}

.about-hero__title span {
  color: var(--color-lime);
}

.about-hero__sub {
  position: relative;
  margin: 0;
  color: rgba(255, 255, 255, 0.78);
  font-size: 14px;
  font-weight: 500;
  line-height: 1.5;
}

.about-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.about-stats__cell {
  display: grid;
  gap: 4px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-lg);
  background: var(--color-white);
  padding: 12px 10px;
  text-align: left;
  box-shadow: var(--shadow-card-sm);
}

.about-stats__icon {
  display: grid;
  width: 32px;
  height: 32px;
  margin-bottom: 4px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-lime);
  color: var(--color-ink);
  place-items: center;
}

.about-stats__cell strong {
  font-family: var(--font-display);
  font-size: 22px;
  letter-spacing: -0.01em;
}

.about-stats__label {
  color: var(--color-text-muted);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.about-values {
  display: grid;
  gap: 10px;
  grid-template-columns: 1fr 1fr;
}

.value-card {
  display: grid;
  gap: 6px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-lg);
  background: var(--color-white);
  padding: 14px;
  box-shadow: var(--shadow-card-sm);
}

.value-card:nth-child(1) { background: var(--color-lime); }
.value-card:nth-child(2) { background: var(--color-yellow); }
.value-card:nth-child(3) { background: var(--color-bg-alt); }
.value-card:nth-child(4) { background: var(--color-white); }

.value-card__icon {
  display: grid;
  width: 36px;
  height: 36px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-white);
  color: var(--color-ink);
  place-items: center;
}

.value-card strong {
  font-family: var(--font-display);
  font-size: 14px;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.value-card p {
  margin: 0;
  color: var(--color-text-muted);
  font-size: 12px;
  font-weight: 500;
  line-height: 1.45;
}
</style>
