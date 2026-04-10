<script setup lang="ts">
import { resolveKratomProductImageSrc } from '~/utils/productImage'

const { t, d } = useI18n()
const regionPath = useToLocalePath()

const props = defineProps<{
  order: Record<string, any>
}>()

const recipient = computed(() => {
  return [
    props.order?.user?.first_name,
    props.order?.user?.last_name,
    props.order?.user?.phone,
    props.order?.user?.email,
  ]
    .filter(Boolean)
    .join(', ')
})

const delivery = computed(() => {
  return [
    props.order?.delivery?.method ? t(`form.delivery.${props.order.delivery.method}`) : null,
    props.order?.delivery?.settlement || props.order?.delivery?.city,
    props.order?.delivery?.warehouse,
    props.order?.delivery?.street,
    props.order?.delivery?.house,
    props.order?.delivery?.zip,
  ]
    .filter(Boolean)
    .join(', ')
})

const payment = computed(() => {
  return props.order?.payment?.method
    ? t(`form.payment.${props.order.payment.method}`)
    : null
})

const productCount = computed(() => Array.isArray(props.order?.products) ? props.order.products.length : 0)

const orderDate = computed(() => {
  if (!props.order?.created_at) {
    return ''
  }

  const value = new Date(props.order.created_at)
  return Number.isNaN(value.getTime()) ? '' : d(value, 'short')
})

const getProductLink = (product: Record<string, any>) => {
  const slug = typeof product?.slug === 'string' ? product.slug.trim() : ''
  return slug ? regionPath(`/${slug}`) : regionPath('/account/orders')
}

const getProductImage = (product: Record<string, any>) => {
  return resolveKratomProductImageSrc(product)
}
</script>

<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <article class="order-card">
    <header class="order-card__header">
      <div>
        <div class="order-card__code">#{{ order.code }}</div>
        <div class="order-card__date">{{ orderDate }}</div>
      </div>

      <div class="order-card__summary">
        <span class="order-card__status" :class="`is-${order.status}`">{{ $t(`status.${order.status}`) }}</span>
        <simple-price :value="order.price" :currency-code="order.currencyCode || order.currency_code" />
      </div>
    </header>

    <div class="order-card__products">
      <NuxtLink
        v-for="product in order.products || []"
        :key="`${order.id}-${product.id}`"
        :to="getProductLink(product)"
        class="order-card__product"
      >
        <nuxt-img
          :src="getProductImage(product)"
          width="56"
          height="72"
          fit="contain"
          format="webp"
          quality="70"
          class="order-card__product-image"
        />
        <div class="order-card__product-content">
          <div class="order-card__product-name">{{ product.name }}</div>
          <div class="order-card__product-meta">
            <span v-if="product.short_name">{{ product.short_name }}</span>
            <span>x{{ product.amount || 1 }}</span>
          </div>
        </div>
      </NuxtLink>
    </div>

    <div class="order-card__details">
      <div v-if="recipient" class="order-card__detail">
        <div class="order-card__label">{{ t('recipient') }}</div>
        <div>{{ recipient }}</div>
      </div>

      <div v-if="delivery" class="order-card__detail">
        <div class="order-card__label">{{ t('title.delivery') }}</div>
        <div>{{ delivery }}</div>
      </div>

      <div v-if="payment" class="order-card__detail">
        <div class="order-card__label">{{ t('title.payment') }}</div>
        <div>{{ payment }}</div>
      </div>

      <div v-if="order.comment" class="order-card__detail">
        <div class="order-card__label">{{ t('form.comment') }}</div>
        <div>{{ order.comment }}</div>
      </div>
    </div>

    <footer class="order-card__footer">
      <span>{{ productCount }} {{ t('messages.products_total') }}</span>
      <span v-if="order.storefrontCode || order.storefront_code" class="order-card__storefront">
        {{ order.storefrontCode || order.storefront_code }}
      </span>
    </footer>
  </article>
</template>

<style scoped lang="scss">
.order-card {
  display: grid;
  gap: 18px;
  padding: 24px;
  border-radius: 28px;
  background: rgba(255, 250, 244, 0.96);
  border: 1px solid rgba(74, 91, 68, 0.12);
  box-shadow: 0 18px 48px rgba(39, 49, 36, 0.06);
}

.order-card__header,
.order-card__footer {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.order-card__code {
  color: #1f2b1d;
  font-size: 20px;
  font-weight: 700;
}

.order-card__date,
.order-card__footer {
  color: #667160;
  font-size: 14px;
}

.order-card__summary {
  display: grid;
  gap: 8px;
  justify-items: end;
  text-align: right;
}

.order-card__status {
  display: inline-flex;
  align-items: center;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(53, 82, 74, 0.08);
  color: #35524a;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
}

.order-card__status.is-new {
  color: #8a5a2b;
  background: rgba(138, 90, 43, 0.12);
}

.order-card__status.is-completed {
  color: #2f6a43;
  background: rgba(47, 106, 67, 0.12);
}

.order-card__status.is-canceled,
.order-card__status.is-failed {
  color: #9a4b4b;
  background: rgba(154, 75, 75, 0.12);
}

.order-card__products {
  display: grid;
  gap: 12px;
}

.order-card__product {
  display: grid;
  grid-template-columns: 56px minmax(0, 1fr);
  gap: 14px;
  align-items: center;
  text-decoration: none;
  color: inherit;
}

.order-card__product-image {
  width: 56px;
  height: 72px;
  border-radius: 16px;
  background: rgba(53, 82, 74, 0.08);
  object-fit: contain;
}

.order-card__product-content {
  display: grid;
  gap: 4px;
}

.order-card__product-name {
  color: #1f2b1d;
  font-size: 15px;
  font-weight: 600;
}

.order-card__product-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  color: #667160;
  font-size: 13px;
}

.order-card__details {
  display: grid;
  gap: 12px;
}

.order-card__detail {
  display: grid;
  gap: 4px;
  color: #435141;
  font-size: 14px;
  line-height: 1.5;
}

.order-card__label {
  color: #8a5a2b;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.order-card__storefront {
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
</style>
