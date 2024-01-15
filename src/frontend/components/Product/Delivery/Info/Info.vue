<script setup>
const {locale} = useI18n()

const text = await queryContent('delivery').locale(locale.value).findOne()

const deliveries = [
  {
    id: 1,
    name: text.method1,
    desc: text.method1_desc,
    logo: '/images/logo/djini.png',
    icon: 'iconoir:shop-four-tiles'
  },{
    id: 2,
    name: text.method2,
    desc: text.method2_desc,
    logo: '/images/logo/np.png',
    icon: 'iconoir:delivery-truck'
  },{
    id: 3,
    name: text.method3,
    desc: text.method3_desc,
    logo: '/images/logo/np.png',
    icon: 'iconoir:delivery'
  }
]

</script>

<style src="./info.scss" lang="scss" scoped />

<template>
  <div class="delivery">
    <product-methods :items="deliveries" class="deliveries"></product-methods>

    <ContentQuery path="delivery" :locale="locale" find="one">
      <template #default="{ data }">
        <div v-html="data.info"></div>
      </template>
    </ContentQuery>
  </div>
</template>