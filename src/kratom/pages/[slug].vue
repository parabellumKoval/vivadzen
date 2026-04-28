<script setup lang="ts">
import ProductDeliveryInfo from '~/components/Product/Delivery/Info/Info.vue'
import { normalizeKratomProductImages } from '~/utils/productImage'
import { formatKratomAttributeValue, useKratomBadges } from '~/utils/kratomBadges'

const route = useRoute()
const { t, tm, rt, locale } = useI18n()
const regionPath = useToLocalePath()
const cartStore = useCartStore()
const runtimeConfig = useRuntimeConfig()
const { openCertificatesModal } = useDocumentsModal()
const quantity = ref(1)
const galleryIndex = ref(0)
const adultoGuideModal = defineAsyncComponent(() => import('~/components/Modal/AdultoGuide/AdultoGuide.vue'))
const categorySlug = runtimeConfig.public.kratomStore?.categorySlug || 'kratom'
const catalogPerPage = 48
const mainStoreUrl = 'https://vivadzen.com'

const slug = computed(() => String(route.params.slug || ''))

const {
  data: product,
  error,
} = await useAsyncData(
  () => `kratom-product-${slug.value}-${locale.value}`,
  async () => {
    const response = await useProductStore().show(slug.value)
    const payload = response.data.value as Record<string, any> | null | undefined
    const normalized = payload && typeof payload === 'object' && payload.data && typeof payload.data === 'object'
      ? payload.data
      : payload

    if (response.error.value || !normalized || typeof normalized !== 'object' || !normalized.slug) {
      throw createError({ statusCode: 404, statusMessage: t('error.product_not_found') })
    }

    return normalized
  },
  { server: true },
)

if (error.value) {
  throw createError({
    statusCode: error.value.statusCode || error.value.status || 404,
    statusMessage: error.value.statusMessage || error.value.message || t('error.product_not_found'),
    fatal: true,
  })
}

const fetchCatalogPage = async (page: number) => {
  const response = await useProductStore().catalog({
    with_products: true,
    category_slug: categorySlug,
    per_page: catalogPerPage,
    page,
    cache: true,
  })

  if (response.error.value) {
    throw response.error.value
  }

  return response.data.value
}

const { data: catalogProductsData } = await useAsyncData(
  () => `kratom-product-catalog-${locale.value}`,
  async () => {
    const firstPage = await fetchCatalogPage(1)
    const firstPageProducts = firstPage?.products?.data || []
    const lastPage = Number(firstPage?.products?.meta?.last_page || 1)

    if (lastPage <= 1) {
      return firstPageProducts
    }

    const restPages = await Promise.all(
      Array.from({ length: lastPage - 1 }, (_, index) => fetchCatalogPage(index + 2)),
    )

    const allProducts = [
      ...firstPageProducts,
      ...restPages.flatMap((pageData) => pageData?.products?.data || []),
    ]

    const seen = new Set<string | number>()

    return allProducts.filter((catalogProduct: Record<string, any> | null) => {
      const key = catalogProduct?.id ?? catalogProduct?.slug
      if (!key || seen.has(key)) {
        return false
      }

      seen.add(key)
      return true
    })
  },
  {
    server: true,
    default: () => [],
  },
)

const images = computed(() => {
  return normalizeKratomProductImages(product.value?.images, product.value?.name || '')
})

const activeImage = computed(() => images.value[galleryIndex.value] || images.value[0])
const modifications = computed(() => Array.isArray(product.value?.modifications) ? product.value.modifications : [])
const attributes = computed(() => Array.isArray(product.value?.attrs) ? product.value.attrs : [])
const productBadges = computed(() => useKratomBadges(attributes.value).list)
const displayPrice = computed(() => {
  const basePrice = product.value?.old_price ?? product.value?.oldPrice ?? product.value?.basePrice
  return basePrice ?? product.value?.price
})

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.catalog'), item: '/catalog' },
  { name: product.value?.name || '', item: `/${slug.value}` },
])

const normalizeInlineHtml = (value: unknown) => {
  if (typeof value !== 'string') {
    return null
  }

  const normalized = value
    .replace(/\\r\\n|\\n|\\r/g, ' ')
    .replace(/\r\n|\r|\n/g, ' ')
    .replace(/&nbsp;/gi, ' ')
    .replace(/([\p{Extended_Pictographic}])([\p{L}\p{N}])/gu, '$1 $2')
    .replace(/([\p{Extended_Pictographic}])<(strong|b|span|em|i)\b/gu, '$1 <$2')
    .replace(/([\p{L}\p{N}])<(strong|b|span|em|i)\b/gu, '$1 <$2')
    .replace(/<\/(strong|b|span|em|i)>([\p{L}\p{N}])/gu, '</$1> $2')
    .replace(/[ \t]{2,}/g, ' ')
    .trim()

  return normalized || null
}

// const summaryHtml = computed(() => normalizeInlineHtml(product.value?.short_description || product.value?.excerpt))

// const descriptionHtml = computed(() => {
//   return product.value?.content || product.value?.description || null
// })

const translateText = (value: unknown) => {
  if (typeof value === 'string') {
    return value
  }

  return value ? rt(value as Parameters<typeof rt>[0]) : ''
}

const getTranslatedList = (key: string) => {
  const value = tm(key)

  return Array.isArray(value)
    ? value.map((item) => translateText(item))
    : []
}

