<script setup>
const {t} = useI18n()

const props = defineProps({
  columns: {
    type: [Array, Object],
    default: []
  }
});

const items = computed(() => {
  const columns = useNuxtDataSetup().columns

  let doubleCup = Object.values(columns).filter((item) => {
    return item.sortable
  }).map((item) => {
    return [
      {
        ...item,
        title: item.title + (' (по-возростанию)'),
        dir: true,
        callback: sortHandler
      },{
        ...item,
        title: item.title + (' (по-убыванию)'),
        dir: false,
        callback: sortHandler
      }
    ]
  })

  return doubleCup.flat()
})

// HANDLERS
const sortHandler = (item) => {
  useNuxtDataSetup().sort(item.key, item.dir)
}
</script>

<style src="./sort.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('sort')">
    <simple-list-icon :items="items" class="list"></simple-list-icon>
  </modal-wrapper>
</template>