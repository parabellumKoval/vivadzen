<script setup>
const props = defineProps({
  icon: {
    type: String
  },
  isActive: {
    type: Boolean
  }
})
</script>

<style src="./button.scss" lang="scss" scoped />

<template>
  <button :class="{active: isActive}" class="btn btn-primary-2 tiptap-btn">
    <IconCSS :name="icon" size="20" class="icon"></IconCSS>
  </button>
</template>