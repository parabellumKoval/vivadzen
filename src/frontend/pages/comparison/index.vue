<script setup>
import {useComparisonStore} from '~/store/comparison.ts'
import {useProductStore} from '~/store/product.ts'

const {t} = useI18n()
const products = ref([])

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
  if(!products.value){
    return []
  }

  let firstColumn = useDevice().isDesktop? '250px': '190px'

  return Object.keys(products.value).reduce((carry) => {
    return carry + ' 290px'
  }, firstColumn)
})

const headerRows = computed(() => {
  if(!products.value){
    return []
  }

  let list = {
    images: [
      {
        value: t('count') + ': ' + (products.value?.length || 0),
        type: 'text',
      }
    ],
    names: [
      {
        value: t('product_name'),
        type: 'text'
      }
    ],
    prices: [
      {
        value: t('label.price'),
        type: 'text'
      }
    ],
    sales: [
      {
        value: t('label.sale'),
        type: 'text'
      }
    ],
    dates: [
      {
        value: t('label.product_code'),
        type: 'text'
      }
    ],
    categories: [
      {
        value: t('category'),
        type: 'text'
      }
    ]
  }
  
  for(let i = 0; i < products.value.length; i++) {
    const product = products.value[i]

    const sale = product.oldPrice? product.price - product.oldPrice: 0

    list.images.push({
      value: product.images[0]?.src || null,
      type: 'image'
    })

    list.names.push({
      value: product.name,
      src: product.slug,
      type: 'link'
    })

    list.prices.push({
      value: product.price,
      type: 'price'
    })

    list.sales.push({
      value: sale,
      type: 'price'
    })

    list.dates.push({
      value: product.code || '-',
      type: 'text'
    })

    list.categories.push({
      value: product.categories[0]?.name || null,
      type: 'text'
    })

  }

  return list
})

const attrRows = computed(() => {
  if(!products.value){
    return []
  }

  let list = allAttributes.value

  for(let i = 0; i < products.value.length; i++) {
    const product = products.value[i]
    const filledAttrs = []

    if(product.attrs) {
      for(let a = 0; a < product.attrs.length; a++){
        const attr = product.attrs[a]

        list[attr.id].push({
          value: getAttributeValue(attr.value),
          type: 'text'
        })

        filledAttrs.push(attr.id)
      }
    }

    // fill empty
    if(list) {
      for (const [key, value] of Object.entries(list)) {
        const attributeId = parseInt(key)
        if(!filledAttrs.includes(attributeId)) {
          list[attributeId].push({value: '-', type: 'text'})
        }
      }
    }

  }

  return list

})


const allAttributes = computed(() => {
  let attributes = {}

  for(let i = 0; i < products.value.length; i++) {
    const product = products.value[i]

    if(!product.attrs) {
      continue
    }

    for(let a = 0; a < product.attrs.length; a++) {
      const attribute = product.attrs[a]
      
      if(!attributes[attribute.id]) {
        attributes[attribute.id] = []
        attributes[attribute.id].push({
          value: attribute.name,
          type: 'text'
        })
      }
    }
  }

  return attributes
})

// METHODS
const getAttributeValue = (attr) => {
  if(Array.isArray(attr)) {
    return attr.reduce((carry, item) => {
      if(!carry) {
        return item.value
      }else {
        return carry + ', ' + item.value
      }
    }, null)
  }else {
    return attr.value
  }
}

const getImageSrc = (src) => {
  if(src) {
    return '/server/images/products/' + src
  } else {
    return './images/noimage.png'
  }
}

const getProducts = (ids) => {
  useProductStore().getMultiple(ids).then((v) => {
    if(v) {
      products.value = v
    }
  })
}

const deleteItemHandler = (i) => {
  const index = i - 1
  const product = products.value[index]
  
  if(product) {
    products.value.splice(index, 1)
    useComparisonStore().toggle(product.id)
  }
  
}

// HANDLERS
// WATCHERS
watch(() => useComparisonStore().ids, (v) => {
  getProducts(v)
}, {
  immediate: true
})
</script>

<style src='./comparison.scss' lang='scss' scoped></style>
<style lang="scss" scoped>
.content-row {
  grid-template-columns: v-bind(gridColumns);
}
</style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title">
        <span class="title-common">{{ t('title.comparison') }}</span>
      </div>
      
    </div>

    <div v-if="products" class="content">
      <div>
        <div v-for="(row, rowKey, rowIndex) in headerRows" :key="rowIndex" class="content-row">
          <div v-for="(cell,cellIndex) in row" :key="cellIndex" :class="{'content-caption': cellIndex === 0}" class="content-cell">
            
            <button
              v-if="rowIndex === 0 && cellIndex !== 0"
              @click="deleteItemHandler(cellIndex)"
              class="delete-btn"
            >
              <IconCSS name="iconoir:trash" class="delete-btn-icon"></IconCSS>
            </button>

            <div :class="{'first-cell': rowIndex === 0 && cellIndex === 0}" class="content-value">
              <template v-if="cell.type === 'image'">
                <nuxt-img
                  v-if='cell.value'
                  :src='getImageSrc(cell.value)'
                  alt=''
                  title=''
                  width='250'
                  height='150'
                  sizes='mobile:100vw tablet:250px desktop:250px'
                  format='webp'
                  quality='60'
                  loading='lazy'
                  fit='outside'
                  placeholder="./images/noimage.png"
                  class='product-image' 
                />
              </template>
              <template v-else-if="!cell.type || cell.type === 'text'">
                {{ cell.value }}
              </template>
              <template v-else-if="!cell.type || cell.type === 'link'">
                <NuxtLink :to="localePath('/' + cell.src)" class="field-link">{{ cell.value }}</NuxtLink>
              </template>
              <template v-else-if="!cell.type || cell.type === 'price'">
                <span v-if="cell.value === 0">–</span>
                <simple-price v-else :value="cell.value"></simple-price>
              </template>
            </div>
          </div>
        </div>
      </div>

      <div class="attr-list">
        <div v-for="(row, rowKey, rowIndex) in attrRows" :key="rowIndex" class="content-row attr-row">
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