<script setup lang="ts">
import { resolveKratomProductImageSrc } from '~/utils/productImage'
import { useKratomBadges } from '~/utils/kratomBadges'
import { isProductAvailable } from '~/utils/productAvailability'

type ProductRecord = Record<string, any>

const props = defineProps<{
  product: ProductRecord
}>()

const { t } = useI18n()
const cartStore = useCartStore()
const modal = useModal()
const regionPath = useToLocalePath()
const selectedModification = ref<ProductRecord | null>(null)

const image = computed(() => {
  return resolveKratomProductImageSrc(props.product)
})

const attrs = computed(() => Array.isArray(props.product?.attrs) ? props.product.attrs : [])

const badges = computed(() => {
  return useKratomBadges(attrs.value).list
})

const modifications = computed<ProductRecord[]>(() => {
  if (!Array.isArray(props.product?.modifications)) {
    return []
  }

  return props.product.modifications.filter((modification: ProductRecord | null) => {
    return Boolean(modification && (modification.short_name || modification.name || modification.id))
  })
})

const activeProduct = computed(() => selectedModification.value ?? props.product)

const displayPrice = computed(() => {
  return activeProduct.value?.price ?? props.product?.price
})

const displayOldPrice = computed(() => {
  return activeProduct.value?.old_price ?? activeProduct.value?.oldPrice ?? activeProduct.value?.basePrice ?? null
})

const currencyCode = computed(() => {
  return activeProduct.value?.currency ?? props.product?.currency
})

const getModificationKey = (modification: ProductRecord | null) => {
  if (!modification) {
    return null
  }

  return modification.id ?? modification.slug ?? modification.short_name ?? modification.name ?? null
}

const getDefaultModification = (list: ProductRecord[]) => {
  return list.find((modification) => isProductAvailable(modification)) ?? list[0] ?? null
}

watch(modifications, (list) => {
  if (!list.length) {
    selectedModification.value = null
    return
  }

  const currentKey = getModificationKey(selectedModification.value)
  const current = list.find((modification) => getModificationKey(modification) === currentKey)
  selectedModification.value = current ?? getDefaultModification(list)
}, { immediate: true, deep: true })

const selectModification = (modification: ProductRecord) => {
  selectedModification.value = modification
}

const isActiveProductAvailable = computed(() => {
  return isProductAvailable(activeProduct.value)
})

const addToCart = async () => {
  if (!isActiveProductAvailable.value) {
    return
  }

  await cartStore.add({ ...props.product, ...activeProduct.value, amount: 1 })
  modal.open(resolveComponent('ModalCart'), null, null, {
    width: { min: 968, max: 968 },
  })
}
</script>

