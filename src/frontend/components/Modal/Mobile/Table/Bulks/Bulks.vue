<script setup>
const {t} = useI18n()

const items = ref([
  {
    id: 1,
    icon: 'ph:trash-simple-light',
    title: t('delete')
  },{
    id: 2,
    icon: 'ph:checks-light',
    title: t('activate')
  },{
    id: 3,
    icon: 'ph:minus-circle-light',
    title: t('deactivate')
  }
])
</script>

<style src="./bulks.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('bulks')">
    <simple-list-icon :items="items" class="list"></simple-list-icon>
  </modal-wrapper>
</template>