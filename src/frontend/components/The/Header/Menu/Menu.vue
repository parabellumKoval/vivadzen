<script setup>
const openHandler = (event) => {
  useModal().open(resolveComponent('ModalMenu'), null, event.target.closest('[modalable]'))
}
</script>

<style src="./menu.scss" lang="scss" scoped ></style>

<template>
  <button @click="openHandler" class="btn-menu btn" modalable>
    <IconCSS name="iconoir:menu" size="24" class="icon"></IconCSS>
  </button>
</template>