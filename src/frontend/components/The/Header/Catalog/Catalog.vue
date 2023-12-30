<script setup>

const { t } = useI18n()

const openMenuHandler = () => {
  useModal().open(resolveComponent('ModalCatalog'), null, null, {width: {
    min: 'calc(100vw - 90px)',
    max: 'calc(100vw - 90px)'
  }})
}
</script>

<style src="./catalog.scss" lang="scss" scoped />

<template>
  <button @click="openMenuHandler" class="catalog-btn btn" type="button" clickable>
    <IconCSS name="iconoir:view-grid" size="24px" class="icon"></IconCSS>
    <span class="text">{{ t('catalog') }}</span>
  </button>
</template>

<i18n lang="yaml">
uk:
  catalog: "Каталог товарів"
ru:
  catalog: "Каталог товаров"
</i18n>