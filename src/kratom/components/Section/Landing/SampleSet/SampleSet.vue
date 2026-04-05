<script setup lang="ts">
const { t } = useI18n()

// 5 товаров веером в пределах 90 градусов (от -45 до +45)
// Центральный товар (id: 3) - 0 градусов, по бокам по 2 товара с шагом ~22.5 градуса
const products = [
  { id: 1, img: '/images/landing/product-gold.png', angle: -45, glowColor: '255, 215, 0' },      // Gold - крайний левый
  { id: 2, img: '/images/landing/product-red.png', angle: -22.5, glowColor: '220, 53, 69' },    // Red
  { id: 3, img: '/images/landing/product.png', angle: 0, glowColor: '255, 255, 255' },      // Green - центр
  { id: 4, img: '/images/landing/product-white.png', angle: 22.5, glowColor: '34, 197, 94' },     // White 
  { id: 5, img: '/images/landing/product-strawberry.png', angle: 45, glowColor: '255, 140, 0' },       // strawberry - крайний правый
]

import { ref, onMounted, onBeforeUnmount, type ComponentPublicInstance, defineAsyncComponent } from 'vue'

const leftDecor = ref<HTMLElement | ComponentPublicInstance | null>(null)
const rightDecor = ref<HTMLElement | ComponentPublicInstance | null>(null)
const sectionRef = ref<HTMLElement | null>(null)
const modal = useModal()
const SampleSetModal = defineAsyncComponent(() => import('./SampleSetModal.vue'))

const getEl = (target: HTMLElement | ComponentPublicInstance | null) => {
  if (!target) {
    return null
  }

  return (target as ComponentPublicInstance).$el ?? target
}

function handleParallax(e) {
  if (!sectionRef.value) return;
  const rect = sectionRef.value.getBoundingClientRect();
  const x = (e.clientX - rect.left) / rect.width - 0.5;
  const y = (e.clientY - rect.top) / rect.height - 0.5;
  // Левый декор
  const leftEl = getEl(leftDecor.value)
  if (leftEl) {
    leftEl.style.transform = `rotate(180deg) translate3d(${x * 13.33}px, ${y * 10}px, 0) skew(${x * 2.67}deg, ${y * 2.67}deg)`;
  }
  // Правый декор
  const rightEl = getEl(rightDecor.value)
  if (rightEl) {
    rightEl.style.transform = `translate3d(${-x * 11.67}px, ${-y * 8.33}px, 0) skew(${-x * 2}deg, ${-y * 2}deg)`;
  }
}

onMounted(() => {
  if (sectionRef.value) {
    sectionRef.value.addEventListener('mousemove', handleParallax);
  }
});
onBeforeUnmount(() => {
  if (sectionRef.value) {
    sectionRef.value.removeEventListener('mousemove', handleParallax);
  }
});

const openSampleSetModal = () => {
  modal.open(SampleSetModal, null, null, {
    width: {
      min: 320,
      max: 520
    },
    height: {
      min: 'auto',
      max: '85vh'
    }
  })
}
</script>

<style src="./sample-set.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section class="sample-set" ref="sectionRef">
    <nuxt-img 
        src="/images/landing/leaves-bg-clear.png"
        :alt="t('image_alt')"
        loading="lazy"
        class="bg"
      />
    <nuxt-img 
        src="/images/landing/bg-stars-shine.png"
        :alt="t('image_alt')"
        loading="lazy"
        class="bg-2"
      />
    <!-- Left decorative element placeholder -->
    <div class="sample-set__decor sample-set__decor--left" ref="leftDecor">
      <nuxt-img 
        src="/images/landing/vetka.png"
        :alt="t('image_alt')"
        loading="lazy"
      />
    </div>

    <div class="sample-set__container">
      <div class="sample-set__text">
        <h3 class="sample-set__subtitle">
          {{ t('subtitle') }}
        </h3>
        <h2 class="sample-set__title">
          {{ t('title') }}
        </h2>
      </div>

      <div class="sample-set__circle-container">
        <!-- Полукруг с белой обводкой -->
        <div class="sample-set__semicircle"></div>
        
        <!-- Товары веером -->
        <div class="sample-set__products">
          <div 
            v-for="product in products" 
            :key="product.id"
            class="sample-set__product"
            :style="{ '--angle': `${product.angle}deg`, '--glow-color': product.glowColor }"
          >
            <nuxt-img 
              :src="product.img"
              :alt="t('product_alt', { index: product.id })"
              class="sample-set__product-img"
              loading="lazy"
            />
          </div>
        </div>

        <!-- Кнопка внутри круга -->
        <button class="sample-set__button" type="button" @click="openSampleSetModal">
          {{ t('button') }}
        </button>
      </div>
    </div>

    <!-- Right decorative element placeholder -->
    <div class="sample-set__decor sample-set__decor--right" ref="rightDecor">
        <nuxt-img 
          src="/images/landing/vetka.png"
          :alt="t('image_alt')"
          loading="lazy"
        />
    </div>
  </section>
</template>
