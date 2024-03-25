<script setup>
const props = defineProps({
  title: {
    type: String,
  },
  description: {
    type: String,
  },
  canClose: {
    type: Boolean,
    default: true
  },
  showContent: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'default' // default and full
  }
})

const margin = 25

// COMPUTEDS
const activeModalOptions = computed(() => {
  return useModal().active?.options
})

const minHeight = computed(() => {
  if(activeModalOptions.value?.height.min !== 'initial')
    return getPxValue(activeModalOptions.value?.height.min - margin)
  else
    return 'initial'
})

const maxHeight = computed(() => {
  console.log('maxHeight', activeModalOptions.value)
  if(activeModalOptions.value?.height.max !== 'initial')
    return getPxValue(activeModalOptions.value?.height.max - margin)
  else
    return 'calc(100vh - 70px)'
})

const minWidth = computed(() => {
  return getPxValue(activeModalOptions.value?.width.min)
})

const maxWidth = computed(() => {
  return getPxValue(activeModalOptions.value?.width.max)
})

// METHODS
const getPxValue = (v) => {
  if(v === undefined)
    return 'initial'
  
  if(typeof v === 'string'){
    return v
  } else {
    return v + 'px'
  }
}

// HANDLERS
const closeHandler = () => {
  if(props.canClose)
    useModal().close()
}
</script>

<style src="./wrapper.scss" lang="sass" scoped />

<style lang="scss" scoped>
@import '/assets/scss/_mixins.scss';
:deep(.modal-inner) {
  @include _desktop {
    max-height: v-bind(maxHeight);
    min-height: v-bind(minHeight);
    min-width: v-bind(minWidth);
    max-width: v-bind(maxWidth);
  }
}
</style> 

<template>
  <div :class="type" clickable class="wrapper">
    <div class="modal-container">
      <button @click="closeHandler" class="close-btn">
        <span class="close-btn-inner">
          <IconCSS name="ph:x" size="20" class="icon"></IconCSS>
        </span>
      </button>
      <div class="modal-inner" scrollable>
        <div v-if="title" class="title">
          {{ title }}
        </div>
        <div v-if="description" class="description">
          {{ description }}
        </div>
        <div class="modal-slot">
          <slot/>
        </div>
      </div>
    </div>
  </div>
</template>