<script setup lang="ts">
defineProps<{
  modelValue: boolean
  title?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const close = () => emit('update:modelValue', false)
</script>

<template>
  <Teleport to="body">
    <Transition name="sheet">
      <div v-if="modelValue" class="sheet-overlay" @click.self="close">
        <div class="sheet">
          <div class="sheet__handle" />
          <div v-if="title" class="sheet__title">{{ title }}</div>
          <slot :close="close" />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.sheet-overlay {
  position: fixed;
  z-index: 200;
  inset: 0;
  display: flex;
  align-items: flex-end;
  background: rgba(0, 0, 0, 0.4);
}

.sheet {
  width: 100%;
  max-width: 480px;
  max-height: 85vh;
  margin: 0 auto;
  overflow-y: auto;
  border-top: 2px solid var(--color-ink);
  border-radius: 24px 24px 0 0;
  background: var(--color-bg);
  box-shadow: var(--shadow-modal);
  padding: 14px 16px calc(20px + env(safe-area-inset-bottom));
}

.sheet__handle {
  width: 48px;
  height: 5px;
  margin: 0 auto 16px;
  border-radius: 999px;
  background: var(--color-ink);
}

.sheet__title {
  margin-bottom: 16px;
  font-family: var(--font-display);
  font-size: 18px;
  letter-spacing: -0.01em;
  text-transform: uppercase;
}

.sheet-enter-active,
.sheet-leave-active {
  transition: opacity 0.2s;
}

.sheet-enter-active .sheet,
.sheet-leave-active .sheet {
  transition: transform 0.25s cubic-bezier(0.32, 0.72, 0, 1);
}

.sheet-enter-from,
.sheet-leave-to {
  opacity: 0;
}

.sheet-enter-from .sheet,
.sheet-leave-to .sheet {
  transform: translateY(100%);
}
</style>
