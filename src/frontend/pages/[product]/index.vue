<script setup>
import {useProductFaker} from '~/composables/fakers/useProductFaker.ts'

const {t} = useI18n()
const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.catalog'),
    item: '/catalog'
  }
]

const tab = ref(0)

// COMPUTEDS
const product = computed(() => {
  return useProductFaker()(1)[0]
})

const tabs = computed(() => {
  return [
    {
      id: 1,
      name: 'Описание'
    },{
      id: 2,
      name: 'Характеристики'
    },{
      id: 2,
      name: 'Отзывы <span class="budge green">3</span>'
    },{
      id: 2,
      name: 'Доставка'
    },{
      id: 2,
      name: 'Оплата'
    },{
      id: 2,
      name: 'Гарантии'
    }
  ]
})

// HANDLERS
const reviewHandler = () => {
  console.log('reviewHandler')
}
</script>

<style src="./product.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div>
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="header">
        <span class="name title-common">{{ product.name }}</span>
        <span class="code">
          <span class="label">{{ t('code') }}:</span>
          <span class="value">{{ product.code }}</span>
        </span>
        <div class="header-reviews">
          <simple-stars :amount="product.rating.rating" desktop="large" mobile="large"></simple-stars>
          <div class="rating-label">
            {{ t('rating', {rating: product.rating.rating_count, reviews: product.rating.reviews_count }) }}
          </div>
          <simple-button-text text="Оставить отзыв" :callback="reviewHandler" class="header-reviews-btn"></simple-button-text>
        </div>
        <div class="right">
          <simple-button-text text="Добавить к сравнению" icon="ph:scales-light" :callback="reviewHandler"></simple-button-text>
          <simple-button-text text="В избранное" icon="iconoir:heart" :callback="reviewHandler"></simple-button-text>
        </div>
      </div>
    </div>

    <simple-tabs v-model="tab" :values="tabs" value="name" class="tab-wrapper"></simple-tabs>

    <div class="container">
      <div class="content">
        
        <div class="content-main">
          <transition name="fade-in">
            <!-- Common -->
            <template v-if="tab === 0">
              <div class="content-common">
                <product-gallery :items="product.images" class="gallery-wrapper"></product-gallery>
                <div v-if="$device.isDesktop" class="content-html" v-html="product.content"></div>
              </div>
            </template>
            <!-- Properties -->
            <template v-else-if="tab === 1">
              <div class="params-wrapper">
                <div class="tab-title">Характеристики товара</div>
                <simple-list-params :items="product.attrs"></simple-list-params>
              </div>
            </template>
            <!-- Reviews -->
            <template v-else-if="tab === 2">
              <product-reviews></product-reviews>
            </template>
            <template v-else-if="tab === 3">
              <div class="">
                <div class="tab-title">Доставка</div>
                <product-delivery-info></product-delivery-info>
              </div>
            </template>
            <template v-else-if="tab === 4">
              <div class="">
                <div class="tab-title">Оплата</div>
                <product-payment-info></product-payment-info>
              </div>
            </template>
            <template v-else-if="tab === 5">
              <div class="">
                <div class="tab-title">Гарантии</div>
                <product-guarantees-info></product-guarantees-info>
              </div>
            </template>
          </transition>
        </div>

        <div class="content-sale">
          <product-sale-block
            v-if="$device.isDesktop || tab === 0"
            :product="product"
            :class="{mini: tab !== 0}"
            class="content-sale-block"
          ></product-sale-block>

          <transition name="fade-in">
            <template v-if="tab === 0">
              <div class="content-grid">
                <product-delivery-box></product-delivery-box>

                <product-payment-box></product-payment-box>

                <div class="params-mini">
                  <simple-list-params :items="product.attrs" class="params-wrapper"></simple-list-params>
                  <button class="text-link params-mini-btn">
                    <span>Все характеристики</span>
                    <IconCSS name="iconoir:arrow-right" class="icon"></IconCSS>
                  </button>
                </div>

                <div v-if="$device.isMobile" class="content-html" v-html="product.content"></div>
              </div>
            </template>
          </transition>
        </div>

      </div>
    </div>
  
    <!-- Sale block mobile   -->
    <product-sale-fixed v-if="$device.isMobile" :product="product"></product-sale-fixed>
  </div>
</template>