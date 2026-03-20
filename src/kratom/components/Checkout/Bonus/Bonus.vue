<script setup>
// import { storeToRefs } from 'pinia'
import {useCartStore} from '~/store/cart'

const {t} = useI18n()
const props = defineProps({})
const {get} = useSettings()
const converter = useConverter()
const {currency} = useRegion()
// const cartStore = useCartStore()
const {user, isAuthenticated} = useAuth()

const emit = defineEmits(['update:promocode'])


// COMPUTEDS
const balance = computed(() => {
  return parseFloat(user.value?.balance) || 0
})

const orderPriceInPoints = computed(() => {
  const v = converter.convertFiatToPointsSync(useCartStore().total, currency.value) ?? 0
  return Math.ceil(v)
})

const maxAvailableToUse = computed(() => {
  return Math.min(balance.value, orderPriceInPoints.value)
})
const {name: balanceCurrency} = usePoints()

const order = computed(() => {
  return useCartStore().order
})
const withdrawalFiatAmount = computed(() => {
  return converter.convertPointsSync(order.value.bonus, currency.value) ?? 0
})

const useBonusesActive = computed({
  get() {
    return order.value.bonus > 0
  },
  set(active) {
    if (!active) {
      // выключаем бонусы
      order.value.bonus = 0
      order.value.bonusInFiat = 0
    } else {
      // включаем — ставим максимально доступную сумму
      order.value.bonus = maxAvailableToUse.value
      order.value.bonusInFiat = withdrawalFiatAmount.value
    }
  }
})

// const useBonusesActive = ref(order.value.bonus > 0)
const useBonusesAmount = ref(balance.value)
// METHODS
const resetBonusAmount = () => {
  order.value.bonus = 0
  order.value.bonusInFiat = 0
}
// HANDLERS
const toggleBonusHandler = () => {
  useBonusesActive.value = !useBonusesActive.value
}

const applyBonusHandler = async () => {
  // await usePromocodeStore().show(order.value.promocode).then((res) => {
  //   if(res) {
  //     useCartStore().setPromocode(res)
  //     useBonusesActive.value = false
  //     emit('update:promocode', order.value.promocode)

  //     useNoty().setNoty({
  //       content: t('noty.promocode.success', {code: order.value.promocode}),
  //       type: 'success'
  //     }, 5000)
  //   }
  // }).catch((e) => {

  //   useNoty().setNoty({
  //     content: e.message,
  //     type: 'error'
  //   }, 7000)
  // })
}
// WATCHERS
// watch(useBonusesActive, (active) => {
//   if(!active) {
//     resetBonusAmount()
//   }else {
//     order.value.bonus = maxAvailableToUse.value
//     order.value.bonusInFiat = withdrawalFiatAmount.value
//   }
// })

watch(() => order.value.bonus, (newVal) => {
  const fiatAmount = converter.convertPointsSync(newVal, currency.value) ?? 0
  order.value.bonusInFiat = fiatAmount
})


// onMounted(() => {
//   converter.ensureRates().catch(() => {
//     // silent: UI can still retry via sync helper when needed
//   })
// })
</script>

<style src='./bonus.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <ClientOnly>
    <div v-if="isAuthenticated && balance > 0" class="bonus-wrapper">
      <transition name="fade-in">
        <div v-if="!useBonusesActive" :key="0" @click="toggleBonusHandler" class="bonus-action">
          <nuxt-img
            src = "/images/viva.png"
            :provider = "useImg().provider"
            width="30"
            height="30"
            sizes = "mobile:70px"
            format = "avif"
            loading = "lazy"
            fit="outside"
            class="bonus-action-image"
          />
          <span class="bonus-action-text">{{ t('use_bonus') }}</span>
        </div>
        <div v-else :key="1" class="bonus-use">
          <div class="bonus-label">
            <span>{{ t('on_your_balance') }}</span>
            <simple-price
              :value="balance"
              :currency-code="balanceCurrency"
              :currency-label="balanceCurrency"
              class="bonus-label-amount"
            ></simple-price>
          </div>
            <div class="bonus-use-label">{{ t('how_much_bonus') }}</div>
            <div class="bonus-form">
              <div>
                <form-amount v-model="order.bonus" :max="maxAvailableToUse" class="bonus-form-amount"></form-amount>
                <div class="bonus-form-hint">{{ t('form.max') }} {{ maxAvailableToUse}}</div>
              </div>
              <button @click="toggleBonusHandler" class="button small secondary full">{{ t('button.cancel') }}</button>
            </div>
        </div>
      </transition>
    </div>
  </ClientOnly>
</template>
