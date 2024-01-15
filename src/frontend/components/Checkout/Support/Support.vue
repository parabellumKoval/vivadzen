<script setup>
// import 'vue3-lottie/dist/style.css'
import {Vue3Lottie} from 'vue3-lottie'

const {t} = useI18n()

const ctaElement = ref(null)
const lottieElement = ref(null)
const playedTimes = ref(0)
const observer = ref(null)

const props = defineProps({
  noLottie: {
    type: Boolean,
    default: false
  }
})

const setObserver = () => {
  observer.value = new IntersectionObserver((v) => {
    if(v && v[0] && v[0].isIntersecting && playedTimes.value === 0) {
      lottieElement.value.goToAndPlay(1)
      ++playedTimes.value
    }
  }, {
    root: null,
    rootMargin: '0px',
    threshold: 1.0
  })
  observer.value.observe(ctaElement.value)
}

// HANDLERS
const playHandler = () => {
  lottieElement.value.goToAndPlay(5)
}

watch(ctaElement, (v) => {
  if(!observer.value && v) 
    setObserver()
}, {
  immediate: true
})

onBeforeUnmount(() => {
  observer.value.disconnect()
})
</script>

<style src="./support.scss" lang="scss" scoped />

<template>
  <div @mouseenter="playHandler" ref="ctaElement" class="support">

    <div v-if="!noLottie" class="lottie-wrapper">
      <!-- <Vue3Lottie
        animationLink="/lottie/support-abu.json"
        :height="200"
        width="100%"
        :loop="1"
        :autoPlay="true"
        class="lottie"
        ref="lottieElement"
      /> -->
    </div>

    <div>
      <div class="title">{{ t('title') }}</div>
      <div class="desc">{{ t('desc') }}</div>
      <div class="phone">{{ useContacts().all.value.phone }}</div>
      <NuxtLink :to="localePath('/contacts')" class="link all-contacts">
        <span>{{ t('contacts') }}</span>
        <IconCSS name="fluent:chevron-right-48-filled" size="18px" class="icon"></IconCSS>
      </NuxtLink>
    </div>
  </div>
</template>

<i18n src="./lang.yaml" lang="yaml"></i18n>