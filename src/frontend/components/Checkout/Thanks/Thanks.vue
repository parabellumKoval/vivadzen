<script setup>
import {Vue3Lottie} from 'vue3-lottie'
const {t} = useI18n() 

const ctaElement = ref(null)
const lottieElement = ref(null)
const playedTimes = ref(0)

const setObserver = () => {
  const observer = new IntersectionObserver((v) => {
    if(v && v[0] && v[0].isIntersecting && playedTimes.value === 0) {
      lottieElement.value.goToAndPlay(1)
      ++playedTimes.value
    }
  }, {
    root: null,
    rootMargin: '0px',
    threshold: 1.0
  })

  observer.observe(ctaElement.value)
}

// HANDLERS
const playHandler = () => {
  lottieElement.value.goToAndPlay(1)
}

onMounted(() => {
  setObserver()
})
</script>

<style src="./thanks.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div @mouseenter="playHandler" ref="ctaElement" class="thx">
    <div class="lottie-wrapper">
      <Vue3Lottie
        animationLink="/lottie/shop-abu.json"
        height="auto"
        width="100%"
        :loop="1"
        :autoPlay="true"
        ref="lottieElement"
        class="lottie"
      />
    </div>

    <div class="title-secondary">{{ t('thx') }}</div>
    <div class="desc">{{ t('thx_desc') }}</div>
    
  </div>
</template>