<script setup>
const props = defineProps({
  isShow: {
    type: Boolean,
    default: false
  }
})

const activeModalOptions = computed(() => {
  return useModal().active?.options
})

const topCss = computed(() => {
  return getPxValue(activeModalOptions.value?.y?.top)
})

const bottomCss = computed(() => {
  return getPxValue(activeModalOptions.value?.y?.bottom)
})

const rightCss = computed(() => {
  return getPxValue(activeModalOptions.value?.x?.right)
})

const leftCss = computed(() => {
  return getPxValue(activeModalOptions.value?.x?.left)
})

const transformCss = computed(() => {
  return activeModalOptions.value?.transform
})

const getPxValue = (v) => {
  if(v === undefined)
    return 'initial'
  
  if(typeof v === 'string'){
    return v
  } else {
    return v + 'px'
  }
}

const closeHandler = () => {
  useModal().close()
}
</script>

<style src="./transition.scss" lang="scss" scoped />

<style lang="scss" scoped>
@import '/assets/scss/_mixins.scss';
:deep(.modal) {
  @include _desktop {
    top: v-bind(topCss);
    bottom: v-bind(bottomCss);
    right: v-bind(rightCss);
    left: v-bind(leftCss);
    transform: v-bind(transformCss);
  }
}
</style> 

<template>
  <transition name="modal">
    <div v-if="isShow" @click.self="closeHandler" class="modal-wrapper" clickable>
      <div class="modal">
        <transition name="fade" mode="out-in" appear>
          <slot />
        </transition>
      </div>
    </div>
  </transition>
</template>