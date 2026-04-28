<script setup lang="ts">
type Section = 'delivery' | 'payment'

const props = defineProps<{
  section: Section
}>()

const { t, locale } = useI18n()
const { pickupLocations } = useContacts()
const { methods: deliveryMethods } = useDelivery()
const { methodsInfo: paymentMethods } = usePayment()

const content = await queryContent(props.section).locale(locale.value).findOne() as Record<string, string | undefined> | null

const escapeHtml = (value: unknown) => String(value || '')
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;')
  .replace(/'/g, '&#39;')

const deliveryDescription = (key: string) => {
  const description = content?.[key]
  if (typeof description !== 'string') return ''

  if (key === 'default_pickup' && pickupLocations.value.length) {
    const items = pickupLocations.value
      .map((location) => {
        const schedule = location.schedule ? ` (${escapeHtml(location.schedule)})` : ''

        return `<li><b>${escapeHtml(location.title || location.address)}</b>${location.title ? `: ${escapeHtml(location.address)}` : ''}${schedule}</li>`
      })
      .join('')

    return `${description} <ul>${items}</ul>`
  }

  return description
}

const items = computed(() => {
  if (props.section === 'delivery') {
    return deliveryMethods.value.map((method) => ({
      key: method.key,
      title: method.title || method.label || t(`delivery.${method.key}`),
      image: method.image || method.logo,
      description: deliveryDescription(method.key),
    }))
  }

  return paymentMethods.value.map((method) => ({
    key: method.key,
    title: method.title || method.label || t(`payments.${method.key}.title`),
    image: method.image || method.logo,
    description: content?.[method.key] || '',
  }))
})

const title = computed(() => (
  props.section === 'delivery'
    ? t('kratom.product.delivery_methods_title')
    : t('kratom.product.payment_methods_title')
))

const subtitle = computed(() => (
  props.section === 'delivery'
    ? t('delivery.subtitle')
    : t('payments.subtitle')
))

const note = computed(() => {
  if (props.section !== 'payment') {
    return ''
  }

  const value = content?.info
  return typeof value === 'string' ? value : ''
})
</script>

<template>
  <section class="kratom-methods-details">
    <div class="kratom-methods-details__header">
      <p class="kratom-methods-details__eyebrow">
        {{ t('kratom.product.shipping_payment') }}
      </p>
      <h2 class="kratom-methods-details__title">{{ title }}</h2>
      <p class="kratom-methods-details__subtitle">{{ subtitle }}</p>
    </div>

    <div class="kratom-methods-details__list">
      <article
        v-for="item in items"
        :key="item.key"
        class="kratom-methods-details__card"
      >
        <div class="kratom-methods-details__card-head">
          <div v-if="item.image" class="kratom-methods-details__logo-wrap">
            <img
              :src="item.image"
              :alt="item.title"
              class="kratom-methods-details__logo"
            >
          </div>
          <h3 class="kratom-methods-details__card-title">{{ item.title }}</h3>
        </div>
        <p
          v-if="item.description"
          class="kratom-methods-details__description"
          v-html="item.description"
        />
      </article>
    </div>

    <div v-if="note" class="kratom-methods-details__note">
      {{ note }}
    </div>
  </section>
</template>

<style scoped lang="scss">
.kratom-methods-details {
  display: grid;
  gap: 24px;
}

.kratom-methods-details__header {
  display: grid;
  gap: 10px;
}

.kratom-methods-details__eyebrow {
  margin: 0;
  color: #c58228;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 12px;
  font-weight: 800;
}

.kratom-methods-details__title {
  margin: 0;
  color: #1f3220;
  font-size: clamp(28px, 4vw, 42px);
  line-height: 1.02;
}

.kratom-methods-details__subtitle {
  margin: 0;
  max-width: 780px;
  color: #5f6859;
  font-size: 15px;
  line-height: 1.7;
}

.kratom-methods-details__list {
  display: grid;
  gap: 16px;
}

.kratom-methods-details__card {
  display: grid;
  gap: 16px;
  padding: 24px;
  border-radius: 28px;
  border: 1px solid rgba(74, 91, 68, 0.1);
  background:
    radial-gradient(circle at top right, rgba(255, 212, 124, 0.12), transparent 30%),
    linear-gradient(160deg, rgba(255, 252, 247, 0.98), rgba(248, 242, 233, 0.95));
}

.kratom-methods-details__card-head {
  display: flex;
  align-items: center;
  gap: 18px;
}

.kratom-methods-details__logo-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 128px;
  min-width: 128px;
  height: 64px;
  padding: 12px 14px;
  border-radius: 18px;
  border: 1px solid rgba(74, 91, 68, 0.08);
  background: rgba(255, 255, 255, 0.82);
}

.kratom-methods-details__logo {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.kratom-methods-details__card-title {
  margin: 0;
  color: #21321f;
  font-size: 20px;
  line-height: 1.25;
}

.kratom-methods-details__description {
  margin: 0;
  color: #465142;
  font-size: 15px;
  line-height: 1.75;

  :deep(ul) {
    margin: 12px 0 0;
    padding-left: 20px;
  }

  :deep(li) {
    margin-top: 8px;
  }
}

.kratom-methods-details__note {
  padding: 24px 26px;
  border-radius: 24px;
  background: #18382f;
  color: #fff4e7;
  font-size: 15px;
  line-height: 1.7;
}

@include mobile {
  .kratom-methods-details__card {
    padding: 20px;
    border-radius: 24px;
  }

  .kratom-methods-details__card-head {
    align-items: flex-start;
    flex-direction: column;
  }

  .kratom-methods-details__logo-wrap {
    width: 120px;
    min-width: 120px;
    height: 56px;
  }
}
</style>
