<script setup>
const props = defineProps({
  filter: {
    type: Object
  }
})
</script>

<style src="./checkbox2.scss" lang="scss" scoped></style>

<template>
  <div class="wrapper">
    <ul class="checkbox-list">
      <li v-for="(value, index) in filter.values" :key="value.id" class="checkbox-item">
        <button class="checkbox-item-btn">
          <div class="checkbox-input">
            <IconCSS name="iconoir:check" class="checkbox-input-icon"></IconCSS>
          </div>
          <span class="checkbox-content">
            <span class="checkbox-name">{{ value.name }}</span>
            <span class="checkbox-count">({{ value.count }})</span>
          </span>
        </button>
      </li>
    </ul>
  </div>
</template>