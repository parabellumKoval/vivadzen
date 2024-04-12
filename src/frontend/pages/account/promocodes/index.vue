<script setup>
import {usePromocodeFaker} from '~/composables/fakers/usePromocodeFaker.ts'
// import {useAuthStore} from '~/store/auth'
import {usePromocodeStore} from '~/store/promocode'

const {t} = useI18n()

definePageMeta({
  crumb: {
    name: 'title.account.promocodes',
    item: '/'
  },
  tab: 'promocodes'
});

const isLoading = ref(false)
const isInitLoading = ref(true)

const promocodes = ref([])
const meta = ref(null)

const nextPage = computed(() => {
  return meta.value && meta.value.current_page !== meta.value.last_page
})


// const loadmoreHandler = () => {}
const loadmoreHandler = () => {
  isLoading.value = true

  const query = {
    page: ++meta.value.current_page
  }

  getPromocodes(query)
    .then(({data, meta}) => {
      if(data && meta) {
        promocodes.value = promocodes.value.concat(data)
        meta.value = meta
      }
    })
    .finally(() => {
      isLoading.value = false
    })
}

const getPromocodes = async (query = null) => {
  return await usePromocodeStore().index(query)
}

useAsyncData('get-promocodes', () => getPromocodes()).then(({data, error}) => {
  isInitLoading.value = true
  if(data && data.value) {
    promocodes.value = data.value.data
    meta.value = data.value.meta
  }
}).finally(() => {
  isInitLoading.value = false
})


</script>

<style src="./promocodes.scss" lang="scss" scoped />
<style src='./../account-page.scss' lang='scss' scoped></style>

<template>
  <div>
    <div class="title-secondary">{{ t('title.account.promocodes') }}</div>
    
    <simple-table v-if="isInitLoading || (promocodes.length && !isInitLoading)">
      <template v-if="!isInitLoading">
        <promocode-card
          v-for="(promocode, index) in promocodes"
          :key="promocode.id"
          :promocode="promocode"
          class="promocode-card"
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
      v-if="nextPage"
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