<script setup>
const {t} = useI18n()
const props = defineProps({
  timeout: {
    type: Number,
    default: 3000
  }
})

const noties = computed(() => {
  return useNoty().noties.value
})

const closeHandler = (key) => {
  useNoty().removeNoty(key)
}

const playHandler = (key) => {
  useNoty().playNoty(key)
}

const getKey = (message) => {
  return Math.random()
}
</script>

<style src="./noty.scss" lang="scss" scoped />

<template>
  <div class="wrapper">
    <transition-group name="move-x">
      <div
        v-for="(noty, key) in noties"
        :key="noty.k"
        :class="noty.type"
        class="noty"
      >
        <div class="header">
          <div class="header-countdown">
            {{ noty.countdown }} {{ t('label.sec') }}...
          </div>
          <button @click="playHandler(key)" class="header-btn play-btn" type="button">
            <transition name="fade-in">
              <IconCSS v-if="noty.intervalInstance" name="iconoir:pause" size="20px" class="icon"></IconCSS>
              <IconCSS v-else name="iconoir:play" size="20px" class="icon"></IconCSS>
            </transition>
          </button>
          <button @click="closeHandler(key)" class="header-btn close-btn" type="button">
            <IconCSS name="iconoir:xmark" size="20px" class="icon"></IconCSS>
          </button>
        </div>

        <div v-if="noty.title" v-html="noty.title" class="title"></div>
        <div v-html="noty.content" class="content"></div>
      </div>
    </transition-group>
  </div>
</template>