<script setup>
  import { useModalStore } from '~/store/modal';

  const props = defineProps({
    canClose: {
      type: Boolean,
      default: true
    }
  })

  const closeHandler = () => {
    if(props.canClose)
      useModalStore().closeAll()
  }
</script>

<style src="./wrapper.scss" lang="sass" scoped />

<template>
  <div @click.self="closeHandler" clickable class="popup-wrapper effect-1">
    <div class="effect-2">
      <slot/>
    </div>
  </div>
</template>