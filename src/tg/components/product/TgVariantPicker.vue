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
  font-family: var(--font-display);
  font-size: 12px;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--color-text-muted);
}

.variant-picker__items {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.variant-chip {
  display: inline-grid;
  min-height: 46px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-md);
  background: var(--color-white);
  padding: 8px 14px;
  color: var(--color-ink);
  gap: 2px;
  font-size: 14px;
  font-weight: 700;
  box-shadow: var(--shadow-card-sm);
  transition: transform 0.08s ease, box-shadow 0.08s ease;
}

.variant-chip:hover {
  transform: translate(-1px, -1px);
  box-shadow: 3px 3px 0 0 var(--color-ink);
}

.variant-chip small {
  color: var(--color-text-muted);
  font-size: 11px;
  font-weight: 600;
}

.variant-chip.active {
  background: var(--color-lime);
  color: var(--color-ink);
}

.variant-chip.active small {
  color: var(--color-ink);
}

.variant-chip.disabled {
  opacity: 0.4;
  text-decoration: line-through;
  box-shadow: none;
}
</style>
