<script setup>
// import {useProductFaker} from '~/composables/fakers/useProductFaker.ts'
import {useSchema} from '~/composables/product/useSchema.ts'
import {useProductStore} from '~/store/product'
import {useReviewStore} from '~/store/review'
// import {useAuthStore} from '~/store/auth'
import {useFavoritesStore} from '~/store/favorites';

const {t} = useI18n()
const route = useRoute()

const breadcrumbs = ref(null)
const product = ref(null)
const reviews = ref([])
const reviewsMeta = ref({})
const isReviewsLoading = ref(false)
const tab = ref(0)

// Content HTML ref
const content = ref(null)
const isLoading = ref(false)

// COMPUTEDS
const category = computed(() => {
  if(product.value?.categories && product.value.categories[0]){
    return product.value.categories[0]
  }else {
    return null
  }
})

const images = computed(() => {
  return product.value?.images
})

const slug = computed(() => {
  return route.params.product
})

const productMicro = computed(() => {
  return {
    id: product.value.id,
    code: product.value.code,
    name: product.value.name,
    slug: product.value.slug,
    image: product.value.images[0],
    price: product.value.price,
    oldPrice: product.value.oldPrice,
    inStock: product.value.inStock,
  }
})

// const getReviewQuery = (productId) => {
//   const type = String.raw`Backpack\Store\app\Models\Product`;

//   return {
//     per_page: 6,
//     reviewable_id: productId,
//     reviewable_type: type
//   }
// }
const reviewQuery = computed(() => {
  const type = String.raw`Backpack\Store\app\Models\Product`;

  return {
    per_page: 6,
    reviewable_slug: slug.value,
    reviewable_type: type
  }
})

// const reviewsSlice = computed(() => {
//   return currentTab.value === 0? reviews.value.slice(0, 3): reviews.value
// })

const isFavorite = computed(() => {
  const fi = useFavoritesStore().ids
  
  if(fi && fi.length)
    return fi.includes(product.value.id)
  
  return false
})

const tabs = computed(() => {
  const list = [
    {
      id: 1,
      name: 'Описание'
    },{
      id: 2,
      name: 'Характеристики'
    },{
      id: 2,
      name: 'Отзывы ' + (reviewsMeta.value.total? `<span class="budge green">${reviewsMeta.value.total}</span>`: '')
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

  if(!product.value.attrs.length) {
    list[1].disabled = true
  }

  if(!reviews.value.length) {
    list[2].disabled = true
  }

  return list
})

// HANDLERS
const reviewHandler = () => {
  useModal().open(resolveComponent('ModalReviewCreate'), productMicro.value, null, {width: {min: 420, max: 420}})
}

const loadReviewsHandler = async (page) => {
  const query = {
    ...reviewQuery.value,
    // for loadmore button
    // page: reviewsMeta.value.current_page + 1
    page: page
  }
  await getReviews(query, false).then(() => {
    scrollToContent()
  })
}

const toFavoriteHandler = () => {
  if(!auth.value || !user.value) {
    console.log('NEED TO OPEN SING IN MODAL')
    // useModalStore().open('signInSocial')
  }else {
    useFavoritesStore().sync({
      user_id: user.value.id,
      product_id: product.value.id
    }).then((r) => {
      if(r) {
        // useNoty().setNoty(t('noty.favorites_success'))
      }
    }).catch((e) => {
      // useNoty().setNoty(t('noty.favorites_error'))
    })
  }
}

// METHODS
const scrollToContent = () => {
  var headerOffset = 180;
  var elementPosition = content.value.getBoundingClientRect().top;
  var offsetPosition = elementPosition + window.pageYOffset - headerOffset;

  window.scrollTo({
    top: offsetPosition,
    behavior: "smooth"
  })
}

const setSeo = () => {
  useHead({
    title: product.value.seo.meta_title || t('seo_title_template', {product: product.value.name}),
    meta: [
      {
        name: 'description',
        content: product.value.seo.meta_description || t('seo_desc_template', {product: product.value.name})
      },
    ],
  })
}

const setCrumbs = () => {
  breadcrumbs.value = [
    {
      name: t('crumbs.home'),
      item: '/'
    },{
      name: category?.value?.name,
      item: `/${category?.value?.slug}`
    },{
      name: product.value.name,
      item: product.value.slug
    }
  ]
}



const getReviews = async (query, refresh) => {
  isReviewsLoading.value = true
  await useAsyncData('reviews', () => useReviewStore().getAll(query, refresh)).then(({data, error}) => {
    // console.log('reviews reviews', data.value.reviews, data.value.meta)
    
    if(data?.value?.reviews) {
      reviews.value = data.value.reviews
    }

    if(data?.value?.meta) {
      reviewsMeta.value = data.value.meta
    }
  }).finally(() => {
    isReviewsLoading.value = false
  })
}

// FETCH
await Promise.all([
  await useAsyncData(`product-${slug}`, () => useProductStore().show(slug.value)).then(({data, error}) => {
    if(data && data.value) {
      product.value = data.value
      setCrumbs()
      // console.log('product', product.value)
      return product
    }

    if(error.value){
      throw createError({ statusCode: 404, message: 'Page Not Found' })
    }
  }), 
  await useAsyncData('product_reviews', () => useReviewStore().getAll(reviewQuery.value, true)).then(({data, error}) => {
    // console.log('data reviews', data.value)
    if(data?.value?.reviews) {
      reviews.value = data.value.reviews
    }

    if(data?.value?.meta) {
      reviewsMeta.value = data.value.meta
    }
  })
]).then(([ product, reviews]) => {
  useSchema(product.value, reviews?.value?.reviews)
  setSeo()
})
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
          <simple-stars :amount="product.rating|| 0" desktop="large" mobile="large"></simple-stars>
          <div class="rating-label">
            {{ t('rating', {rating: product.reviews_rating_detailes?.rating_count || 0, reviews: product.reviews_rating_detailes?.reviews_count || 0 }) }}
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
        
        <div class="content-main" ref="content">
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
              <product-reviews
                :reviews="reviews"
                :meta="reviewsMeta"
                @update:current="loadReviewsHandler"
              ></product-reviews>
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
            :product="productMicro"
            :class="{mini: tab !== 0}"
            class="content-sale-block"
          ></product-sale-block>

          <transition name="fade-in">
            <template v-if="tab === 0">
              <div class="content-grid">
                <product-delivery-box></product-delivery-box>

                <product-payment-box></product-payment-box>

                <div v-if="product.attrs && product.attrs.length" class="params-mini">
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