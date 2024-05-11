<script setup>
import {useAuthStore} from '~/store/auth'
import {useFavoritesStore} from '~/store/favorites'

const {t} = useI18n()
const props = defineProps({})

const products = ref([])
const meta = ref(null)
const pending = ref(false)
const isLoading = ref(true)

definePageMeta({
  crumb: {
    name: 'title.account.favorite',
    item: '/'
  },
  tab: 'favorite'
});

// COMPUTEDS
const user = computed(() => {
  return useAuthStore().user
})

const nextPage = computed(() => {
  return meta.value && meta.value.current_page !== meta.value.last_page
})

const query = computed(() => {
  const query = {
    user_id: user.value.id,
    per_page: 12,
    page: 1
  }

  return query
})

// METHODS
const getProducts = async (query, refresh) => {
  pending.value = true

  return useAsyncData('favorites', () => {
    return useFavoritesStore().getAll(query)
  }).then(({data, error}) => {
    if(error){

    }

    if(data.value.data){
      if(refresh)
        products.value = data.value.data
      else
        products.value = products.value.concat(data.value.data)
    }

    if(data.value.meta){
      meta.value = data.value.meta
    }
  }).finally(() => {
    pending.value = false
  })

}

// HANDLERS
const updatePageHandler = (v) => {
  meta.value.current_page = v

  const queryObject = {
    ...query.value,
    page: meta.value.current_page
  }

  getProducts(queryObject, true)
}

const loadmoreHandler = async() => {
  meta.value.current_page++

  const queryObject = {
    ...query.value,
    page: meta.value.current_page
  }

  getProducts(queryObject, false)
}

getProducts(query.value, true).finally(() => {
  isLoading.value = false
})


// WATCHERS

</script>

<style src='./favorite.scss' lang='scss' scoped></style>
<style src='./../account-page.scss' lang='scss' scoped></style>
<!-- <i18n src='./lang.yaml' lang='yaml'></i18n> -->

<template>
  <div>
    <div class="title-secondary">{{ t('title.account.favorite') }}</div>
    
      <div v-if="isLoading" class="products-wrapper">
        <product-card-skeleton
          v-for="item in 3"
          :key="item"
        >
        </product-card-skeleton>
      </div>
      
      <div v-else :class="{pending: pending}" class="products-wrapper">
        <product-card
          v-for="product in products"
          :key="product.id"
          :item="product"
        >
        </product-card>

        <div v-if="!products.length">{{ t('messages.no_results') }}</div>
      </div>

      <div v-if="meta && meta.last_page > 1" class="load-more-wrapper" >
        <button
          v-if="nextPage"
          @click="loadmoreHandler"
          :class="{loading: pending}"
          class="button secondary"
          type="button"
        >
          <span>{{ $t('btn.load_more') }}</span>
        </button>

        <!-- <simple-pagination
          v-if="meta"
          :total="meta.last_page"
          :current="meta.current_page"
          @update:current="updatePageHandler"
          class="pagi"
        >
        </simple-pagination> -->
      </div>

  </div>
</template>