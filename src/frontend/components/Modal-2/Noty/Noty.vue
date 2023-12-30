<script setup>
const props = defineProps({
  timeout: {
    type: Number,
    default: 3000
  }
})

const messages = computed(() => {
  return useNoty().noties.value
})

const closeHandler = (index) => {
  useNoty().removeNoty(index)
}

const getKey = (message) => {
  return Math.random()
}
</script>

<style src="./noty.scss" lang="scss" scoped />

<template>
  <div class="wrapper">
    <transition-group name="move-x">
      <div
        v-for="(message, index) in messages"
        :key="message.k"
        class="noty"
      >
        <div v-html="message.v" class="message"></div>
        <button @click="closeHandler(index)" class="close-btn" type="button">
          <IconCSS name="fluent:dismiss-20-filled" size="20px" class="icon"></IconCSS>
        </button>
      </div>
    </transition-group>
  </div>
</template>