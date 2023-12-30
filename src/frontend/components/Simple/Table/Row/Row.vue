<script setup>

const active = ref(false)

const props = defineProps({
  showDetails: {
    type: Boolean,
    default: true
  }
})

const toggleHandler = () => {
  active.value = !active.value
}
</script>
<style src="./row.scss" lang="scss" scoped />

<template>
  <div class="row-wrapper">
    <div class="row">
      <slot name="columns" />
      <button
        v-if="showDetails"
        @click="toggleHandler"
        :class="{active: active}"
        class="button sub clear-padding small show-more-btn"
        type="button"
      >
        <IconCSS name="fluent:chevron-right-48-filled" size="20px" class="icon"></IconCSS>
      </button>
      <div v-else class="button-disabled"></div>
    </div>
    <transition name="fade-in">
      <div v-if="active" class="details">
        <slot name="details" />
      </div>
    </transition>
  </div>
</template>