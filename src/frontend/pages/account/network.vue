<script setup>
const localePath = useLocalePath()
const {t} = useI18n()

const props = defineProps({})

const tab = ref(0)

const tabs = computed(() => {
  return [
    {
      id: 1,
      name: 'Основное'
    },{
      id: 2,
      name: 'Партнерская сеть <span class="budge green">3</span>'
    },{
      id: 3,
      name: 'Условия программы'
    }
  ]
})
// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
watch(tab, (index) => {
  if(index === 0) {
    navigateTo(localePath('/account/network/common'))
  }else if(index === 1) {
    navigateTo(localePath('/account/network/referrals'))
  }else if(index === 2) {
    navigateTo(localePath('/account/network/conditions'))
  }
})

watch(() => useRoute().meta.tab, (v) => {
  tab.value = v
}, {
  immediate: true
})
</script>

<style src='./network/network.scss' lang='scss' scoped></style>
<style src='./account-page.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div>
    <div class="title-secondary">{{ t('title.network') }}</div>
    <simple-tabs v-model="tab" :values="tabs" value="name" class="tab-wrapper"></simple-tabs>
    <div class="content">
      <NuxtPage />
    </div>
  </div>
</template>