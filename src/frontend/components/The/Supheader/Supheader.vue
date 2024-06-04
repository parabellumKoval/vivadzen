<script setup>
  const {t} = useI18n()
  const isShow = ref(true)

  const close = () => {
    isShow.value = false
  }
</script>

<style src="./supheader.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <transition name="fade-in">
    <div v-if="isShow" class="supheader">
      <div class="container">
        <div class="inner">
          <div class="content">
            <div>🪖 {{ t('text') }}</div>
          </div>
          <button @click="close" class="close-btn btn">
            <IconCSS name="iconoir:cancel" size="24" class="icon"></IconCSS>
          </button>
        </div>
      </div>
    </div>
  </transition> 
</template>