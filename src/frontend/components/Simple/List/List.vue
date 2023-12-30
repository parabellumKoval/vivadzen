<script setup>
const props = defineProps({
  list: {
    type: [Array, Object],
    required: true
  }
})

const emit = defineEmits([
  'clicked'
])

const clickedHandler = (event) => {
  emit('clicked', {event: event})
}
</script>
<style src="./list.scss" lang="scss" scoped />

<template>
  <ul class="list">
    <li v-for="(item, index) in list" :key="index">
      <NuxtLink v-if="item.link" :to="localePath(item.link)" class="link href">
        <IconCSS v-if="item.icon" :name="item.icon" size="20px"/>
        <span class="text">{{ item.text }}</span>
      </NuxtLink>
      <button v-else @click="clickedHandler(item.event)" class="link href" type="button">
        <IconCSS v-if="item.icon" :name="item.icon" size="20px"/>
        <span class="text">{{ item.text }}</span>
      </button>
    </li>
  </ul>
</template>