<template>
  <div class="kratom-product-card-frame" :class="{ 'is-unavailable': !isActiveProductAvailable }">
    <div class="kratom-product-card-shell">
      <div class="kratom-product-card">
        <span class="kratom-product-card__ribbon" aria-hidden="true">
          <!-- <span>premium quality</span> -->
          <!-- <span>100% Legal</span> -->
          <span>{{ t('kratom.product_card.ribbon') }}</span>
        </span>
        <NuxtLink :to="regionPath(`/${product.slug}`)" class="kratom-product-card__media">
          <span class="kratom-product-card__media-clip">
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
            <span v-if="badges.length" class="kratom-product-card__badges" :aria-label="t('kratom.product_card.badges_label')">
              <span v-for="badge in badges" :key="`${badge.attribute?.id || badge.attribute?.slug}-${badge.key}`" class="kratom-product-card__badge">
                <nuxt-img
                  :src="badge.image"
                  :alt="badge.label"
                  :title="badge.label"
                  width="64"
                  height="64"
                  sizes="64px"
                  format="webp"
                  quality="80"
                />
              </span>
            </span>
          </span>
        </NuxtLink>

        <div class="kratom-product-card__body">
          <!-- <div class="kratom-product-card__meta">
            <span v-if="product.brand?.name">{{ product.brand.name }}</span>
            <span v-if="product.code">#{{ product.code }}</span>
          </div> -->

          <NuxtLink :to="regionPath(`/${product.slug}`)" class="kratom-product-card__title">
            {{ product.name }}
          </NuxtLink>

          <p v-if="product.short_description" class="kratom-product-card__excerpt">
            {{ product.short_description }}
          </p>

          <div class="kratom-product-card__mods-row">
            <div v-if="modifications.length" class="kratom-product-card__mods">
              <button
                v-for="(modification, index) in modifications"
                :key="modification.id ?? modification.slug ?? index"
                type="button"
                class="kratom-product-card__mod"
                :class="{ 'is-active': getModificationKey(selectedModification) === getModificationKey(modification) }"
                @click="selectModification(modification)"
              >
                {{ modification.short_name || modification.name }}
              </button>
            </div>

            <div class="kratom-product-card__price kratom-product-card__price--mobile">
              <simple-price
                v-if="displayOldPrice && Number(displayOldPrice) > Number(displayPrice)"
                :value="displayOldPrice"
                :currency-code="currencyCode"
                class="kratom-product-card__old-price"
              />
              <simple-price :value="displayPrice" :currency-code="currencyCode" class="kratom-product-card__current-price" />
            </div>
          </div>

          <div class="kratom-product-card__footer">
            <div class="kratom-product-card__price kratom-product-card__price--desktop">
              <simple-price
                v-if="displayOldPrice && Number(displayOldPrice) > Number(displayPrice)"
                :value="displayOldPrice"
                :currency-code="currencyCode"
                class="kratom-product-card__old-price"
              />
              <simple-price :value="displayPrice" :currency-code="currencyCode" class="kratom-product-card__current-price" />
            </div>

            <button
              v-if="isActiveProductAvailable"
              type="button"
              class="button primary kratom-product-card__action"
              @click="addToCart"
            >
              <IconCSS name="ci:shopping-cart-01" class="kratom-product-card__action-icon" />
              <span>{{ t('button.to_cart') }}</span>
            </button>
            <span v-else class="kratom-product-card__availability">
              {{ t('label.not_available') }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-product-card-frame {
  position: relative;
  height: 100%;
  transition: transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);

  &::before {
    content: '';
    position: absolute;
    inset: 3px;
    border-radius: 32px;
    box-shadow: 0 24px 60px rgba(43, 55, 41, 0.05);
    transition: box-shadow 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
    pointer-events: none;
  }

  &:hover {
    transform: translateY(-8px);

    &::before {
      box-shadow: 0 36px 80px rgba(43, 55, 41, 0.12);
    }

    :deep(img) {
      transform: scale(1.08) translateY(-4px);
    }
  }

  &.is-unavailable {
    opacity: 0.58;

    &:hover {
      transform: none;

      &::before {
        box-shadow: 0 24px 60px rgba(43, 55, 41, 0.05);
      }

      :deep(img) {
        transform: none;
      }
    }
  }
}

.kratom-product-card-shell {
  position: relative;
  height: 100%;
  padding: 3px;
  overflow: hidden;
  border-radius: 32px;
}

.kratom-product-card {
  position: relative;
  display: flex;
  flex-direction: column;
  height: 100%;
  border-radius: 29px;
  container-type: inline-size;

  &::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 0;
    border-radius: inherit;
    background: $color-8;
    border: 1px solid rgba(74, 91, 68, 0.08);
  }

  > * {
    position: relative;
    z-index: 1;
  }
}

.kratom-product-card__media {
  position: relative;
  aspect-ratio: 3/2;
  display: block;
  border-radius: 32px 32px 0 0;
  overflow: visible;
  isolation: isolate;

  @include desktop {
    aspect-ratio: 1/1;
  }
}

.kratom-product-card__media-clip {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 28px 20px;
  background-color: #f0f0f0;
  border-radius: inherit;
  overflow: hidden;
  z-index: 0;

  :deep(img) {
    width: 120%;
    height: 120%;
    object-fit: contain;
    max-width: initial;
    filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.12));
    transition: transform 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
  }
}

.kratom-product-card__badges {
  position: absolute;
  right: clamp(14px, 5cqw, 20px);
  bottom: clamp(14px, 5cqw, 20px);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  z-index: 2;
}

.kratom-product-card__badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: clamp(52px, 14cqw, 60px);
  height: clamp(52px, 14cqw, 60px);
  flex: 0 0 auto;

  :deep(img) {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 10px 20px rgba(33, 39, 30, 0.18));
  }
}

