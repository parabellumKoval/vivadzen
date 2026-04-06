<script setup lang="ts">
const props = withDefaults(defineProps<{
  variant?: 'footer' | 'modal'
}>(), {
  variant: 'footer',
})

const { t } = useI18n()

const regions = computed(() => [
  {
    key: 'ua',
    icon: 'emojione:flag-for-ukraine',
    href: 'https://shop.vivadzen.com/ua',
    title: t('kratom.region_switcher.regions.ua.title'),
    subtitle: t('kratom.region_switcher.regions.ua.subtitle'),
  },
  {
    key: 'es',
    icon: 'emojione:flag-for-spain',
    href: 'https://shop.vivadzen.com/es',
    title: t('kratom.region_switcher.regions.es.title'),
    subtitle: t('kratom.region_switcher.regions.es.subtitle'),
  },
  {
    key: 'de',
    icon: 'emojione:flag-for-germany',
    href: 'https://shop.vivadzen.com/de',
    title: t('kratom.region_switcher.regions.de.title'),
    subtitle: t('kratom.region_switcher.regions.de.subtitle'),
  },
  {
    key: 'cz-other',
    icon: 'emojione:flag-for-czechia',
    href: 'https://shop.vivadzen.com/cz',
    title: t('kratom.region_switcher.regions.cz_other.title'),
    subtitle: t('kratom.region_switcher.regions.cz_other.subtitle'),
  },
])
</script>

<template>
  <section
    :class="[
      'kratom-region-switcher',
      `kratom-region-switcher--${props.variant}`,
    ]"
  >
    <div class="kratom-region-switcher__content">
      <p class="kratom-region-switcher__eyebrow">{{ t('kratom.region_switcher.eyebrow') }}</p>
      <h2 class="kratom-region-switcher__title">{{ t('kratom.region_switcher.title') }}</h2>
      <p class="kratom-region-switcher__text">{{ t('kratom.region_switcher.text') }}</p>
    </div>

    <div class="kratom-region-switcher__list">
      <a
        v-for="item in regions"
        :key="item.key"
        :href="item.href"
        class="kratom-region-switcher__item"
      >
        <span class="kratom-region-switcher__flag">
          <Icon :name="item.icon" size="24" />
        </span>

        <span class="kratom-region-switcher__item-copy">
          <span class="kratom-region-switcher__item-title">{{ item.title }}</span>
          <span class="kratom-region-switcher__item-text">{{ item.subtitle }}</span>
        </span>

        <IconCSS
          name="ph:arrow-up-right"
          size="18"
          class="kratom-region-switcher__arrow"
        />
      </a>
    </div>
  </section>
</template>

<style scoped lang="scss">
.kratom-region-switcher {
  display: grid;
  gap: 24px;
  padding: 24px;
  border: 1px solid rgba(217, 191, 150, 0.22);
  border-radius: 28px;
  background:
    radial-gradient(circle at top right, rgba(217, 191, 150, 0.16), transparent 35%),
    linear-gradient(180deg, rgba(255, 247, 236, 0.08), rgba(255, 247, 236, 0.04));
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);

  @include desktop {
    grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
    gap: 28px;
    padding: 30px;
  }
}

.kratom-region-switcher--modal {
  grid-template-columns: 1fr;
  padding: 22px;
  border-radius: 26px;
  background:
    radial-gradient(circle at top right, rgba(217, 191, 150, 0.18), transparent 34%),
    linear-gradient(180deg, #fff9ef, #f5ead9);
  border-color: rgba(138, 90, 43, 0.16);
  box-shadow: 0 30px 80px rgba(15, 20, 16, 0.24);

  .kratom-region-switcher__eyebrow {
    color: #8a5a2b;
  }

  .kratom-region-switcher__title,
  .kratom-region-switcher__text,
  .kratom-region-switcher__item-title,
  .kratom-region-switcher__item-text,
  .kratom-region-switcher__arrow {
    color: #172019;
  }

  .kratom-region-switcher__item {
    background: rgba(255, 255, 255, 0.74);
    border-color: rgba(138, 90, 43, 0.12);
  }

  .kratom-region-switcher__flag {
    background: rgba(138, 90, 43, 0.12);
  }

  .kratom-region-switcher__content {
    padding-right: 34px;
  }

  .kratom-region-switcher__title {
    font-size: 18px;
    line-height: 1.2;
  }

  .kratom-region-switcher__text {
    margin-top: 10px;
    font-size: 14px;
    line-height: 1.55;
  }

  .kratom-region-switcher__list {
    grid-template-columns: 1fr;
  }

  .kratom-region-switcher__item {
    padding: 14px 15px;
    border-radius: 18px;
  }

  .kratom-region-switcher__item-title {
    font-size: 14px;
  }

  .kratom-region-switcher__item-text {
    font-size: 12px;
  }
}

.kratom-region-switcher__eyebrow {
  margin-bottom: 12px;
  color: #d9bf96;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 700;
}

.kratom-region-switcher__title {
  margin: 0;
  color: #fff7ec;
  font-size: 24px;
  line-height: 1.15;

  @include tablet {
    font-size: 28px;
  }
}

.kratom-region-switcher__text {
  margin: 14px 0 0;
  color: rgba(255, 247, 236, 0.78);
  line-height: 1.65;
}

.kratom-region-switcher__list {
  display: grid;
  gap: 12px;

  @include tablet {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.kratom-region-switcher__item {
  min-width: 0;
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  border-radius: 20px;
  border: 1px solid rgba(255, 247, 236, 0.1);
  background: rgba(255, 247, 236, 0.05);
  color: inherit;
  text-decoration: none;
  transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;

  &:hover {
    transform: translateY(-2px);
    border-color: rgba(217, 191, 150, 0.35);
    background: rgba(255, 247, 236, 0.09);
  }
}

.kratom-region-switcher__flag {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  background: rgba(255, 247, 236, 0.08);
  flex-shrink: 0;
}

.kratom-region-switcher__item-copy {
  min-width: 0;
  display: grid;
  gap: 4px;
}

.kratom-region-switcher__item-title {
  color: #fff7ec;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.35;
}

.kratom-region-switcher__item-text {
  color: rgba(255, 247, 236, 0.68);
  font-size: 13px;
  line-height: 1.45;
}

.kratom-region-switcher__arrow {
  color: rgba(255, 247, 236, 0.72);
  flex-shrink: 0;
}
</style>
