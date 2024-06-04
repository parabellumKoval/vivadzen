<script setup>
const {t} = useI18n()

definePageMeta({
  subTab: 2
});

const props = defineProps({})
// COMPUTEDS
const items = computed(() => {
  return [
    {
      id: 1,
      icon: '🔗',
      text: t('item_1')
    },{
      id: 2,
      icon: '💸',
      text: t('item_2')
    },{
      id: 3,
      icon: '🤝',
      text: t('item_3')
    },{
      id: 4,
      icon: '🔔',
      text: t('item_4')
    }
  ]
})
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./conditions.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div class="cond-wrapper">
    <div class="grid-title">{{ t('title') }}</div>
    <div class="grid">
      <div v-for="item in items" :key="item.id" class="grid-item">
        <div class="grid-item-icon">{{ item.icon }}</div>
        <div class="grid-item-text">{{ item.text }}</div>
      </div>
    </div>
  </div>
</template>