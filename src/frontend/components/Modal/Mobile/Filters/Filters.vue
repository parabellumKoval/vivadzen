<script setup>
const {t} = useI18n()

const collapseHandler = () => {
  useFilter().showSidebar()
  useModal().close()
}
</script>

<style src="./filters.scss" lang="scss" scoped />

<template>
  <modal-wrapper :title="t('title.search_filters')">
    <div>
      <button @click="collapseHandler" class="collapse-btn">
        <IconCSS name="ph:push-pin-light" size="20" class="icon"></IconCSS>
      </button>
      <filter-box></filter-box>
      <div class="buttons">
        <button class="button">{{ t('label.apply') }}</button>
        <button class="button button-secondary button-red">{{ t('label.reset_all') }}</button>
      </div>
    </div>
  </modal-wrapper>
</template>