<script setup>
const props = defineProps({
  modelValue: {
    type: String,
    default: 'default'
  }
})

const emit = defineEmits([
  'update:modelValue'
])

const colors = ref([
  {
    id: 'default',
    value: '#EEEEEE'
  },{
    id: 'red',
    value: '#FFADAD'
  },{
    id: 'orange',
    value: '#FFD6A5'
  },{
    id: 'yellow',
    value: '#FDFFB6'
  },{
    id: 'green',
    value: '#CAFFBF'
  },{
    id: 'cyan',
    value: '#9BF6FF'
  },{
    id: 'blue',
    value: '#A0C4FF'
  },{
    id: 'purple',
    value: '#BDB2FF'
  },{
    id: 'pink',
    value: '#FFC6FF'
  }
])

const selectHandler = (v) => {
  emit('update:modelValue', v)
}
</script>

<style src="./color.scss" lang="scss" scoped />

<template>
  <div class="colors-wrapper">
    <button
      v-for="item in colors"
      :key="item.id"
      @click="selectHandler(item.id)"
      :class="[{active: item.id === modelValue}, item.id]"
      class="colors-item"
    >
      <!-- <IconCSS v-if="item.id === modelValue" name="ph:check-bold" size="20" class="icon"></IconCSS> -->
    </button>
  </div>
</template>