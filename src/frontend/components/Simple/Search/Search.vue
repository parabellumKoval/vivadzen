<script setup>
const {t} = useI18n()
const props = defineProps({
  modelValue: {
    type: String
  }
})

const emit = defineEmits([
  'update:modelValue',
  'input:focus',
  'input:blur',
  'close',
  'btn:click'
])

// const model = defineModel()
// function update(v) {
//   console.log('update', v)
//   model.value = v
// }

// COMPUTEDS
// METHODS
// HANDLERS
const updateHandler = ($event) => {
  emit('update:modelValue', $event.target.value)
}
const focusHandler = () => {
  emit('input:focus')
}
const blurHandler = () => {
  emit('input:blur')
}
const closeHandler = () => {
  emit('close')  
}
const goToSearchPage = () => {
  emit('btn:click')
}
// WATCHERS

</script>

<style src='./search.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div class="search">
    <div class="search-inner" type="button" clickable>
      <IconCSS v-if="!$device.isMobile" name="iconoir:search" size="20px" class="search-input-icon"></IconCSS>
      <input
        :value="props.modelValue"
        @input="updateHandler"
        @focus="focusHandler"
        @blur="blurHandler"
        :placeholder="t('search')"
        class="search-input"
      />
      <button @click="closeHandler" class="search-close">
        <IconCSS v-if="$device.isMobile" name="iconoir:cancel"  class="search-close-icon"></IconCSS>
      </button>
      <button @click="goToSearchPage" class="search-action">
        <IconCSS v-if="$device.isMobile" name="iconoir:search" class="search-action-icon"></IconCSS>
        <span v-else-if="!$device.isMobile" class="search-action-text">{{ t('find') }}</span>
      </button>
    </div>
  </div>
</template>