<script setup>
const {t} = useI18n()

const items = ref([])
const dragNdropActive = ref(false)
const selectedString = ref(`Выбрано 0 элементов`)


// COMPUTEDS
// const selectedString = computed(() => {
//   console.log('selectedString', useNuxtDataBulks().bulks.value)
//   return `Выбрано ${useNuxtDataBulks().bulks.value} элементов`
// })


// METHODS
const fillItems  = () => {
  // console.log('fillItems', useNuxtDataSetup().settings.value.bulks.enabled)

  if(useNuxtDataSetup().settings.value.view.enabled){
    items.value.push({
      id: 1,
      icon: 'ph:grid-four-light',
      title: t('view'),
      more: true,
      callback: openViewHandler
    })
  }

  if(useNuxtDataSetup().settings.value.bulks.enabled){
    items.value.push({
      id: 2,
      icon: 'ph:pencil-line-light',
      title: t('select'),
      callback: bulksOnHandler
    })
  }

  if(useNuxtDataSetup().settings.value.sorting.enabled){
    items.value.push({
      id: 3,
      icon: 'ph:sort-ascending-light',
      title: t('sort'),
      more: true,
      callback: openSortHandler
    })
  }

  if(useNuxtDataSetup().settings.value.perPage.enabled){
    items.value.push({
      id: 4,
      icon: 'ph:brackets-square-light',
      title: t('per_page'),
      more: true,
      callback: openPerpageHandler
    })
  }

  if(useNuxtDataSetup().settings.value.dragNdrop.enabled){
    items.value.push({
      id: 5,
      icon: 'ph:dots-six-vertical',
      title: t('change_order'),
      callback: openChangeOrderHandler
    })
  }


  if(useNuxtDataSetup().settings.value.pagination.enabled){
    items.value.push({
      id: 6,
      icon: 'ph:repeat-light',
      title: t('pagination'),
      more: true,
      callback: openPaginationHandler
    })
  }
}

const openChangeOrderHandler = (v) => {
  useModal().close()
  useNuxtDataDrag().activate()

  useButton().open([
    {
      text: 'Отменить',
      class: 'button-secondary',
      callback: () => {
        useNuxtDataDrag().deactivate()
        useButton().close()
      }
    }
  ], 'Изменить порядок', 'Перетаскивайте элементы зажав на них пальцем')
}

const openSortHandler = (v) => {
  useModal().open(resolveComponent('ModalMobileTableSort'), null, 'save')
}

const openViewHandler = (v) => {
  useModal().open(resolveComponent('ModalMobileTableView'), null, 'save')
}

const openPerpageHandler = (v) => {
  useModal().open(resolveComponent('ModalMobileTablePerpage'), null,  'save')
}

const openPaginationHandler = (v) => {
  const actions = [
    {
      id: 1,
      icon: 'typcn:sort-numerically',
      title: t('numbers'),
      callback: () => {
        useNuxtDataSetup().setPagination('numbers')
        useModal().close()
      }
    },    {
      id: 2,
      icon: 'ph:repeat-light',
      title: t('loadmore'),
      callback: () => {
        useNuxtDataSetup().setPagination('loadmore')
        useModal().close()
      }
    }
  ]
  useModal().open(resolveComponent('ModalMobileTableActions'), {actions: actions},  'save')
}

const bulksOnHandler = (v) => {
  useModal().close()
  useNuxtDataBulks().on()
  useButton().open([
    {
      id: 1,
      text: 'Отменить',
      class: 'button-secondary',
      callback: () => {
        useNuxtDataBulks().off()
        useButton().close()
      }
    }, {
      id: 2,
      component: resolveComponent('PageButtonBulks'),
    }
  ], 'Массовые действия')
}

fillItems()
</script>

<style src="./settings.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('settings')">
    <simple-list-icon :items="items" class="list"></simple-list-icon>
  </modal-wrapper>
</template>