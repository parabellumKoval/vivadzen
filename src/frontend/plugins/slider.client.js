import Slider from '@vueform/slider'

export default defineNuxtPlugin((nuxtApp) => {
  
  if(!nuxtApp.vueApp.component('UiSlider')) {
    nuxtApp.vueApp.component('UiSlider', Slider)
  }

})