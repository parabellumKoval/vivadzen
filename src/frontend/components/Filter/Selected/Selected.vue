<script setup>
const {t} = useI18n()

const selected = computed(() => {
  return [
    {
      id: 1,
      name: 'Италия'
    },{
      id: 2,
      name: 'Powerful Progress'
    },{
      id: 3,
      name: 'Акции'
    }
  ]
})
</script>

<style src="./selected.scss" lang="scss" scoped></style>

<template>
  <div class="filter-wrapper">
    <div class="filter-label">Выбрано 120 товаров:</div>
    <button class="button small light filter-remove-all-btn">Сбросить все</button>
    <div v-for="filter in selected" :key="filter.id" class="filter-item">
      <span class="filter-name">{{ filter.name }}</span>
      <button class="filter-remove-btn">
        <IconCSS name="iconoir:cancel"></IconCSS>
      </button>
    </div>
  </div>
</template>