const consumerNotice = computed(() => ({
  eyebrow: t('consumer_notice.eyebrow'),
  title: t('consumer_notice.title'),
  lead: t('consumer_notice.lead'),
  highlights: getTranslatedList('consumer_notice.highlights'),
  effectsTitle: t('consumer_notice.effects_title'),
  effects: getTranslatedList('consumer_notice.effects'),
  warningsTitle: t('consumer_notice.warnings_title'),
  warnings: getTranslatedList('consumer_notice.warnings'),
  usageTitle: t('consumer_notice.usage_title'),
  usage: getTranslatedList('consumer_notice.usage'),
}))

const formatAttributeValue = (attribute: Record<string, any> | null | undefined) => {
  return formatKratomAttributeValue(attribute)
}

const keyFacts = computed(() => {
  const rows = [] as Array<{ label: string; value: string }>
  rows.push({ label: t('label.brand'), value: 'Vivadzen' })
  if (product.value?.code) rows.push({ label: t('label.articul'), value: String(product.value.code) })
  if (product.value?.inStock !== undefined) rows.push({ label: t('label.status'), value: product.value.inStock > 0 ? t('label.available') : t('label.not_available') })
  return rows
})

const productAttributeRows = computed(() => {
  const factRows = keyFacts.value.map((fact) => ({
    key: `fact-${fact.label}`,
    label: fact.label,
    value: fact.value,
  }))

  const attributeRows = attributes.value.map((attribute) => ({
    key: `attribute-${attribute.id || attribute.name}`,
    label: attribute.name,
    value: formatAttributeValue(attribute),
  }))

  return [...factRows, ...attributeRows]
})

const deliveryHighlights = computed(() => {
  const isAvailable = product.value?.inStock === undefined || Number(product.value?.inStock) > 0

  return [
    {
      icon: isAvailable ? 'ph:check-circle-fill' : 'ph:clock-fill',
      text: isAvailable
        ? t('kratom.product.delivery_inline_available')
        : t('kratom.product.delivery_inline_unavailable'),
    },
    {
      icon: 'ph:truck-fill',
      text: t('kratom.product.delivery_inline_shipping'),
    },
  ]
})

const relatedProducts = computed(() => {
  const currentId = product.value?.id
  const currentSlug = product.value?.slug

  return (catalogProductsData.value || []).filter((catalogProduct: Record<string, any> | null) => {
    if (!catalogProduct) {
      return false
    }

    if (currentId && catalogProduct.id === currentId) {
      return false
    }

    if (currentSlug && catalogProduct.slug === currentSlug) {
      return false
    }

    return true
  })
})

const changeImage = (index: number) => {
  galleryIndex.value = index
}

watch(images, (nextImages) => {
  if (!nextImages.length) {
    galleryIndex.value = 0
    return
  }

  if (galleryIndex.value > nextImages.length - 1) {
    galleryIndex.value = 0
  }
}, { immediate: true })

const adjustQuantity = (delta: number) => {
  quantity.value = Math.max(1, quantity.value + delta)
}

const addToCart = async () => {
  await cartStore.add({ ...product.value, amount: quantity.value })
  useModal().open(resolveComponent('ModalCart'), null, null, {
    width: { min: 968, max: 968 },
  })
}

const openAdultoGuide = () => {
  useModal().open(adultoGuideModal, null, null, {
    width: { min: 320, max: 920 },
    height: { min: 520, max: 860 },
  })
}

useSeo().setPageSeo('product', {
  params: () => ({
    name: product.value?.name || '',
  }),
  title: () => product.value?.seo?.meta_title,
  description: () => product.value?.seo?.meta_description,
  image: () => activeImage.value?.src,
  ogType: 'product',
})
</script>

