<script setup>
const props = defineProps({
  modelValue: {
    type: [String,  Number, Boolean]
  },
  items: {
    type: Array
  }
})

const emit = defineEmits([
  'update:modelValue'
])

const selectHandler = (v) => {
  emit('update:modelValue', v.key)
}
</script>

<style src="./tabs.scss" lang="scss" scoped />

<template>
  <div class="tabs-wrapper">
    <button
      v-for="item in items"
      :key="item.key"
      @click="selectHandler(item)"
      :class="{active: item.key === modelValue}"
      class="tab"
    >
      <template v-if="item.image">
        <nuxt-img
          :src = "item.image"
          width="100"
          height="20"
          sizes = "mobile:100px tablet:100px desktop:100px"
          format = "webp"
          quality = "60"
          loading = "lazy"
          fit="outside"
          class="tab-image"
        >
        </nuxt-img> 
      </template>
      <template v-if="item.title">
        {{ item.title }}
      </template>
    </button>
  </div>
</template>