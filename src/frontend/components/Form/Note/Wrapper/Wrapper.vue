<script setup>
const props = defineProps({
  modelValue: {
    type: [Array, Object]
  }
})

const items = ref([])

const addHandler = () => {
  items.value.push({
    color: 'default',
    content: 'Текст заметки'
  })
}

items.value = props.modelValue
</script>

<style src="./wrapper.scss" lang="scss" scoped />

<template>
  <div>
    <form-note-box :items="modelValue"></form-note-box>
    <button @click="addHandler" class="button-text add-btn">
      <IconCSS name="ph:plus-circle" size="20"></IconCSS>
      <span class="text">Добавить заметку</span>
    </button>
  </div>
</template>