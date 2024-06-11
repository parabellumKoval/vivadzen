<script setup>
import {useFetchReview} from '~/composables/review/useFetchReview.ts'
import {useFilter} from '~/composables/product/useFilter.ts'
import {useFilterItem} from '~/composables/product/useFilterItem.ts'

const props = defineProps({
  category: {
    type: Object,
    required: true
  }
})

const {t} = useI18n()
const route = useRoute()

const {getFilters} = useFilter()
const {setFilters} = useFilterItem()

// Attributes
const attributes = ref([])

// Reviews
const reviews = ref([])
const reviewsMeta = ref({})

const breadcrumbs = ref([])

// COMPUTED
const slug = computed(() => {
  return route.path.substring(route.path.lastIndexOf('/') + 1) || null 
})

const setCrumbs = () => {
  breadcrumbs.value = [
    {
      name: t('title.home'),
      item: '/'
    },{
      name: props.category?.name,
      item: `/${props.category?.slug}`
    }
  ]
}

const categories = computed(() => {
  return props.category.children
})

// METHODS
const getQuery = () => {
  let query = {
    per_page: 20,
    page: route.query.page || 1
  }

  if(slug.value)
    query.category_slug = slug.value

  // Set filters from URL
  if(route.query) {
    const selections = setFilters(route.query)
    
    if(selections?.length) {
      query.selections = selections
    }
  }

  return query
}

const setSeo = () => {
  useHead({
    title: props.category.seo.meta_title,
    meta: [
      {
        name: 'description',
        content: props.category.seo.meta_description
      },
    ],
  })
}

// HOOKS
onServerPrefetch(() => {
  // setSchema(props.product, reviews.value)
  setSeo()
})

setCrumbs()

attributes.value = await getFilters(getQuery()).then(({data}) => {
  return data.value || []
});

await useFetchReview().getReviews({
  per_page: 6,
  category_slug: slug.value,
  resource: 'large'
}, true).then(({reviews: r, meta: m}) => {
  reviews.value = r
  reviewsMeta.value = m
})
</script>

<style src="./category.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <NuxtLayout
    name="category"
    :slug="slug"
    :breadcrumbs="breadcrumbs"
    :attributes="attributes"
    :initQuery="getQuery()"
  >
    <template #title>
      {{ category.name }}
    </template>

    <template #header>
      <catalog-categories v-if="categories?.length" :categories="categories"></catalog-categories>
    </template>
    
    <template #footer> 
      <div v-if="reviewsMeta?.total" class="review">
        <div class="title-secondary review-title">{{ t('review') }} {{ category.name }}</div>
        <div class="review-header">
          <simple-stars :amount="reviewsMeta?.rating_avg || 0"></simple-stars>
          <div class="review-count">
            {{ t('messages.rates_reviews', {rates: (reviewsMeta?.rating_count || 0),reviews: (reviewsMeta?.total || 0)}) }}
          </div>
        </div>
        <review-product v-for="review in reviews" :key="review.id" :item="review" type="mini" class="review-item"></review-product>
      </div>

      <div v-if="category.content" class="seo-text rich-text" v-html="category.content"></div>

      <section-faq-catalog :category="category" class="faq-section"></section-faq-catalog>
    </template>
  </NuxtLayout>
</template>