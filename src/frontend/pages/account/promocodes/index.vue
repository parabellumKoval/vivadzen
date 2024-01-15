<script setup>
import {usePromocodeFaker} from '~/composables/fakers/usePromocodeFaker.ts'
// import {useAuthStore} from '~/store/auth'
// import {useCartStore} from '~/store/cart'

const {t} = useI18n()
const isLoading = ref(false)
const isInitLoading = ref(false)

// const orders = ref([])
const meta = ref(null)

const nextPage = computed(() => {
  return meta.value && meta.value.current_page !== meta.value.last_page
})

const promocodes = computed(() => {
  return usePromocodeFaker()(4)
})

const loadmoreHandler = () => {}
// const loadmoreHandler = () => {
//   isLoading.value = true

//   const query = {
//     ...useAuthStore().orderable,
//     per_page: ++meta.value.current_page
//   }

//   getOrders(query)
//     .then(({data, meta}) => {
//       if(data && meta) {
//         orders.value = orders.value.concat(data)
//         meta.value = meta
//       }
//     })
//     .finally(() => {
//       isLoading.value = false
//     })
// }

// const getOrders = async (data) => {
//   return await useCartStore().index(data)
// }

// useAsyncData('get-orders', () => getOrders({
//   ...useAuthStore().orderable,
// })).then(({data, error}) => {
//   if(data && data.value) {
//     orders.value = data.value.data
//     meta.value = data.value.meta
//   }
// }).finally(() => {
//   isInitLoading.value = false
// })

</script>

<style src="./promocodes.scss" lang="scss" scoped />
<style src='./../account-page.scss' lang='scss' scoped></style>

<template>
  <div>
    <div class="title-secondary">Мои промокоды</div>

    <simple-table v-if="isInitLoading || (promocodes.length && !isInitLoading)">
      <template v-if="!isInitLoading">
        <promocode-card
          v-for="(promocode, index) in promocodes"
          :key="promocode.id"
          :promocode="promocode"
          class="order-card"
        >
        </promocode-card>
      </template>
      <template v-else>
        <promocode-card-skeleton
          v-for="(item, index) in 6"
          :key="item"
        >
        </promocode-card-skeleton>
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
        <span>{{ $t('btn.load_more') }}</span>
      </button>
    </div>
  </div>
</template>