<template>
  <div class="page-base kratom-product-page">
    <div class="container kratom-page-container kratom-product-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />

      <section class="kratom-product-hero">
        <div class="kratom-product-hero__column kratom-product-hero__column--left">
          <div class="kratom-product-gallery">
            <div class="kratom-product-gallery__stage">
              <nuxt-img
                :src="activeImage.src"
                :alt="activeImage.alt || product?.name"
                :title="activeImage.title || product?.name"
                width="960"
                height="960"
                sizes="mobile:100vw tablet:80vw desktop:640px"
                fit="contain"
                format="webp"
                quality="75"
              />
            </div>

            <div v-if="images.length > 1" class="kratom-product-gallery__thumbs">
              <button
                v-for="(image, index) in images"
                :key="image.src || index"
                type="button"
                class="kratom-product-gallery__thumb"
                :class="{ 'is-active': galleryIndex === index }"
                @click="changeImage(index)"
              >
                <nuxt-img :src="image.src" :alt="image.alt || product?.name" width="120" height="120" fit="contain" />
              </button>
            </div>
          </div>

          <div class="kratom-product-adulto">
            <div class="kratom-product-adulto__inner">
              <p class="kratom-product-adulto__eyebrow">{{ t('kratom.product.adulto_eyebrow') }}</p>
              <h2 class="kratom-product-adulto__title">{{ t('kratom.product.adulto_title') }}</h2>
              <p class="kratom-product-adulto__text">{{ t('kratom.product.adulto_text') }}</p>
              <button type="button" class="kratom-product-adulto__action" @click="openAdultoGuide">
                {{ t('kratom.product.adulto_action') }}
              </button>
            </div>
          </div>

          <div class="kratom-product-legal">
            <div class="kratom-product-legal__copy">
              <p class="kratom-product-legal__eyebrow">{{ t('kratom.product.legal_eyebrow') }}</p>
              <h2 class="kratom-product-legal__title">{{ t('kratom.product.legal_title') }}</h2>
              <p class="kratom-product-legal__text">{{ t('kratom.product.legal_text') }}</p>
            </div>

            <div class="kratom-product-legal__media">
              <nuxt-img
                src="/images/landing/round-kratom.png"
                :alt="t('kratom.product.legal_image_alt')"
                width="220"
                height="220"
                sizes="mobile:140px desktop:220px"
                fit="contain"
                format="webp"
                quality="85"
              />
            </div>
          </div>
        </div>

        <div class="kratom-product-hero__column kratom-product-hero__column--right">
          <div class="kratom-product-summary">
            <h1 class="kratom-product-summary__title">{{ product?.name }}</h1>
            <!-- <div v-if="summaryHtml" class="rich-text kratom-product-summary__text" v-html="summaryHtml"></div> -->

            <div class="kratom-product-summary__badges">
              <div
                v-for="badge in productBadges"
                :key="`${badge.attribute?.id || badge.attribute?.slug}-${badge.key}`"
                class="kratom-product-summary__badge"
              >
                <nuxt-img
                  :src="badge.image"
                  :alt="badge.label"
                  :title="badge.label"
                  width="56"
                  height="56"
                  sizes="56px"
                  format="webp"
                  quality="80"
                />
                <div class="kratom-product-summary__badge-copy">
                  <span>{{ badge.attribute?.name }}</span>
                  <strong>{{ badge.label }}</strong>
                </div>
              </div>

              <div class="kratom-product-summary__badge kratom-product-summary__badge--brand">
                <nuxt-img
                  src="/images/logo/vivadzen.png"
                  alt="Vivadzen"
                  title="Vivadzen"
                  width="56"
                  height="56"
                  sizes="56px"
                  format="webp"
                  quality="90"
                />
                <div class="kratom-product-summary__badge-copy">
                  <span>{{ t('label.brand') }}</span>
                  <strong>Vivadzen</strong>
                </div>
              </div>
            </div>

            <div class="kratom-product-summary__delivery">
              <div
                v-for="(item, index) in deliveryHighlights"
                :key="index"
                class="kratom-product-summary__delivery-item"
              >
                <IconCSS :name="item.icon" class="kratom-product-summary__delivery-icon" />
                <span class="kratom-product-summary__delivery-text">{{ item.text }}</span>
              </div>
            </div>

            <div class="kratom-product-summary__commerce">
              <div v-if="modifications.length > 1" class="kratom-product-summary__mods">
                <p class="kratom-product-summary__mods-title">{{ t('kratom.product.choose_variation') }}</p>
                <div class="kratom-product-summary__mods-grid">
                  <NuxtLink
                    v-for="modification in modifications"
                    :key="modification.id"
                    :to="regionPath(`/${modification.slug}`)"
                    class="kratom-product-summary__mod"
                    :class="{ 'is-active': modification.slug === product?.slug }"
                  >
                    {{ modification.short_name || modification.name }}
                  </NuxtLink>
                </div>
              </div>

              <div class="kratom-product-summary__pricing">
                <simple-price :value="displayPrice" :currency-code="product?.currency" class="kratom-product-summary__price" />
              </div>
            </div>

            <div class="kratom-product-summary__buy-box">
              <div class="kratom-product-summary__qty">
                <button type="button" @click="adjustQuantity(-1)">-</button>
                <span>{{ quantity }}</span>
                <button type="button" @click="adjustQuantity(1)">+</button>
              </div>
              <button type="button" class="button primary kratom-product-summary__buy-btn" @click="addToCart">
                <span>{{ t('button.buy') }}</span>
                <IconCSS name="ci:shopping-cart-01" />
              </button>
            </div>

          </div>

          <ProductDeliveryInfo class="kratom-product-delivery-card" />

          <div class="kratom-product-age-warning" :aria-label="t('kratom.product.age_warning.aria_label')">
            <div class="kratom-product-age-warning__sign" role="img" aria-hidden="true">
              <svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                <circle cx="60" cy="60" r="53" fill="#fff" stroke="#e1251b" stroke-width="10" />
                <text x="60" y="78" text-anchor="middle" font-size="58" font-weight="700" fill="#111" font-family="Arial, sans-serif">18</text>
                <line x1="24" y1="24" x2="96" y2="96" stroke="#e1251b" stroke-width="12" stroke-linecap="round" />
              </svg>
            </div>

            <div class="kratom-product-age-warning__copy">
              <p>{{ t('kratom.product.age_warning.line_1') }}</p>
              <p>{{ t('kratom.product.age_warning.line_2') }}</p>
            </div>
          </div>
        </div>
      </section>

      <section class="kratom-product-stack">
        <article class="kratom-product-panel kratom-product-panel--consumer-notice">
          <div class="kratom-product-consumer-notice">
            <div class="kratom-product-consumer-notice__hero">
              <div class="kratom-product-consumer-notice__headline">
                <p class="kratom-product-panel__eyebrow">{{ consumerNotice.eyebrow }}</p>
                <h2 class="kratom-product-consumer-notice__title">{{ consumerNotice.title }}</h2>
              </div>
              <span class="kratom-product-consumer-notice__badge">18+</span>
            </div>

            <p class="kratom-product-consumer-notice__lead">{{ consumerNotice.lead }}</p>

            <div class="kratom-product-consumer-notice__top">
              <div class="kratom-product-consumer-notice__highlights">
                <div
                  v-for="(highlight, index) in consumerNotice.highlights"
                  :key="`notice-highlight-${index}`"
                  class="kratom-product-consumer-notice__highlight"
                >
                  <IconCSS name="ph:warning-diamond-fill" class="kratom-product-consumer-notice__highlight-icon" />
                  <p>{{ highlight }}</p>
                </div>
              </div>

              <section
                v-if="productAttributeRows.length"
                class="kratom-product-consumer-notice__details"
                aria-labelledby="kratom-product-details-title"
              >
                <p id="kratom-product-details-title" class="kratom-product-panel__eyebrow">{{ t('kratom.product.details') }}</p>
                <div class="kratom-product-attributes">
                  <div v-for="row in productAttributeRows" :key="row.key" class="kratom-product-attributes__row">
                    <span>{{ row.label }}</span>
                    <strong>{{ row.value }}</strong>
                  </div>
                </div>
              </section>
            </div>

            <div class="kratom-product-consumer-notice__grid">
              <section class="kratom-product-consumer-notice__section">
                <h3>{{ consumerNotice.effectsTitle }}</h3>
                <div class="kratom-product-consumer-notice__copy">
                  <p v-for="(paragraph, index) in consumerNotice.effects" :key="`effect-${index}`">
                    {{ paragraph }}
                  </p>
                </div>
              </section>

              <section class="kratom-product-consumer-notice__section">
                <h3>{{ consumerNotice.warningsTitle }}</h3>
                <ul class="kratom-product-consumer-notice__list">
                  <li v-for="(warning, index) in consumerNotice.warnings" :key="`warning-${index}`">
                    <IconCSS name="ph:warning-circle-fill" class="kratom-product-consumer-notice__list-icon" />
                    <span>{{ warning }}</span>
                  </li>
                </ul>
              </section>

              <section class="kratom-product-consumer-notice__section kratom-product-consumer-notice__section--full">
                <h3>{{ consumerNotice.usageTitle }}</h3>
                <ol class="kratom-product-consumer-notice__steps">
                  <li v-for="(step, index) in consumerNotice.usage" :key="`usage-${index}`">
                    {{ step }}
                  </li>
                </ol>
              </section>
            </div>
          </div>
        </article>

        <article class="kratom-product-safety" aria-labelledby="kratom-product-safety-title">
          <div class="kratom-product-safety__circle">
            <nuxt-img
              src="/images/landing/progress/thing-2.png"
              :alt="t('kratom.product.safety.title')"
              width="320"
              height="320"
              sizes="mobile:220px desktop:260px"
              fit="contain"
              format="webp"
              quality="85"
              class="kratom-product-safety__image"
            />
          </div>

          <div class="kratom-product-safety__content">
            <h2 id="kratom-product-safety-title" class="kratom-product-safety__title">
              {{ t('kratom.product.safety.title') }}
            </h2>

            <p class="kratom-product-safety__description">
              {{ t('kratom.product.safety.description_prefix') }}
              <span class="kratom-product-safety__highlight">
                {{ t('kratom.product.safety.description_highlight') }}
              </span>
              {{ t('kratom.product.safety.description_suffix') }}
            </p>

            <button class="kratom-product-safety__button" type="button" @click="openCertificatesModal">
              {{ t('kratom.product.safety.button') }}
            </button>
          </div>
        </article>

        <!-- <article v-if="descriptionHtml || product?.content_slices?.length" class="kratom-product-panel">
          <p class="kratom-product-panel__eyebrow">{{ t('kratom.product.overview') }}</p>
          <div v-if="product?.content_slices?.length" class="rich-text kratom-product-panel__content">
            <slice-area :slices="product.content_slices" />
          </div>
          <div v-else class="rich-text kratom-product-panel__content" v-html="descriptionHtml"></div>
        </article> -->

        <article class="kratom-product-panel kratom-product-panel--notice">
          <p class="kratom-product-panel__eyebrow">{{ t('kratom.product.checkout_note') }}</p>
          <i18n-t keypath="kratom.product.checkout_note_text" tag="p" scope="global">
            <template #link>
              <a
                :href="mainStoreUrl"
                class="kratom-product-panel__link"
                target="_blank"
                rel="noopener noreferrer"
              >
                {{ t('kratom.product.checkout_note_link') }}
              </a>
            </template>
          </i18n-t>
        </article>
      </section>
    </div>

    <div v-if="relatedProducts.length" class="container kratom-page-container kratom-product-related-shell">
      <section class="kratom-product-related">
        <div class="kratom-product-related__intro">
          <p class="kratom-product-related__eyebrow">{{ t('title.catalog') }}</p>
          <h2 class="kratom-product-related__title">{{ t('kratom.product.related_title') }}</h2>
        </div>

        <div class="kratom-product-related__grid">
          <KratomProductCard v-for="relatedProduct in relatedProducts" :key="relatedProduct.id" :product="relatedProduct" />
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped lang="scss">
@use '@/assets/scss/rich-text' as *;

