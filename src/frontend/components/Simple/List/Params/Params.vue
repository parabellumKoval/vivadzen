<script setup>
const {t} = useI18n()

const props = defineProps({
  items: {
    type: Array
  }
})

const getValue = (value) => {
  if(Array.isArray(value)) {
    return value.map(item => item.value).join(', ')
  }else {
    return value
  }
}
</script>

<style src="./params.scss" lang="scss" scoped></style>

<template>
  <div class="params">
    <ul class="list">
      <li v-for="(item, index) in items" :key="index" class="list-item">
        <span class="list-label">{{ item.name }}</span>
        <span class="list-line"></span>
        <span class="list-value">{{ getValue(item.value) }}</span>
      </li>
    </ul>
  </div>
</template>