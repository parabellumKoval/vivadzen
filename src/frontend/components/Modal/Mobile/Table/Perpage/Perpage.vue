<script setup>
const {t} = useI18n()

const items = computed(() => {
  return useNuxtDataSetup().presets.perPages.map((item) => {
    return {
      id: item,
      title: item,
      callback: setPerPageHandler
    }
  })
})

const setPerPageHandler = (v) => {
  useModal().close()
  useNuxtDataSetup().setPerPage(v.id)
}
</script>

<style src="./perpage.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('sort')">
    <simple-list-icon :items="items" class="list"></simple-list-icon>
  </modal-wrapper>
</template>