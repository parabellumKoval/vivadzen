<script setup>
const {t} = useI18n()
const active = ref(false)

const props = defineProps({
  showDetails: {
    type: Boolean,
    default: true
  },
  grid: {
    type: String,
    default: 'initial'
  }
})

const toggleHandler = () => {
  active.value = !active.value
}
</script>

<style src="./row.scss" lang="scss" scoped />
<style lang="scss" scoped>
@import './assets/scss/mixins';

.columns-area {
  @include _desktop {
    grid-template-columns: v-bind(grid);
  }
}
</style>

<template>
  <div class="row-wrapper">
    <div class="row">
      <div class="columns-area">
        <slot name="columns" />
      </div>
      <div class="action-column" :class="{empty: !showDetails}">
        <button
          v-if="showDetails"
          @click="toggleHandler"
          :class="{active: active}"
          class="show-more-btn"
          type="button"
        >
          <IconCSS name="fluent:chevron-right-48-filled" class="icon"></IconCSS>
          <span class="text">{{ t('label.thorough') }}</span>
        </button>
        <div v-else class="button-disabled"></div>
      </div>
    </div>
    <transition name="fade-in">
      <div v-if="active" class="details">
        <slot name="details" />
      </div>
    </transition>
  </div>
</template>