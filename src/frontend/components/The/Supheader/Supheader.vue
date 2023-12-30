<script setup>
  const isShow = ref(true)

  const close = () => {
    isShow.value = false
  }
</script>

<style src="./supheader.scss" lang="scss" scoped />

<template>
  <transition name="fade-in">
    <div v-if="isShow" class="supheader">
      <div class="container">
        <div class="inner">
          <div class="content">
            <div>🪖  Зроби донат для ЗСУ у розмірі не менше 150 грн та отримай купон на знижку 15% на всі товари українського бренду All Be Ukraine. Пропозиція діє до 31.08.2023</div>
          </div>
          <button @click="close" class="close-btn btn">
            <IconCSS name="iconoir:cancel" size="24" class="icon"></IconCSS>
          </button>
        </div>
      </div>
    </div>
  </transition> 
</template>