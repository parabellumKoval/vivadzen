<script setup lang="ts">

const { locale } = useI18n()

const props = defineProps({
  value: { type: [Number, String], required: true },
  currency: { type: Boolean, default: true }, // показывать как валюту или как число
  // можно и через проп
  currencyCode: { type: String, default: null },
  currencyLabel: { type: String, default: '' },
})

const {currency: regionCurrency} = useRegion()

const appCurrency = computed(() => {  
  return regionCurrency.value
})

const chosenCode = computed(() => (props.currencyCode || appCurrency?.value || 'UAH').toUpperCase())


const numericValue = computed(() => {
  const n = parseFloat(String(props.value))
  return Number.isFinite(n) ? n : 0
})

/** Проверка, поддерживается ли код валюты движком Intl */
const supportsCurrency = computed<boolean>(() => {
  if (!props.currency) return false
  try {
    // locale.value можно передать, но не обязательно
    new Intl.NumberFormat(locale?.value, { style: 'currency', currency: chosenCode.value }).format(1)
    return true
  } catch {
    return false
  }
})

const fallbackCurrencyLabel = computed(() => {
  if (props.currencyLabel) {
    return props.currencyLabel
  }

  return chosenCode.value
})

// динамически собираем объект формата
const numberFormat = computed(() => {
  if (!props.currency) {
    return { key: 'cur' } // обычное число
  }
  if (supportsCurrency.value) {
    return {
      key: 'currency',
      currency: chosenCode.value,
      currencyDisplay: 'narrowSymbol', // можно 'symbol' | 'code' | 'name'
    }
  }
  // если код невалидный — форматируем как число (без валюты)
  return { key: 'cur' }
})
</script>

<style src="./price.scss" lang="scss" scoped />

<template>
  <div class="price" v-if="numericValue !== null">
    <span class="value">
      <i18n-n :value="numericValue" :format="numberFormat" />
      <template v-if="currency && !supportsCurrency">
        <span class="currency-code">&nbsp;{{ fallbackCurrencyLabel }}</span>
      </template>
    </span>
  </div>
</template>
