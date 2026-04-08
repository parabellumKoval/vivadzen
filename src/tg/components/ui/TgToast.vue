<script setup lang="ts">
import { useTgUiStore } from '~/stores/ui'

const ui = useTgUiStore()
</script>

<template>
  <Transition name="toast">
    <div
      v-if="ui.toast"
      class="tg-toast"
      :class="ui.toast.type ? `tg-toast--${ui.toast.type}` : ''"
      role="status"
    >
      {{ ui.toast.content }}
    </div>
  </Transition>
</template>

<style scoped>
.tg-toast {
  position: fixed;
  z-index: 300;
  right: 16px;
  bottom: calc(72px + env(safe-area-inset-bottom));
  left: 16px;
  max-width: 448px;
  margin: 0 auto;
  border-radius: var(--radius-md);
  background: var(--color-text);
  color: var(--color-white);
  padding: 12px 16px;
  text-align: center;
  font-size: 14px;
}

.tg-toast--error {
  background: var(--color-danger);
}

.tg-toast--success {
  background: var(--color-primary-dark);
}

.toast-enter-active,
.toast-leave-active {
  transition: opacity 0.2s, transform 0.2s;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>
