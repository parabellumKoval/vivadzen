<script setup>
const {locale} = useI18n()

const text = await queryContent('payment').locale(locale.value).findOne()

const payments = [
  {
    id: 1,
    name: text.cash,
    desc: text.cash_desc,
    logo: '/images/logo/djini.png',
    icon: 'iconoir:wallet'
  },{
    id: 2,
    name: text.deliv,
    desc: text.deliv_desc,
    logo: '/images/logo/np.png',
    icon: 'iconoir:hand-cash'
  },{
    id: 3,
    name: text.liq,
    desc: text.liq_desc,
    logo: '/images/logo/liqpay.png',
    icon: 'iconoir:laptop'
  },{
    id: 4,
    name: text.card,
    desc: text.card_desc,
    logo: '/images/logo/visa-mastercard.png',
    icon: 'iconoir:credit-cards'
  },{
    id: 5,
    name: text.box,
    desc: text.box_desc,
    logo: '/images/logo/ibox-city24.png',
    icon: 'mdi:box'
  }
]
</script>

<style src="./info.scss" lang="scss" scoped />

<template>
  <div class="payment">
    <product-methods :items="payments" class="payments"></product-methods>

    <ContentQuery path="delivery" :locale="locale" find="one">
      <template #default="{ data }">
        <div v-html="data.info"></div>
      </template>
    </ContentQuery>
  </div>
</template>