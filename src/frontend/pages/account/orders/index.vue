<script setup>
import {useAuthStore} from '~/store/auth'
import {useCartStore} from '~/store/cart'

const {t} = useI18n()

definePageMeta({
  crumb: {
    name: 'title.account.orders',
    item: '/'
  },
  tab: 'orders'
});

const isLoading = ref(false)
const isInitLoading = ref(true)

const orders = ref([])
const meta = ref(null)


// COMPUTEDS
const nextPage = computed(() => {
  return meta.value && meta.value.current_page !== meta.value.last_page
})

const user = computed(() => {
  return useAuthStore().user
})

const loadmoreHandler = () => {
  isLoading.value = true

  const query = {
    ...useAuthStore().orderable,
    page: ++meta.value.current_page
  }

  getOrders(query)
    .then(({data, meta}) => {
      if(data && meta) {
        orders.value = orders.value.concat(data)
        meta.value = meta
      }
    })
    .finally(() => {
      isLoading.value = false
    })
}

const getOrders = async (data) => {
  return await useCartStore().index(data)
}

useAsyncData('get-orders', () => getOrders({
  ...useAuthStore().orderable,
})).then(({data, error}) => {
  isInitLoading.value = true

  if(data && data.value) {
    orders.value = data.value.data
    meta.value = data.value.meta
  }
}).finally(() => {
  isInitLoading.value = false
})

</script>

<style src="./orders.scss" lang="scss" scoped />
<style src='./../account-page.scss' lang='scss' scoped></style>

<template>
  <div>
    <div class="title-secondary">{{ t('title.account.orders') }}</div>

    <simple-table v-if="isInitLoading || (orders.length && !isInitLoading)">
      <template v-if="!isInitLoading">
        <order-card
          v-for="(order, index) in orders"
          :key="order.id"
          :order="order"
          class="order-card"
        >
        </order-card>
      </template>
      <template v-else>
        <order-card-skeleton
          v-for="(item, index) in 6"
          :key="item"
        >
        </order-card-skeleton>
      </template>
    </simple-table>
    <div v-else >{{ t('messages.no_results') }}</div>

    <div
      v-if="nextPage || isLoading"
      class="load-more-wrapper"
    >
      <button
        @click="loadmoreHandler"
        :class="{loading: isLoading}"
        class="button secondary"
        type="button"
      >
        <span>{{ $t('button.load_more') }}</span>
      </button>
    </div>
  </div>
</template>