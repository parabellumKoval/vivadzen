<script setup lang="ts">
const props = defineProps<{
  product: Record<string, any>
}>()

const { t } = useI18n()
const regionPath = useToLocalePath()

const image = computed(() => {
  const images = Array.isArray(props.product?.images) ? props.product.images : []
  return props.product?.image?.src || images[0]?.src || useImg().noImage
})

const hasMultipleModifications = computed(() => {
  return Array.isArray(props.product?.modifications) && props.product.modifications.length > 1
})

const displayPrice = computed(() => {
  const basePrice = props.product?.old_price ?? props.product?.oldPrice ?? props.product?.basePrice
  return basePrice ?? props.product?.price
})

const addToCart = async () => {
  if (hasMultipleModifications.value) {
    await navigateTo(regionPath(`/${props.product.slug}`))
    return
  }

  await useCartStore().add({ ...props.product, amount: 1 })
  useModal().open(resolveComponent('ModalCart'), null, null, {
    width: { min: 968, max: 968 },
  })
}
</script>

<template>
  <article class="kratom-product-card">
    <NuxtLink :to="regionPath(`/${product.slug}`)" class="kratom-product-card__media">
      <nuxt-img
        :src="image"
        :alt="product.name"
        :title="product.name"
        width="640"
        height="640"
        sizes="mobile:90vw tablet:45vw desktop:360px"
        format="webp"
        quality="70"
        fit="contain"
      />
      <span v-if="hasMultipleModifications" class="kratom-product-card__badge">
        {{ product.modifications.length }} {{ t('kratom.product_card.variants') }}
      </span>
    </NuxtLink>

    <div class="kratom-product-card__body">
      <div class="kratom-product-card__meta">
        <span v-if="product.brand?.name">{{ product.brand.name }}</span>
        <span v-if="product.code">#{{ product.code }}</span>
      </div>

      <NuxtLink :to="regionPath(`/${product.slug}`)" class="kratom-product-card__title">
        {{ product.name }}
      </NuxtLink>

      <p v-if="product.short_description || product.excerpt" class="kratom-product-card__excerpt">
        {{ product.short_description || product.excerpt }}
      </p>

      <div class="kratom-product-card__footer">
        <div class="kratom-product-card__price">
          <simple-price :value="displayPrice" :currency-code="product.currency" class="kratom-product-card__current-price" />
        </div>

        <button type="button" class="button primary kratom-product-card__action" @click="addToCart">
          <span>{{ t('button.buy') }}</span>
          <IconCSS name="mynaui:arrow-right-solid" />
        </button>
      </div>
    </div>
  </article>
</template>

<style scoped lang="scss">
.kratom-product-card {
  display: flex;
  flex-direction: column;
  height: 100%;
  border-radius: 28px;
  overflow: hidden;
  background: rgba(255, 250, 244, 0.92);
  border: 1px solid rgba(74, 91, 68, 0.1);
  box-shadow: 0 24px 60px rgba(43, 55, 41, 0.08);
}

.kratom-product-card__media {
  position: relative;
  padding: 28px;
  min-height: 260px;
  display: flex;
  align-items: center;
  justify-content: center;
  background:
    radial-gradient(circle at top, rgba(143, 180, 134, 0.22), transparent 48%),
    linear-gradient(180deg, rgba(243, 233, 221, 0.9), rgba(255, 250, 244, 0.2));
}

.kratom-product-card__badge {
  position: absolute;
  top: 18px;
  left: 18px;
  padding: 8px 12px;
  border-radius: 999px;
  background: rgba(31, 43, 29, 0.88);
  color: #fff7ec;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.kratom-product-card__body {
  padding: 22px;
  display: flex;
  flex: 1;
  flex-direction: column;
  gap: 14px;
}

.kratom-product-card__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  font-size: 11px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #697162;
}

.kratom-product-card__title {
  color: #1b2619;
  text-decoration: none;
  font-family: var(--font-display);
  font-size: 28px;
  line-height: 1.02;
}

.kratom-product-card__excerpt {
  color: #5f6458;
  line-height: 1.6;
}

.kratom-product-card__footer {
  margin-top: auto;
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: end;
  justify-content: space-between;
}

.kratom-product-card__price {
  display: grid;
  gap: 6px;
}

:deep(.kratom-product-card__current-price .value) {
  font-size: 30px;
  font-weight: 800;
  color: #1f2b1d;
}

.kratom-product-card__action {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  border-radius: 999px;
}
</style>
