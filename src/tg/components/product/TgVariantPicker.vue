<script setup lang="ts">
import type { TgProductVariant } from '~/types/tg'

const props = defineProps<{
  variants: TgProductVariant[]
  modelValue?: TgProductVariant | null
  label?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: TgProductVariant]
}>()

const { variantLabel, formatMoney, priceOf, currencyOf, isInStock } = useTgProductUtils()
</script>

<template>
  <div v-if="variants.length" class="variant-picker">
    <div v-if="label" class="variant-picker__label">{{ label }}</div>
    <div class="variant-picker__items">
      <button
        v-for="variant in props.variants"
        :key="variant.id || variant.slug"
        type="button"
        class="variant-chip"
        :class="{ active: modelValue?.id === variant.id, disabled: !isInStock(variant) }"
        :disabled="!isInStock(variant)"
        @click="emit('update:modelValue', variant)"
      >
        <span>{{ variantLabel(variant) }}</span>
        <small v-if="priceOf(variant)">{{ formatMoney(priceOf(variant), currencyOf(variant)) }}</small>
      </button>
    </div>
  </div>
</template>

<style scoped>
.variant-picker {
  display: grid;
  gap: 10px;
}

.variant-picker__label {
  font-size: 13px;
  font-weight: 700;
}

.variant-picker__items {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.variant-chip {
  display: inline-grid;
  min-height: 44px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  background: var(--color-white);
  padding: 8px 14px;
  color: var(--color-text);
  gap: 2px;
  font-size: 14px;
  font-weight: 600;
}

.variant-chip small {
  color: var(--color-text-muted);
  font-size: 11px;
  font-weight: 500;
}

.variant-chip.active {
  border-color: var(--color-primary);
  background: rgba(115, 197, 111, 0.1);
  color: var(--color-primary);
}

.variant-chip.disabled {
  opacity: 0.45;
}
</style>
