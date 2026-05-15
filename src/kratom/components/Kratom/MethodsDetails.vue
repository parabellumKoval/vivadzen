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

const normalizeMeta = (method: Record<string, any>) => {
  if (method?.isMetaPriceObject && method?.meta?.amount !== undefined) {
    return {
      kind: 'price' as const,
      amount: Number(method.meta.amount),
      currency: String(method.meta.currency || ''),
    }
  }

  if (method?.meta) {
    return {
      kind: 'text' as const,
      text: String(method.meta),
    }
  }

  return null
}

const pageIntro = computed(() => {
  if (props.section !== 'delivery') {
    return null
  }

  const title = content?.page_intro_title
  const text = content?.page_intro_text

  if (!title && !text) {
    return null
  }

  return {
    title: title || '',
    text: text || '',
  }
})

const messengerSections = computed(() => {
  if (props.section !== 'delivery') {
    return []
  }

  return [
    {
      title: content?.messenger_age_title || '',
      text: content?.messenger_age_text || '',
    },
    {
      title: content?.messenger_delivery_title || '',
      text: content?.messenger_delivery_text || '',
    }
  ].filter((section) => section.title || section.text)
})

const items = computed(() => {
  if (props.section === 'delivery') {
    return deliveryMethods.value.map((method) => ({
      key: method.key,
      title: method.title || method.label || t(`delivery.${method.key}`),
      image: method.image || method.logo,
      description: deliveryDescription(method.key),
      meta: normalizeMeta(method),
      sections: method.key === 'messenger_address' ? messengerSections.value : [],
    }))
  }

  return paymentMethods.value.map((method) => ({
    key: method.key,
    title: method.title || method.label || t(`payments.${method.key}.title`),
    image: method.image || method.logo,
    description: content?.[method.key] || '',
    meta: normalizeMeta(method),
    sections: [],
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

    <div v-if="pageIntro" class="kratom-methods-details__intro">
      <h3 v-if="pageIntro.title" class="kratom-methods-details__intro-title">{{ pageIntro.title }}</h3>
      <p v-if="pageIntro.text" class="kratom-methods-details__intro-text">{{ pageIntro.text }}</p>
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
          <div class="kratom-methods-details__card-copy">
            <h3 class="kratom-methods-details__card-title">{{ item.title }}</h3>
            <div v-if="item.meta" class="kratom-methods-details__meta">
              <simple-price
                v-if="item.meta.kind === 'price'"
                :value="item.meta.amount"
                :currency-code="item.meta.currency"
                class="kratom-methods-details__meta-price"
              />
              <template v-else>
                {{ item.meta.text }}
              </template>
            </div>
          </div>
        </div>
        <p
          v-if="item.description"
          class="kratom-methods-details__description"
          v-html="item.description"
        />
        <div v-if="item.sections?.length" class="kratom-methods-details__sections">
          <div
            v-for="(section, index) in item.sections"
            :key="`${item.key}-${index}`"
            class="kratom-methods-details__section"
          >
            <div v-if="section.title" class="kratom-methods-details__section-title">{{ section.title }}</div>
            <p v-if="section.text" class="kratom-methods-details__section-text">{{ section.text }}</p>
          </div>
        </div>
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

.kratom-methods-details__intro {
  display: grid;
  gap: 10px;
  padding: 24px 26px;
  border-radius: 24px;
  border: 1px solid rgba(74, 91, 68, 0.1);
  background: rgba(255, 252, 247, 0.96);
}

.kratom-methods-details__intro-title {
  margin: 0;
  color: #21321f;
  font-size: 22px;
  line-height: 1.3;
}

.kratom-methods-details__intro-text {
  margin: 0;
  color: #4d5848;
  font-size: 15px;
  line-height: 1.7;
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

.kratom-methods-details__card-copy {
  display: grid;
  gap: 6px;
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

.kratom-methods-details__meta {
  color: #7e8679;
  font-size: 13px;
  line-height: 1.5;
}

:deep(.kratom-methods-details__meta-price .value) {
  font-size: 13px;
  font-weight: 700;
  color: #70796a;
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

.kratom-methods-details__sections {
  display: grid;
  gap: 14px;
}

.kratom-methods-details__section-title {
  font-weight: 700;
  color: #21321f;
  margin-bottom: 6px;
}

.kratom-methods-details__section-text {
  margin: 0;
  color: #465142;
  font-size: 15px;
  line-height: 1.75;
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
