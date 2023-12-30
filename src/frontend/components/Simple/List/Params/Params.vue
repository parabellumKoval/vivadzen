<script setup>
const {t} = useI18n()

const props = defineProps({
  items: {
    type: Array
  }
})
</script>

<style src="./params.scss" lang="scss" scoped></style>

<template>
  <div class="params">
    <ul class="list">
      <li v-for="(item, index) in items" :key="index" class="list-item">
        <span class="list-label">{{ item.name }}</span>
        <span class="list-line"></span>
        <span class="list-value">{{ item.value }}</span>
      </li>
    </ul>
  </div>
</template>