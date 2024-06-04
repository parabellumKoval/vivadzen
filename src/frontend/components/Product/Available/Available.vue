<script setup>
const {t} = useI18n({useScope: 'global'})

const props = defineProps({
  inStock: {
    type: Number
  },
  type: {
    type: String,
    default: 'short'
  }
})

// COMPUTEDS
const visibleTypes = computed(() => {
  if(props.inStock > 2){
    return ['in_stock']
  }else if(props.inStock > 0 && props.inStock <= 2) {
    return props.type === 'full'? ['running_out', 'in_stock']: ['running_out']
  }else {
    return ['not_available']
  }
}) 
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./available.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="available">
    <div v-if="visibleTypes.includes('in_stock')" class="available-item in_stock">
      <IconCSS name="iconoir:check" class="icon"></IconCSS> {{ t('label.available') }}
    </div>
    <div v-if="visibleTypes.includes('running_out')" class="available-item running_out">
      {{ t('label.running_out') }}
    </div>
    <div v-if="visibleTypes.includes('not_available')" class="available-item not_available">{{ t('label.not_available') }}</div>
  </div>
</template>