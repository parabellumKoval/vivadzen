<script setup>
import Slider from '@vueform/slider'

const props = defineProps({
  modelValue: {
    type: Array
  },

  filter: {
    type: Object
  },

  meta: {
    type: Object
  }
})

const emit = defineEmits(['update:modelValue'])

const limits = ref([0, 0])
const value = ref([0, 0])
const delay = ref(null)

watch(() => props.meta, (v) => {
  // console.log('watch', v)
  if(v && v.min && v.max) {
    limits.value = [v.min, v.max]
    value.value = [v.min, v.max]
  }
}, {
  deep: true,
  immediate: true
})

// HANDLERS
const changeHandler = (v) => {}

const setHandler = (v) => {
  let modelValueCopy = {...props.modelValue}

  if(!modelValueCopy[props.filter.id]) {
    modelValueCopy[props.filter.id] = {}
  }

  modelValueCopy[props.filter.id] = {min: v[0], max: v[1]}

  delay.value = setTimeout(() => {
    emit('update:modelValue', modelValueCopy)
  }, 500)
}
</script>

<style src="./doubleslider.scss" lang="scss" scoped />
<style src="./style.scss" lang="scss" />
<style src="@vueform/slider/themes/default.css"></style>

<template>
  <div class="slider-wrapper">
    
    <div class="slider-form">
      <div class="input-wrapper">
        <input v-if="value[0] !== undefined" type="number" v-model="value[0]" class="input-form">
        <div class="input-line"></div>
        <input v-if="value[1] !== undefined" type="number" v-model="value[1]" class="input-form">
      </div>
      <!-- <button class="slider-btn">OK</button> -->
    </div>

    <div class="slider-inner">

      <div class="slider" >
        <Slider
          v-model="value"
          :min="limits[0]"
          :max="limits[1]"
          :lazy="false"
          :tooltips="false"
          @change="changeHandler"
          @set="setHandler"
          clickable 
        />
      </div>
    </div>
  </div>
</template>