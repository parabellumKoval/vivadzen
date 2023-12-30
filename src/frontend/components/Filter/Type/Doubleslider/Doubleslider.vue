<script setup>
import Slider from '@vueform/slider'

const props = defineProps({
  filter: {
    type: Object
  }
})
const value = ref([20, 40])
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
      <button class="slider-btn">OK</button>
    </div>

    <div class="slider-inner">

      <div class="slider" >
        <Slider v-model="value" :lazy="false" :tooltips="false" clickable />
      </div>
    </div>
  </div>
</template>