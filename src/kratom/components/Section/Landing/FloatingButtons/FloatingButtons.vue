<script setup>
import ScrollToTopButton from './ScrollToTopButton.vue'
import ChatSupportButton from './ChatSupportButton.vue'
</script>

<template>
  <div class="landing-floating-buttons" aria-label="Landing quick actions">
    <ScrollToTopButton />
    <ChatSupportButton />
  </div>
</template>

<style scoped lang="scss">
.landing-floating-buttons {
  position: fixed;
  right: clamp(10px, 2vw, 28px);
  bottom: clamp(12px, 2.4vw, 30px);
  z-index: 90;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
</style>
