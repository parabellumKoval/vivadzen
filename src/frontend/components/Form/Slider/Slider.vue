<script setup>
import Slider from '@vueform/slider'
const value = ref(0)

const changeHandler = (v) => {
  console.log('v', v)
}
</script>

<style src="./slider.scss" lang="scss" scoped />
<style src="./style.scss" lang="scss" />
<style src="@vueform/slider/themes/default.css"></style>

<template>
  <div class="slider-wrapper">

    <div class="slider" >
      <Slider v-model="value" :lazy="false" :tooltips="false" />
    </div>

    <input type="number" v-model="value" class="slider-input">
  </div>
</template>