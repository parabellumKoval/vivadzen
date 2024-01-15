<script setup>
import {useProductFaker} from '~/composables/fakers/useProductFaker.ts'

const {t} = useI18n()
const props = defineProps({})

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.comparison'),
    item: '/comparison'
  }
]

// COMPUTEDS
const gridColumns = computed(() => {
  let firstColumn = useDevice().isDesktop? '250px': '190px'
  return Object.keys(products.value).reduce((carry) => {
    return carry + ' 290px'
  }, firstColumn)
})
const products = computed(() => {
  return useProductFaker()(5)
})

const headerRows = computed(() => {
  let list = {
    images: [
      {
        value: 'Товаров в сравнении: 5',
        type: 'text',

      }
    ],
    names: [
      {
        value: 'Название товара',
        type: 'text'
      }
    ],
    prices: [
      {
        value: 'Цена',
        type: 'text'
      }
    ],
    dates: [
      {
        value: 'Дата добавления',
        type: 'text'
      }
    ],
    categories: [
      {
        value: 'Категория',
        type: 'text'
      }
    ]
  }
  
  for(let i = 0; i < products.value.length; i++) {
    const product = products.value[i]

    list.images.push({
      value: product.image.src,
      type: 'image'
    })

    list.names.push({
      value: product.name,
      type: 'text'
    })

    list.prices.push({
      value: product.price,
      type: 'text'
    })

    list.dates.push({
      value: product.code,
      type: 'text'
    })

    list.categories.push({
      value: product.category.name,
      type: 'text'
    })

  }

  return list
})

const attrRows = computed(() => {

})
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./comparison.scss' lang='scss' scoped></style>
<style lang="scss" scoped>
.content-row {
  grid-template-columns: v-bind(gridColumns);
}
</style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title">
        <span class="title-common">{{ t('title.comparison') }}</span>
      </div>
      
    </div>

    <div class="content">
      <div>
        <div v-for="(row, rowKey, rowIndex) in headerRows" :key="rowIndex" class="content-row">
          <div v-for="(cell,cellIndex) in row" :key="cellIndex" :class="{'content-caption': cellIndex === 0}" class="content-cell">
            <button v-if="rowIndex === 0 && cellIndex !== 0" class="delete-btn">
              <IconCSS name="iconoir:trash" class="delete-btn-icon"></IconCSS>
            </button>
            <div :class="{'first-cell': rowIndex === 0 && cellIndex === 0}" class="content-value">
              <template v-if="cell.type === 'image'">
                <nuxt-img
                  v-if='cell.value'
                  :src='cell.value'
                  alt=''
                  title=''
                  width='250'
                  height='150'
                  sizes='mobile:100vw tablet:250px desktop:250px'
                  format='webp'
                  quality='60'
                  loading='lazy'
                  fit='outside'
                  class='product-image'
                />
              </template>
              <template v-else-if="!cell.type || cell.type === 'text'">
                {{ cell.value }}
              </template>
            </div>
          </div>
        </div>
      </div>

      <div class="attr-list">
        <div v-for="(row, rowKey, rowIndex) in headerRows" :key="rowIndex" class="content-row attr-row">
          <div v-for="(cell,cellIndex) in row" :key="cellIndex" :class="{'content-caption': cellIndex === 0}" class="content-cell">
            <div class="content-value">
              {{ cell.value }}
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>