.kratom-product-shell {
  padding-top: 24px;
  padding-bottom: 48px;
}

.kratom-product-hero {
  display: flex;
  flex-direction: column;
  gap: 15px;

  @include desktop {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr);
    align-items: start;
    gap: 32px;
  }
}

.kratom-product-hero__column {
  display: contents;

  @include desktop {
    display: flex;
    flex-direction: column;
    gap: 32px;
    min-width: 0;
  }
}

.kratom-product-gallery,
.kratom-product-adulto,
.kratom-product-legal,
.kratom-product-summary,
.kratom-product-age-warning,
.kratom-product-safety,
.kratom-product-panel {
  border-radius: 40px;
  border: 1px solid rgba(74, 91, 68, 0.08);
  // background: rgba(255, 250, 244, 0.96);
  background: #fff;
  box-shadow: 0 32px 80px rgba(39, 49, 36, 0.05);
}

.kratom-product-gallery {
  order: 1;
  padding: 32px;
  display: flex;
  flex-direction: column;
}

.kratom-product-gallery__stage {
  height: 520px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 32px;
  overflow: hidden;
  background:
    radial-gradient(circle at top left, rgba(142, 177, 129, 0.15), transparent 45%),
    linear-gradient(180deg, rgba(0, 0, 0, 0.01), rgba(0, 0, 0, 0.05));
}

