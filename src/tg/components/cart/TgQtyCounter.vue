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
    <button type="button" class="qty-counter__btn" @click="setValue(modelValue - 1)">−</button>
    <span class="qty-counter__value">{{ modelValue }}</span>
    <button type="button" class="qty-counter__btn" @click="setValue(modelValue + 1)">+</button>
  </div>
</template>

<style scoped>
.qty-counter {
  display: inline-flex;
  min-height: 36px;
  border-radius: var(--radius-sm);
  background: var(--color-bg-card);
  padding: 4px 6px;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.qty-counter__btn {
  width: 28px;
  height: 28px;
  border: 0;
  border-radius: var(--radius-full);
  background: var(--color-primary);
  color: var(--color-white);
  font-size: 18px;
  line-height: 1;
}

.qty-counter__value {
  min-width: 20px;
  text-align: center;
  font-size: 14px;
  font-weight: 700;
}
</style>
