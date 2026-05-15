<script setup lang="ts">
const props = withDefaults(defineProps<{
  modelValue: number
  min?: number
}>(), {
  min: 0
})

const emit = defineEmits<{
  'update:modelValue': [value: number]
}>()

const { haptic } = useTelegram()

const setValue = (value: number) => {
  haptic('selection')
  emit('update:modelValue', Math.max(props.min, value))
}
</script>

<template>
  <div class="qty-counter">
    <button type="button" class="qty-counter__btn" aria-label="Decrease" @click="setValue(modelValue - 1)">
      <TgIcon name="minus" :size="16" :stroke="2.6" />
    </button>
    <span class="qty-counter__value">{{ modelValue }}</span>
    <button type="button" class="qty-counter__btn" aria-label="Increase" @click="setValue(modelValue + 1)">
      <TgIcon name="plus" :size="16" :stroke="2.6" />
    </button>
  </div>
</template>

<style scoped>
.qty-counter {
  display: inline-flex;
  min-height: 36px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-white);
  padding: 3px;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.qty-counter__btn {
  display: grid;
  width: 28px;
  height: 28px;
  border: 0;
  border-radius: var(--radius-full);
  background: var(--color-ink);
  color: var(--color-white);
  place-items: center;
  line-height: 1;
}

.qty-counter__value {
  min-width: 22px;
  text-align: center;
  font-family: var(--font-display);
  font-size: 14px;
}
</style>
