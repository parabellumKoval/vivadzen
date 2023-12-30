<script setup>
const {t} = useI18n()

// const baseItems = computed(() => {
//   return [
//     {
//       id: 1,
//       title: t('edit'),
//       icon: 'ph:pencil-line-light',
//       callback: useModal().active?.data?.handlers?.edit
//     },{
//       id: 2,
//       title: t('remove'),
//       icon: 'ph:trash-simple-light',
//       callback: useModal().active?.data?.handlers?.remove
//     }
//   ]
// })

// const list = computed(() => {
//   return items.value

//   // return [
//   //   ...items.value,
//   //   ...baseItems.value
//   // ]
// })

const items = computed(() => {
  return useModal().active?.data?.actions || []
})

const description = computed(() => {
  return useModal().active?.data?.description || null
})

// HANDLERS
</script>

<!-- 
<style src="./actions.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n> -->

<template>
  <modal-wrapper :title="t('actions')" :description="description">
    <!-- <simple-list-icon :items="items" class="list"></simple-list-icon> -->
    <list :items="items" type="nav"></list>
  </modal-wrapper>
</template>