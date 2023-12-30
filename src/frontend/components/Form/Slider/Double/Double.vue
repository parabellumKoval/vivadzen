<script setup>
import Slider from '@vueform/slider'

const props = defineProps({
  compact: {
    type: Boolean,
    default: false
  }
})
const value = ref([20, 40])
</script>

<style src="./double.scss" lang="scss" scoped />
<style src="./../style.scss" lang="scss" />
<style src="@vueform/slider/themes/default.css"></style>

<template>
  <div class="slider-wrapper">
    
    <div v-if="compact" class="inputs-compact">
      <input v-if="value[0] !== undefined" type="number" v-model="value[0]" class="slider-input">
      <input v-if="value[1] !== undefined" type="number" v-model="value[1]" class="slider-input">
    </div>

    <div class="slider-inner">
      <input v-if="!compact && value[0] !== undefined" type="number" v-model="value[0]" class="slider-input">

      <div class="slider" >
        <Slider v-model="value" :lazy="false" :tooltips="false" clickable />
      </div>

      <input v-if="!compact && value[1] !== undefined" type="number" v-model="value[1]" class="slider-input">
    </div>
  </div>
</template>