.kratom-product-card__ribbon {
  position: absolute;
  top: 35px;
  left: -47px;
  z-index: 4;
  width: 192px;
  padding: 5px 20px 6px;
  box-sizing: border-box;
  transform: rotate(-45deg);
  overflow: visible;
  background:
    // linear-gradient(180deg, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0) 38%, rgba(106, 98, 132, 0.08) 100%),
    linear-gradient(90deg, rgba(105, 96, 130, 0.14) 0%, rgba(255, 255, 255, 0) 15%, rgba(255, 255, 255, 0) 85%, rgba(105, 96, 130, 0.14) 100%),
    linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.2) 30%, rgba(255, 255, 255, 0.55) 52%, rgba(255, 255, 255, 0.18) 72%, rgba(255, 255, 255, 0.8)),
    linear-gradient(90deg, #b9dafa 0%, #d1e7ff 18%, #efd5f9 50%, #d6eef2 78%, #b9dafa 100%);
  border-top: 1px solid rgba(255, 255, 255, 0.65);
  border-bottom: 1px solid rgba(120, 112, 142, 0.14);
  box-shadow:
    0 4px 14px rgba(132, 144, 168, 0.12),
    inset 0 1px 0 rgba(255, 255, 255, 0.65),
    inset 0 -1px 0 rgba(120, 112, 142, 0.1);
  text-align: center;
  filter: saturate(1.08) contrast(1.02);
  --tri-color: rgb(185 195 210);

  &::before {
    content: '';
    position: absolute;
    top: 23px;
    right: 13px;
    width: 4px;
    height: 4px;
    background: var(--tri-color);
    clip-path: polygon(0% 0%, 100% 0%, 0% 100%);
    z-index: -1;
    transform: rotate(90deg);
  }

  &::after {
    content: '';
    position: absolute;
    top: 23px;
    left: 10px;
    width: 4px;
    height: 4px;
    background: var(--tri-color);
    clip-path: polygon(0 0, 0 100%, 100% 100%);
    z-index: -1;
    transform: rotate(90deg);
  }

  span {
    display: block;
    width: 100%;
    color: rgba($color-0, 0.8);
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 0.16em;
    line-height: 1.15;
    text-shadow:
      0 1px 0 rgba(255, 255, 255, 0.62),
      0 -1px 0 rgba(95, 85, 121, 0.18),
      1px 0 0 rgba(255, 255, 255, 0.2),
      -1px 0 0 rgba(104, 94, 131, 0.08);
    text-transform: uppercase;
    white-space: nowrap;
  }
}


.kratom-product-card__mods {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.kratom-product-card__mods-row {
  display: block;
}

.kratom-product-card__mod {
  min-height: 30px;
  padding: 0 16px;
  border-radius: 999px;
  border: 1px solid rgba($color-border, 0.8);
  background: rgba(255, 255, 255, 0.78);
  color: rgba($color-0, 0.8);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease, transform 0.25s ease;

  &:hover {
    transform: translateY(-2px);
    border-color: rgba(242, 141, 26, 0.4);
  }

  &.is-active {
    // background: rgb(240 240 240);
    // background: $color-8;
    background: $color-green;
    border-color: rgba($color-green, 0.8);
    // color: rgba($color-0, 0.8);
    color: $color-8;
  }
}

.kratom-product-card__body {
  // padding: 40px 40px 30px 40px;
  padding: clamp(20px, 10cqw, 30px);
  padding-bottom: clamp(10px, 10cqw, 30px);
  display: flex;
  flex: 1;
  flex-direction: column;
  gap: 20px;
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
  height: 100%;
  color: #1b2619;
  text-decoration: none;
  font-family: var(--font-display);
  font-size: 20px;
  line-height: 1.25;
  font-weight: 500;
  // text-transform: uppercase;
  color: $color-0;
  text-align: left;
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
  align-items: center;
  justify-content: space-between;
}

.kratom-product-card__price {
  display: grid;
  gap: 6px;
}

.kratom-product-card__price--mobile {
  display: none;
}

// :deep(.kratom-product-card__old-price .value) {
//   font-size: 15px;
//   color: #8b9284;
//   text-decoration: line-through;
// }

:deep(.kratom-product-card__current-price .value) {
  font-size: clamp(16px, 7cqw, 22px);
  font-weight: 500;
  color: $color-orange;
}

.kratom-product-card__action {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  border-radius: 999px;
  background: linear-gradient(135deg, #f28d1a, #e67d0e);
  color: white;
  border: none;
  transition: background 0.3s ease, transform 0.2s ease;
  padding: 0 4cqw;
  text-transform: uppercase;
  font-size: clamp(12px, 5.5cqw, 15px);
  font-weight: 600;
  height: 44px;

  &:hover {
    background: linear-gradient(135deg, #e67d0e, #d56d05);
    transform: scale(1.02);
  }

  &-icon {
    font-size: clamp(12px, 7.5cqw, 20px);
  }
}

.kratom-product-card__availability {
  display: inline-flex;
  align-items: center;
  min-height: 44px;
  padding: 0 18px;
  border-radius: 999px;
  background: rgba(90, 99, 86, 0.12);
  color: rgba(56, 62, 53, 0.8);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

@media (max-width: 767px) {
  .kratom-product-card__mods-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  .kratom-product-card__mods {
    flex: 1 1 auto;
    min-width: 0;
  }

  .kratom-product-card__price--mobile {
    display: grid;
    flex: 0 0 auto;
    text-align: right;
    justify-items: end;
  }

  .kratom-product-card__price--desktop {
    display: none;
  }

  .kratom-product-card__footer {
    justify-content: flex-start;
  }
}
</style>