.kratom-product-gallery__stage :deep(img) {
  filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.15));
  width: 120%;
  height: 120%;
  max-width: initial;
  object-fit: contain;
}

.kratom-product-gallery__thumbs {
  margin-top: 16px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(88px, 1fr));
  gap: 10px;
}

.kratom-product-gallery__thumb {
  border: 1px solid rgba(74, 91, 68, 0.12);
  border-radius: 24px;
  background: #fffdf8;
  cursor: pointer;
  overflow: hidden;

  &.is-active {
    border-color: #35524a;
  }

  img {
    width: 100%;
    height: 100%;
    max-width: initial;
    object-fit: cover;
  }
}

.kratom-product-summary {
  order: 2;
  padding: 40px;
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.kratom-product-delivery-card {
  order: 3;
}

.kratom-product-safety {
  position: relative;
  overflow: hidden;
  padding: 40px;
  display: grid;
  gap: 28px;
  background:
    radial-gradient(circle at top right, rgba(255, 255, 255, 0.16), transparent 34%),
    radial-gradient(circle at bottom left, rgba(247, 170, 61, 0.14), transparent 28%),
    linear-gradient(145deg, #1a4d41, #12382f);
  color: #f7f1e8;

  @include tablet {
    grid-template-columns: minmax(220px, 280px) minmax(0, 1fr);
    align-items: center;
  }
}

.kratom-product-safety__circle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: min(100%, 240px);
  aspect-ratio: 1;
  margin: 0 auto;
  border-radius: 15%;
}

.kratom-product-safety__image {
  width: calc(100% - 12px);
  height: calc(100% - 12px);
  object-fit: contain;
}

.kratom-product-safety__content {
  display: grid;
  gap: 16px;
  align-items: start;
}

.kratom-product-safety__title {
  margin: 0;
  font-size: clamp(28px, 3vw, 36px);
  line-height: 1.15;
  color: #fffaf3;
  font-family: var(--font-display);
}

.kratom-product-safety__description {
  margin: 0;
  max-width: 60ch;
  color: rgba(247, 241, 232, 0.82);
  line-height: 1.75;
}

.kratom-product-safety__highlight {
  color: #ffd79d;
  font-weight: 700;
}

.kratom-product-safety__button {
  justify-self: start;
  margin-top: auto;
  padding: 0;
  border: 0;
  background: transparent;
  color: #ffd79d;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.2;
  text-decoration: underline;
  text-underline-offset: 4px;
  cursor: pointer;
  transition: color 0.2s ease, transform 0.2s ease;

  &:hover {
    color: #fff4de;
    transform: translateY(-1px);
  }
}

.kratom-product-adulto {
  order: 4;
  position: relative;
  overflow: hidden;
  padding: 40px;
  background:
    radial-gradient(circle at top right, rgba(255, 255, 255, 0.16), transparent 34%),
    radial-gradient(circle at bottom left, rgba(247, 170, 61, 0.14), transparent 28%),
    linear-gradient(145deg, #1a4d41, #12382f);
  color: #f7f1e8;
  min-height: 220px;

  &::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='96' height='96' viewBox='0 0 96 96' xmlns='http://www.w3.org/2000/svg'%3E%3Crect width='96' height='96' fill='none'/%3E%3Cg fill='%23ffffff' fill-opacity='0.12' font-family='Arial,sans-serif' font-size='20' font-weight='700'%3E%3Ctext x='10' y='30'%3E18%2B%3C/text%3E%3Ctext x='46' y='74'%3E18%2B%3C/text%3E%3C/g%3E%3C/svg%3E");
    background-size: 96px 96px;
    opacity: 0.95;
    pointer-events: none;
  }
}

.kratom-product-adulto__inner {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 14px;
}


.kratom-product-adulto__eyebrow {
  margin: 0;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.kratom-product-adulto__title {
  margin: 0;
  font-size: clamp(24px, 3vw, 32px);
  line-height: 1.08;
  font-family: var(--font-display);
  color: #fffaf3;
}

.kratom-product-adulto__text {
  margin: 0;
  max-width: 52ch;
  line-height: 1.7;
  color: rgba(247, 241, 232, 0.82);
}

.kratom-product-adulto__action {
  margin-top: auto;
  padding: 0;
  border: 0;
  background: transparent;
  color: #ffd79d;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.2;
  text-decoration: underline;
  text-underline-offset: 4px;
  cursor: pointer;
  transition: color 0.2s ease, transform 0.2s ease;

  &:hover {
    color: #fff4de;
    transform: translateY(-1px);
  }
}

.kratom-product-legal {
  order: 5;
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 24px;
  align-items: center;
  padding: 32px 36px;
  background:
    radial-gradient(circle at top left, rgba(242, 141, 26, 0.14), transparent 36%),
    linear-gradient(180deg, #fffaf1, #f7f0e3);
}

.kratom-product-legal__copy {
  display: flex;
  flex-direction: column;
  gap: 12px;
  order: 2;

  @include desktop {
    order: 1;
  }
}

.kratom-product-legal__eyebrow {
  margin: 0;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.kratom-product-legal__title {
  margin: 0;
  max-width: 20ch;
  font-size: clamp(24px, 2.4vw, 30px);
  line-height: 1.12;
  font-family: var(--font-display);
  color: #1f2b1d;
}

.kratom-product-legal__text {
  margin: 0;
  max-width: 58ch;
  line-height: 1.75;
  color: #55614f;
}

.kratom-product-legal__media {
  display: flex;
  align-items: center;
  justify-content: center;
  order: 1;

  @include desktop {
    order: 2;
  }

  img {
    display: block;
    width: clamp(140px, 16vw, 220px);
    height: auto;
  }
}

.kratom-product-adulto__eyebrow,
.kratom-product-legal__eyebrow,
.kratom-product-summary__eyebrow,
.kratom-product-panel__eyebrow,
.kratom-product-related__eyebrow {
  color: #f28d1a;
}

.kratom-product-summary__title {
  font-size: clamp(42px, 5vw, 64px);
  line-height: 1.05;
  color: #162014;
  font-family: var(--font-display);
}

.kratom-product-summary__text,
.kratom-product-panel__content,
.kratom-product-panel p {
  color: #5c6559;
  line-height: 1.75;
}

.kratom-product-panel p.kratom-product-panel__eyebrow {
  margin: 0;
  color: #f28d1a;
  font-size: 12px;
  font-weight: 800;
  line-height: normal;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.kratom-product-summary__text {
  text-align: left !important;
}

.kratom-product-summary__text :deep(*) {
  text-align: left !important;
  white-space: normal;
}

.kratom-product-summary__text :deep(p),
.kratom-product-summary__text :deep(ul),
.kratom-product-summary__text :deep(ol) {
  margin: 0 !important;
}

.kratom-product-summary__text :deep(*:first-child) {
  margin-top: 0;
}

.kratom-product-summary__text :deep(*:last-child) {
  margin-bottom: 0;
}

.kratom-product-summary__badges {
  display: grid;
  gap: 12px;
}

.kratom-product-summary__badge {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 14px;
  border-radius: 22px;
  background: rgba(247, 239, 231, 0.86);
  border: 1px solid rgba(74, 91, 68, 0.1);

  :deep(img) {
    border-radius: 16px;
    flex: 0 0 auto;
  }
}

.kratom-product-summary__badge--brand {
  :deep(img) {
    background: #fffdf8;
    object-fit: contain;
    padding: 6px;
  }
}

.kratom-product-summary__badge-copy {
  display: grid;
  gap: 3px;

  span {
    color: #6b7266;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.12em;
  }

  strong {
    color: #203019;
    font-size: 16px;
    line-height: 1.3;
  }
}

.kratom-product-summary__commerce {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  // justify-content: space-between;
  gap: 20px 24px;
}

.kratom-product-summary__pricing {
  display: flex;
  align-items: end;
  justify-content: flex-end;
  gap: 12px;
  flex: 0 0 auto;
}

:deep(.kratom-product-summary__price .value) {
  font-size: 40px;
  font-weight: 800;
  line-height: 100%;
  color: $color-orange;
}

.kratom-product-summary__delivery {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: flex-start;
  gap: 10px;
}

.kratom-product-summary__delivery-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  border-radius: 999px;
  // background: $color-green;
  background: rgba($color-green, 0.16);
  min-height: 40px;
}

.kratom-product-summary__delivery-icon {
  flex: 0 0 auto;
  font-size: 18px;
  color: $color-green;
  // color: rgba(0,0,0,0.6);
}

.kratom-product-summary__delivery-text {
  // color: rgba(0,0,0,0.6);
  color: $color-green;
  font-size: 16px;
  // font-weight: 700;
  line-height: 1.35;
}

.kratom-product-summary__mods {
  // flex: 1 1 380px;
}

.kratom-product-summary__mods-title {
  margin-bottom: 12px;
  font-weight: 700;
  color: #293626;
}

.kratom-product-summary__mods-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.kratom-product-summary__mod {
  padding: 11px 16px;
  border-radius: 999px;
  border: 1px solid rgba(74, 91, 68, 0.14);
  text-decoration: none;
  color: #324030;

  &.is-active {
    background: #20301d;
    color: #fff7ec;
    border-color: #20301d;
  }
}

.kratom-product-summary__buy-box {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: center;
  margin-top: -15px;
}

.kratom-product-age-warning {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 16px;
  align-items: center;
  order: 4;
  padding: 32px 36px;
  border-radius: 40px;
  border: 1px solid rgba(225, 37, 27, 0.16);
  box-shadow: 0 32px 80px rgba(39, 49, 36, 0.05);
  background: #fff;
}

.kratom-product-age-warning__sign {
  width: 88px;
  height: 88px;
  flex: 0 0 88px;

  svg {
    display: block;
    width: 100%;
    height: 100%;
  }
}

.kratom-product-age-warning__copy {
  display: grid;
  gap: 6px;

  p {
    margin: 0;
    color: #3a231e;
    font-size: 15px;
    font-weight: 400;
    line-height: 1.45;
  }

  p:first-child {
    font-weight: 700;
  }
}

.kratom-product-summary__qty {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  overflow: hidden;
  border: 1px solid rgba(74, 91, 68, 0.15);
  font-size: 18px;

  button,
  span {
    width: 44px;
    height: 56px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fffdf8;
    border: 0;
    cursor: pointer;

    @include desktop {
      width: 56px;
    }
  }

  span {
    width: 36px;
    font-weight: 800;
    cursor: default;

    @include desktop {
      width: 64px;
    }
  }
}

.kratom-product-summary__buy-btn {
  flex: 1;
  min-height: 56px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  background: linear-gradient(135deg, #f28d1a, #e67d0e);
  color: white;
  border: none;
  font-size: 18px;
  font-weight: 700;
  transition: transform 0.2s ease, box-shadow 0.2s ease;

  @include mobile {
    padding: 0 20px;
  }
}

.kratom-product-summary__buy-btn:hover {
  background: linear-gradient(135deg, #e67d0e, #d56d05);
  transform: translateY(-3px);
  box-shadow: 0 16px 32px rgba(229, 125, 14, 0.3);
}

.kratom-product-stack {
  margin-top: 40px;
  display: grid;
  gap: 32px;
}

.kratom-product-related {
  margin-top: 40px;
}

.kratom-product-related-shell {
  padding-bottom: 48px;
  max-width: 1800px;
}

.kratom-product-related__intro {
  margin-bottom: 24px;
  display: grid;
  gap: 12px;
}

.kratom-product-related__eyebrow {
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  font-weight: 800;
}

.kratom-product-related__title {
  font-size: clamp(32px, 4vw, 48px);
  line-height: 1.08;
  color: #162014;
  font-family: var(--font-display);
}

.kratom-product-related__grid {
  display: grid;
  gap: 20px;

  @include tablet {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  @include desktop {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }


  @include xld {
    grid-template-columns: repeat(5, minmax(0, 1fr));
  }
}

.kratom-product-panel {
  padding: 40px;
}

.kratom-product-panel--notice {
  background: linear-gradient(135deg, rgba(53, 82, 74, 0.08), rgba(138, 90, 43, 0.08));
}

.kratom-product-panel--consumer-notice {
  background:
    radial-gradient(circle at top right, rgba(242, 141, 26, 0.14), transparent 28%),
    radial-gradient(circle at bottom left, rgba(53, 82, 74, 0.1), transparent 30%),
    linear-gradient(180deg, #fffaf2, #fffdf9);
}

.kratom-product-panel__link {
  color: #8a5a2b;
  font-weight: 700;
  text-decoration: underline;
  text-decoration-thickness: 0.08em;
  text-underline-offset: 0.14em;
}

.kratom-product-panel__link:hover {
  color: #6f461f;
}

.kratom-product-attributes {
  display: grid;
  gap: 12px;
}

.kratom-product-attributes__row {
  padding: 14px 0;
  display: flex;
  justify-content: space-between;
  gap: 16px;
  border-bottom: 1px solid rgba(74, 91, 68, 0.08);

  span {
    color: #6b7266;
  }

  strong {
    color: #1f2b1d;
    text-align: right;
  }
}

.kratom-product-consumer-notice {
  display: grid;
  gap: 28px;
}

.kratom-product-consumer-notice__top {
  display: grid;
  gap: 24px;

  @include desktop {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    align-items: start;
    gap: 32px;
  }
}

.kratom-product-consumer-notice__hero {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
}

.kratom-product-consumer-notice__headline {
  display: grid;
  gap: 12px;
}

.kratom-product-consumer-notice__title {
  margin: 0;
  max-width: 18ch;
  font-size: clamp(30px, 4vw, 44px);
  line-height: 1.08;
  color: #1f2b1d;
  font-family: var(--font-display);
}

.kratom-product-consumer-notice__badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 64px;
  height: 38px;
  padding: 0 16px;
  border-radius: 999px;
  background: linear-gradient(135deg, #1a4d41, #12382f);
  color: #fff8ee;
  font-size: 14px;
  font-weight: 800;
  letter-spacing: 0.08em;
  box-shadow: 0 12px 28px rgba(18, 56, 47, 0.18);
}

.kratom-product-consumer-notice__lead {
  margin: 0;
  max-width: 72ch;
  font-size: 17px;
  line-height: 1.8;
  color: #485346;
}

.kratom-product-consumer-notice__highlights {
  display: grid;
  gap: 14px;

  @include tablet {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.kratom-product-consumer-notice__top > .kratom-product-consumer-notice__highlights {
  grid-template-columns: minmax(0, 1fr);

  @include mobile {
    order: 2;
  }
}

.kratom-product-consumer-notice__highlight {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 18px 20px;
  border-radius: 24px;
  border: 1px solid rgba(242, 141, 26, 0.16);
  background: rgba(255, 247, 236, 0.92);

  p {
    margin: 0;
    color: #4f5242;
    line-height: 1.65;
  }
}

.kratom-product-consumer-notice__highlight-icon {
  flex: 0 0 auto;
  margin-top: 2px;
  font-size: 18px;
  color: #d87a0e;
}

.kratom-product-consumer-notice__details {
  min-width: 0;
  padding: 24px 26px;
  border-radius: 28px;
  border: 1px solid rgba(74, 91, 68, 0.08);
  background: rgba(255, 255, 255, 0.72);

  @include mobile {
    order: 1;
  }
}

.kratom-product-consumer-notice__grid {
  display: grid;
  gap: 18px;

  @include desktop {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.kratom-product-consumer-notice__section {
  padding: 24px 26px;
  border-radius: 28px;
  border: 1px solid rgba(74, 91, 68, 0.08);
  background: rgba(255, 255, 255, 0.72);

  h3 {
    margin: 0 0 14px;
    font-size: 18px;
    line-height: 1.3;
    color: #22311d;
  }

  p {
    margin: 0;
    color: #596254;
    line-height: 1.75;
  }
}

.kratom-product-consumer-notice__section--full {
  @include desktop {
    grid-column: 1 / -1;
  }
}

.kratom-product-consumer-notice__copy {
  display: grid;
  gap: 12px;
}

.kratom-product-consumer-notice__list,
.kratom-product-consumer-notice__steps {
  margin: 0;
  color: #596254;
}

.kratom-product-consumer-notice__list {
  padding: 0;
  list-style: none;
  display: grid;
  gap: 12px;

  li {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 10px;
    align-items: start;
    line-height: 1.7;
  }
}

.kratom-product-consumer-notice__list-icon {
  margin-top: 0.22em;
  font-size: 16px;
  color: #8f4f12;
  flex-shrink: 0;
}

.kratom-product-consumer-notice__steps {
  padding: 0;
  list-style: none;
  display: grid;
  gap: 12px;
  counter-reset: notice-steps;

  li {
    counter-increment: notice-steps;
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 12px;
    align-items: start;
    line-height: 1.7;

    &::before {
      content: counter(notice-steps, decimal-leading-zero);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 32px;
      height: 32px;
      padding: 0 8px;
      border-radius: 999px;
      background: rgba(31, 47, 28, 0.08);
      color: #1f2f1c;
      font-size: 12px;
      font-weight: 800;
      line-height: 1;
    }
  }
}

@include mobile {
  .kratom-product-shell {
    display: flex;
    flex-direction: column;
    gap: 15px;
    padding-top: 24px;
    padding-bottom: 40px;
  }

  .kratom-product-hero,
  .kratom-product-stack {
    display: contents;
  }

  .kratom-product-safety,
  .kratom-product-panel {
    border-radius: 28px;
  }

  .kratom-product-gallery {
    border-top-left-radius: 28px;
    border-top-right-radius: 28px;
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
  }

  .kratom-product-summary {
    margin-top: -15px;
    position: relative;
    z-index: 1;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    border-bottom-left-radius: 28px;
    border-bottom-right-radius: 28px;
  }

  .kratom-product-gallery,
  .kratom-product-summary,
  .kratom-product-panel,
  .kratom-product-adulto,
  .kratom-product-legal,
  .kratom-product-age-warning,
  .kratom-product-delivery-card,
  .kratom-product-safety {
    padding-left: 20px;
    padding-right: 20px;
  }

  .kratom-product-gallery {
    margin-top: -15px;
    padding: 0;
  }

  .kratom-product-summary,
  .kratom-product-panel,
  .kratom-product-adulto,
  .kratom-product-legal,
  .kratom-product-age-warning,
  .kratom-product-delivery-card,
  .kratom-product-safety {
    padding-top: 24px;
    padding-bottom: 24px;
  }

  .kratom-product-gallery__stage {
    height: 360px;
    border-radius: 24px 24px 0 0;
  }

  .kratom-product-summary__title {
    font-size: clamp(34px, 10vw, 46px);
  }

  .kratom-product-summary__pricing {
    justify-content: flex-start;
  }

  .kratom-product-gallery {
    order: 1;
  }

  .kratom-product-summary {
    order: 2;
  }

  .kratom-product-age-warning {
    order: 3;
  }

  .kratom-product-legal {
    grid-template-columns: 1fr;
    order: 4;
  }

  .kratom-product-panel--consumer-notice {
    order: 5;
  }

  .kratom-product-adulto {
    order: 6;
  }

  .kratom-product-delivery-card {
    order: 7;
  }

  .kratom-product-safety {
    order: 8;
  }

  .kratom-product-stack > .kratom-product-panel:not(.kratom-product-panel--consumer-notice) {
    order: 9;
  }

  .kratom-product-consumer-notice__hero {
    flex-direction: column;
  }

  .kratom-product-consumer-notice__title,
  .kratom-product-consumer-notice__lead {
    max-width: none;
  }

  .kratom-product-legal__title,
  .kratom-product-legal__text {
    max-width: none;
  }

  .kratom-product-legal__media img {
    width: 128px;
  }

  .kratom-product-safety {
    gap: 20px;
  }

  .kratom-product-safety__circle {
    width: min(100%, 220px);
  }
}
</style>
<i18n src="./lang.yaml" lang="yaml"></i18n>
