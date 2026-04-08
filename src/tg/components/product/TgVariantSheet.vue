<script setup lang="ts">
import type { TgProduct, TgProductVariant } from '~/types/tg'
import { useTgCartStore } from '~/stores/cart'

const props = defineProps<{
  modelValue: boolean
  product: TgProduct | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const cart = useTgCartStore()
const { t } = useTgI18n()
const { haptic } = useTelegram()
const {
  variantsOf,
  imageOf,
  priceOf,
  oldPriceOf,
  currencyOf,
  formatMoney,
  variantLabel,
  isInStock
} = useTgProductUtils()

const selectedVariant = ref<TgProductVariant | null>(null)

const variants = computed(() => props.product ? variantsOf(props.product) : [])

watch(() => props.modelValue, (open) => {
  if (!open) return
  selectedVariant.value = variants.value.find((variant) => isInStock(variant)) || variants.value[0] || null
})

const addSelected = () => {
  if (!props.product || !selectedVariant.value) return
  cart.addItem(props.product, selectedVariant.value)
  haptic('light')
  emit('update:modelValue', false)
}
</script>

<template>
  <TgBottomSheet
    :model-value="modelValue"
    :title="t('choose_variant')"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <div v-if="product" class="variant-sheet">
      <div class="variant-sheet__product">
        <img :src="imageOf(product)" :alt="product.name" class="variant-sheet__image">
        <div>
          <div class="variant-sheet__name">{{ product.name }}</div>
          <div class="variant-sheet__price">
            <span v-if="selectedVariant && oldPriceOf(selectedVariant)" class="variant-sheet__old">
              {{ formatMoney(oldPriceOf(selectedVariant), currencyOf(selectedVariant)) }}
            </span>
            <strong v-if="selectedVariant">
              {{ formatMoney(priceOf(selectedVariant), currencyOf(selectedVariant)) }}
            </strong>
          </div>
          <div v-if="selectedVariant" class="variant-sheet__label">
            {{ variantLabel(selectedVariant) }}
          </div>
        </div>
      </div>

      <TgVariantPicker
        v-model="selectedVariant"
        :variants="variants"
        :label="t('choose_variant')"
      />

      <button
        type="button"
        class="tg-btn tg-btn--accent"
        :disabled="!selectedVariant"
        @click="addSelected"
      >
        {{ t('add_to_cart') }}
      </button>
    </div>
  </TgBottomSheet>
</template>

<style scoped>
.variant-sheet {
  display: grid;
  gap: 18px;
}

.variant-sheet__product {
  display: grid;
  grid-template-columns: 72px minmax(0, 1fr);
  gap: 12px;
  align-items: center;
}

.variant-sheet__image {
  width: 72px;
  height: 72px;
  border-radius: var(--radius-md);
  background: var(--color-bg-card);
  object-fit: contain;
  padding: 6px;
}

.variant-sheet__name {
  font-size: 14px;
  font-weight: 700;
  line-height: 1.35;
}

.variant-sheet__price {
  display: flex;
  margin-top: 6px;
  align-items: baseline;
  gap: 8px;
  color: var(--color-accent);
}

.variant-sheet__old {
  color: var(--color-text-muted);
  font-size: 12px;
  text-decoration: line-through;
}

.variant-sheet__label {
  margin-top: 4px;
  color: var(--color-text-muted);
  font-size: 12px;
}
</style>
