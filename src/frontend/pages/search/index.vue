<script setup>
import {useProductFaker} from '~/composables/fakers/useProductFaker.ts'

const {t} = useI18n()

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.search_results'),
    item: '/search'
  }
]

const products = computed(() => {
  return useProductFaker()(8)
})

</script>

<style src="./search.scss" lang="scss" scoped></style>

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">
        Результаты поиска «<span class="title-search">Биологические добавки</span>»
      </div>
    </div>

    <div class="selected">
      <div class="container">
        <filter-selected></filter-selected>
      </div>
    </div>

    <div class="container">
      <div class="header">
        <div class="header-title">
          Фильтры
        </div>
        <div class="header-desc">
          Найдено 120 товаров
        </div>
        <div class="header-actions">
          <button class="button mini light sorting-btn">
            <IconCSS name="iconoir:sort-down" class="inline-icon"></IconCSS>
            <span>От дешевых к дорогим</span>
          </button>
        </div>
      </div>
    </div>

    <div class="content">
      <filter-list class="filters"></filter-list>
      <div class="content-grid">
        <product-card v-for="product in products" :key="product.id" :item="product" class="content-grid-item"></product-card>
      </div>
    </div>

    <filter-mobile-buttons v-if="$device.isMobile"></filter-mobile-buttons>

  </div>
</template>