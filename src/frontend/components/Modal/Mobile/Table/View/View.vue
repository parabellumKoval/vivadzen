<script setup>
const {t} = useI18n()

const items = ref([])

// const items = computed(() => {
//   return [
//     {
//       id: 1,
//       icon: 'ph:list-light',
//       title: t('list'),
//       callback: setListView
//     },{
//       id: 2,
//       icon: 'ph:image-square-light',
//       title: t('card'),
//       callback: setCardView
//     },{
//       id: 3,
//       icon: 'ph:table-light',
//       title: t('table'),
//       callback: setTableView
//     }
//   ]
// })


const fillItems  = () => {
  if(useNuxtDataSetup().settings.value.view.values.includes('table')){
    items.value.push({
      id: 1,
      icon: 'ph:table-light',
      title: t('table'),
      callback: setTableView
    })
  }

  if(useNuxtDataSetup().settings.value.view.values.includes('card')){
    items.value.push({
      id: 2,
      icon: 'ph:image-square-light',
      title: t('card'),
      callback: setCardView
    })
  }

  if(useNuxtDataSetup().settings.value.view.values.includes('list')){
    items.value.push({
      id: 3,
      icon: 'ph:list-light',
      title: t('list'),
      callback: setListView
    })
  }

  if(useNuxtDataSetup().settings.value.view.values.includes('sortable')){
    items.value.push({
      id: 4,
      icon: 'ph:list-light',
      title: t('sortable'),
      callback: setListView
    })
  }

  if(useNuxtDataSetup().settings.value.view.values.includes('images')){
    items.value.push({
      id: 5,
      icon: 'ph:list-light',
      title: t('images'),
      callback: setListView
    })
  }
}

const setListView = () => {
  useModal().close()
  useNuxtDataSetup().setView('list')
}

const setCardView = () => {
  useModal().close()
  useNuxtDataSetup().setView('card')
}

const setTableView = () => {
  useModal().close()
  useNuxtDataSetup().setView('table')
}


fillItems()
</script>

<style src="./view.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('view')">
    <simple-list-icon :items="items" class="list"></simple-list-icon>
  </modal-wrapper>
</template>