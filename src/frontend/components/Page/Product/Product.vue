<script setup>
import {useAuthStore} from '~/store/auth';
import {useSchema} from '~/composables/product/useSchema.ts'
import {useReviewStore} from '~/store/review'

const props = defineProps({
  product: {
    type: Object,
    requered: true
  }
})

const {t} = useI18n()
const route = useRoute()

const breadcrumbs = ref(null)
const reviews = ref([])
const reviewsMeta = ref({})
const isReviewsLoading = ref(false)
const tab = ref(0)


const {setSchema} = useSchema()
// Content HTML ref
const content = ref(null)

// COMPUTEDS
const category = computed(() => {
  if(props.product?.categories && props.product.categories[0]){
    return props.product.categories[0]
  }else {
    return null
  }
})

const slug = computed(() => {
  return route.params.slug
})

const productMicro = computed(() => {
  return {
    id: props.product.id,
    code: props.product.code,
    name: props.product.name,
    slug: props.product.slug,
    image: props.product.images?.length? props.product.images[0]: null,
    price: props.product.price,
    oldPrice: props.product.oldPrice,
    inStock: props.product.inStock,
  }
})

const ratingCount = computed(() => {
  return props.product.reviews_rating_detailes?.rating_count || 0
})

const reviewsCount = computed(() => {
  return props.product.reviews_rating_detailes?.reviews_count || 0
})


const reviewQuery = computed(() => {
  const type = String.raw`Backpack\Store\app\Models\Product`;

  return {
    per_page: 6,
    reviewable_slug: slug.value,
    reviewable_type: type
  }
})

const tabs = computed(() => {
  const list = [
    {
      id: 1,
      name: t('label.desc')
    },{
      id: 2,
      name: t('label.props')
    },{
      id: 3,
      name: t('title.reviews') + ' ' + (reviewsMeta.value.total? `<span class="budge green">${reviewsMeta.value.total}</span>`: '')
    },{
      id: 4,
      name: t('title.delivery')
    },{
      id: 5,
      name: t('title.payment')
    },{
      id: 6,
      name: t('title.guarantees')
    }
  ]

  if(!props.product.attrs.length) {
    list[1].disabled = true
  }

  if(!reviews.value.length) {
    list[2].disabled = true
  }

  return list
})

// HANDLERS
const paramsHandler = () => {
  tab.value = 1
  scrollToContent()
}

const reviewHandler = () => {
  if(useAuthStore().auth) {
    useModal().open(resolveComponent('ModalReviewCreate'), productMicro.value, null, {width: {min: 420, max: 420}})
  }else {
    useNoty().setNoty({
      content: t('noty.review.need_login'),
      type: 'warning'
    }, 7000)
    
    useModal().open(resolveComponent('ModalAuthSocial'), null, null, {width: {min: 420, max: 420}})
  }
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
    title: props.product.seo.meta_title || t('seo_title_template', {product: props.product.name}),
    meta: [
      {
        name: 'description',
        content: props.product.seo.meta_description || t('seo_desc_template', {product: props.product.name})
      },
    ],
  })
}

const setCrumbs = () => {
  breadcrumbs.value = [
    {
      name: t('title.home'),
      item: '/'
    },{
      name: category?.value?.name,
      item: `/${category?.value?.slug}`
    },{
      name: props.product.name,
      item: props.product.slug
    }
  ]
}

const getReviews = async (query, refresh) => {
  isReviewsLoading.value = true
  await useAsyncData('reviews', () => useReviewStore().getAll(query, refresh)).then(({data, error}) => {
    
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
await useAsyncData('product_reviews', () => useReviewStore().getAll(reviewQuery.value, true)).then(({data, error}) => {
  if(data?.value?.reviews) {
    reviews.value = data.value.reviews
  }

  if(data?.value?.meta) {
    reviewsMeta.value = data.value.meta
  }
})

// WATCHERS
onServerPrefetch(() => {
  setSchema(props.product, reviews.value)
  setSeo()
})

setCrumbs()

watch(() => route.hash, (v) => {
  if(v === '#reviews') {
    if(reviews?.value?.length){
      tab.value = 2
    }else {
      reviewHandler() 
    }   
  }
}, {
  immediate: true
})
</script>

<style src="./product.scss" lang="scss" scoped></style>
<style src="~/assets/scss/_rich-text.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div>
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="header">
        <span class="name title-common">{{ product.name }}</span>
        
        <span v-if="product.code" class="code">
          <span class="label">{{ t('label.product_code') }}:</span>
          <span class="value">{{ product.code }}</span>
        </span>

        <div class="header-reviews">
          <simple-stars :amount="product.rating|| 0" desktop="large" mobile="large"></simple-stars>
          <div v-if="product.rating" class="rating-label">
            {{ t('messages.rates_reviews', {rates: ratingCount, reviews: reviewsCount }) }}
          </div>
          <simple-button-text
            :text="t('button.leave_review')"
            :callback="reviewHandler"
            :class="{disable: product.rating > 0}"
            class="header-reviews-btn"
          ></simple-button-text>
        </div>

        <div class="right">
          <product-comparison :product-id="product.id"></product-comparison>
          <product-favorite :product-id="product.id"></product-favorite>
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
                <div v-if="$device.isDesktop" class="content-html rich-text" v-html="product.content"></div>
              </div>
            </template>

            <!-- Properties -->
            <template v-else-if="tab === 1">
              <div class="params-wrapper">
                <div class="tab-title">{{  t('attrs') }}</div>
                <simple-list-params :items="product.attrs"></simple-list-params>
              </div>
            </template>

            <!-- Reviews -->
            <template v-else-if="tab === 2">
              <product-reviews
                :reviews="reviews"
                :meta="reviewsMeta"
                :product="productMicro"
                @update:current="loadReviewsHandler"
                id="reviews"
              ></product-reviews>
            </template>

            <!-- Delivery -->
            <template v-else-if="tab === 3">
              <div class="">
                <div class="tab-title">{{ t('title.delivery') }}</div>
                <product-delivery-info></product-delivery-info>
              </div>
            </template>

            <!-- Payment -->
            <template v-else-if="tab === 4">
              <div class="">
                <div class="tab-title">{{ t('title.payment') }}</div>
                <product-payment-info></product-payment-info>
              </div>
            </template>

            <!-- Guarantees -->
            <template v-else-if="tab === 5">
              <div class="">
                <div class="tab-title">{{ t('title.guarantees') }}</div>
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

                <div v-if="product.attrs && product.attrs.length" class="params-mini">
                  <simple-list-params :items="product.attrs" class="params-wrapper"></simple-list-params>
                  <button @click="paramsHandler" class="text-link params-mini-btn">
                    <span>{{ t('all_attrs') }}</span>
                    <IconCSS name="iconoir:arrow-right" class="icon"></IconCSS>
                  </button>
                </div>

                <div v-if="$device.isMobile" class="content-html rich-text" v-html="product.content"></div>
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