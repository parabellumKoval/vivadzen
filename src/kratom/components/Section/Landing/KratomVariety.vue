<script setup>
const { t, te } = useI18n()

const baseModifications = [
  { weight: 25, price: '120-140' },
  { weight: 50, price: '180-250' },
]

const colorModal = defineAsyncComponent(() => import('~/components/Modal/KratomVariety/KratomVariety.vue'))

const openColorModal = (color) => {
  useModal().open(colorModal, color, null, { width: { min: 520, max: 900 } })
}

const colorConfigs = [
  {
    key: 'white',
    bg: '/images/landing/items/white.png',
    img: '/images/landing/items/circle2-1.png',
    color: '#f4e6b8',
    effects: [
      { key: 'fatigue', icon: 'ph:lightning' },
      { key: 'work', icon: 'ph:brain' },
      { key: 'clarity', icon: 'ph:arrow-up' },
      { key: 'sport', icon: 'ph:person-simple-run' }
    ]
  },
  {
    key: 'green',
    bg: '/images/landing/items/green.png',
    img: '/images/landing/items/circle2-3.png',
    color: '#73c56f',
    effects: [
      { key: 'social', icon: 'ph:chats' },
      { key: 'mood', icon: 'ph:battery-medium' },
      { key: 'creativity', icon: 'ph:wind' },
      { key: 'activity', icon: 'ph:bandaids' }
    ]
  },
  {
    key: 'red',
    bg: '/images/landing/items/red.png',
    img: '/images/landing/items/circle2-2.png',
    color: '#d16d3e',
    effects: [
      { key: 'pain', icon: 'ph:first-aid-kit' },
      { key: 'sleep', icon: 'ph:moon' },
      { key: 'stress', icon: 'ph:person-simple-walk' },
      { key: 'withdrawal', icon: 'ph:shield-check' }
    ]
  },
  {
    key: 'gold',
    bg: '/images/landing/items/gold.png',
    img: '/images/landing/items/circle2-4.png',
    color: '#dfc157',
    effects: [
      { key: 'burnout', icon: 'ph:smiley' },
      { key: 'anxiety', icon: 'ph:hand-heart' },
      { key: 'panic', icon: 'ph:heart' },
      { key: 'relax', icon: 'ph:leaf' }
    ]
  }
]

const modifications = computed(() =>
  baseModifications.map((item) => ({
    weight: `${item.weight} ${t('units.gram')}`,
    price: `${item.price} ${t('units.currency')}`
  }))
)

const colors = computed(() =>
  colorConfigs.map((item) => ({
    ...item,
    name: t(`colors.${item.key}.name`),
    effectsName: te(`colors.${item.key}.effects_name`) ? t(`colors.${item.key}.effects_name`) : t(`colors.${item.key}.name`),
    title: t(`colors.${item.key}.title`),
    desc: t(`colors.${item.key}.desc`),
    modifications: modifications.value,
    effects: item.effects.map((effect) => ({
      icon: effect.icon,
      label: t(`colors.${item.key}.effects.${effect.key}.label`),
      text: t(`colors.${item.key}.effects.${effect.key}.text`)
    }))
  }))
)

const effectsProduct = computed(() => (te('effects_product') ? t('effects_product') : t('kratom_word')))
</script>

<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section class="kratom-variety-section">
    <div class="kratom-matrix-hero">
      <div class="pixel-pattern"></div>
      <div class="kratom-matrix-hero__content">
        <h3 class="kratom-matrix-hero__title">{{ t('matrix.title') }}</h3>
        <p class="kratom-matrix-hero__desc">{{ t('matrix.subtitle') }}</p>
      </div>
      <div class="gradient"></div>
    </div>

    <div class="container">
      <div class="kratom-variety-section__delimiter"></div>
      <h2 class="kratom-variety-section__title">{{ t('section.title') }}</h2>
      <div class="kratom-variety-section__subtitle">{{ t('section.subtitle') }}</div>

      <div class="kratom-variety-section__colors">
        <div class="color-pairs">
          <div v-for="color in colors" :key="`${color.key}-pair`" class="color-pair">
            <div class="color-item">
              <div class="color-item__image-wrapper">
                <img :src="color.bg" :alt="color.name" class="color-item__image" loading="lazy" />
              </div>
              <p class="color-item__name">
                <span class="big">{{ color.name }}</span>
                <span class="same" :style="{ color: color.color }"> {{ t('kratom_word') }}</span>
              </p>
              <div class="color-item__desc">{{ color.desc }}</div>
              <button class="button color-secondary color-item__button" type="button" @click="openColorModal(color)">
                {{ t('buttons.choose', { color: color.name }) }}
              </button>
            </div>
            <div class="color-effects-section color-effects-section--inline">
              <div class="color-effects-section__lists">
                <div class="color-item__effects">
                  <div :style="{ color: color.color }" class="effect-item__name">
                    {{ t('effects_title', { color: color.effectsName, product: effectsProduct }) }}
                  </div>
                  <ul class="effects-list">
                    <li v-for="effect in color.effects" :key="effect.label" class="effect-item">
                      <IconCSS :name="effect.icon" class="effect-item__icon" :style="{ color: color.color }" />
                      <div>
                        <strong class="effect-label">{{ effect.label }}</strong>
                        <div class="effect-desc">{{ effect.text }}</div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="colors-list">
          <div v-for="color in colors" :key="color.key" class="color-item">
            <div class="color-item__image-wrapper">
              <img :src="color.bg" :alt="color.name" class="color-item__image" loading="lazy" />
            </div>
            <p class="color-item__name">
              <span class="big">{{ color.name }}</span>
              <span class="same" :style="{ color: color.color }"> {{ t('kratom_word') }}</span>
            </p>
            <div class="color-item__desc">{{ color.desc }}</div>
            <button class="button color-secondary color-item__button" type="button" @click="openColorModal(color)">
              {{ t('buttons.choose', { color: color.name }) }}
            </button>
          </div>
        </div>
        <div class="color-effects-section">
          <div class="color-effects-section__lists">
            <div v-for="color in colors" :key="`${color.key}-effects`" class="color-item__effects">
              <div :style="{ color: color.color }" class="effect-item__name">
                {{ t('effects_title', { color: color.effectsName, product: effectsProduct }) }}
              </div>
              <ul class="effects-list">
                <li v-for="effect in color.effects" :key="effect.label" class="effect-item">
                  <IconCSS :name="effect.icon" class="effect-item__icon" :style="{ color: color.color }" />
                  <div>
                    <strong class="effect-label">{{ effect.label }}</strong>
                    <div class="effect-desc">{{ effect.text }}</div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style lang="scss" src="./kratom-variety.scss"></style>
