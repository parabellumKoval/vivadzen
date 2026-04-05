<script setup lang="ts">
import ProductDeliveryInfo from '~/components/Product/Delivery/Info/Info.vue'

const route = useRoute()
const { t, locale } = useI18n()
const regionPath = useToLocalePath()
const cartStore = useCartStore()
const runtimeConfig = useRuntimeConfig()
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
  const list = Array.isArray(product.value?.images) ? product.value.images : []
  if (list.length) {
    return list.filter((item: any) => item?.src)
  }
  return [{ src: useImg().noImage, alt: product.value?.name || '', title: product.value?.name || '' }]
})

const activeImage = computed(() => images.value[galleryIndex.value] || images.value[0])
const modifications = computed(() => Array.isArray(product.value?.modifications) ? product.value.modifications : [])
const attributes = computed(() => Array.isArray(product.value?.attrs) ? product.value.attrs : [])
const displayPrice = computed(() => {
  const basePrice = product.value?.old_price ?? product.value?.oldPrice ?? product.value?.basePrice
  return basePrice ?? product.value?.price
})

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.catalog'), item: '/catalog' },
  { name: product.value?.name || '', item: `/${slug.value}` },
])

const descriptionHtml = computed(() => {
  return product.value?.content || product.value?.description || null
})

const keyFacts = computed(() => {
  const rows = [] as Array<{ label: string; value: string }>
  if (product.value?.brand?.name) rows.push({ label: t('label.brand'), value: product.value.brand.name })
  if (product.value?.code) rows.push({ label: t('label.articul'), value: String(product.value.code) })
  if (product.value?.inStock !== undefined) rows.push({ label: t('label.status'), value: product.value.inStock > 0 ? t('label.available') : t('label.not_available') })
  return rows
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

useHead(() => ({
  title: product.value?.seo?.meta_title || product.value?.name,
  meta: [
    {
      name: 'description',
      content: product.value?.seo?.meta_description || product.value?.short_description || product.value?.name,
    },
  ],
}))
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
              <span class="kratom-product-adulto__badge">18+</span>
              <p class="kratom-product-adulto__eyebrow">{{ t('kratom.product.adulto_eyebrow') }}</p>
              <h2 class="kratom-product-adulto__title">{{ t('kratom.product.adulto_title') }}</h2>
              <p class="kratom-product-adulto__text">{{ t('kratom.product.adulto_text') }}</p>
              <button type="button" class="kratom-product-adulto__action" @click="openAdultoGuide">
                {{ t('kratom.product.adulto_action') }}
              </button>
            </div>
          </div>
        </div>

        <div class="kratom-product-hero__column kratom-product-hero__column--right">
          <div class="kratom-product-summary">
            <p class="kratom-product-summary__eyebrow">{{ t('kratom.product.eyebrow') }}</p>
            <h1 class="kratom-product-summary__title">{{ product?.name }}</h1>
            <p v-if="product?.short_description || product?.excerpt" class="kratom-product-summary__text">
              {{ product.short_description || product.excerpt }}
            </p>

            <div class="kratom-product-summary__pricing">
              <simple-price :value="displayPrice" :currency-code="product?.currency" class="kratom-product-summary__price" />
            </div>

            <div v-if="keyFacts.length" class="kratom-product-summary__facts">
              <div v-for="fact in keyFacts" :key="fact.label" class="kratom-product-summary__fact">
                <span>{{ fact.label }}</span>
                <strong>{{ fact.value }}</strong>
              </div>
            </div>

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

            <div class="kratom-product-summary__buy-box">
              <div class="kratom-product-summary__qty">
                <button type="button" @click="adjustQuantity(-1)">-</button>
                <span>{{ quantity }}</span>
                <button type="button" @click="adjustQuantity(1)">+</button>
              </div>
              <button type="button" class="button primary kratom-product-summary__buy-btn" @click="addToCart">
                <span>{{ t('button.buy') }}</span>
                <IconCSS name="ph:shopping-cart-fill" />
              </button>
            </div>
          </div>

          <ProductDeliveryInfo class="kratom-product-delivery-card" />
        </div>
      </section>

      <section v-if="descriptionHtml || product?.content_slices?.length || attributes.length" class="kratom-product-stack">
        <article v-if="descriptionHtml || product?.content_slices?.length" class="kratom-product-panel">
          <p class="kratom-product-panel__eyebrow">{{ t('kratom.product.overview') }}</p>
          <div v-if="product?.content_slices?.length" class="kratom-product-panel__content">
            <slice-area :slices="product.content_slices" />
          </div>
          <div v-else class="rich-text kratom-product-panel__content" v-html="descriptionHtml"></div>
        </article>

        <article v-if="attributes.length" class="kratom-product-panel">
          <p class="kratom-product-panel__eyebrow">{{ t('kratom.product.details') }}</p>
          <div class="kratom-product-attributes">
            <div v-for="attribute in attributes" :key="attribute.id || attribute.name" class="kratom-product-attributes__row">
              <span>{{ attribute.name }}</span>
              <strong>{{ attribute.value }}</strong>
            </div>
          </div>
        </article>

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
.kratom-product-shell {
  padding-top: 24px;
  padding-bottom: 48px;
}

.kratom-product-hero {
  display: flex;
  flex-direction: column;
  gap: 32px;

  @include desktop {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr);
    align-items: start;
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
.kratom-product-summary,
.kratom-product-panel {
  border-radius: 40px;
  border: 1px solid rgba(74, 91, 68, 0.08);
  background: rgba(255, 250, 244, 0.96);
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
    linear-gradient(180deg, rgba(243, 233, 221, 0.95), rgba(255, 250, 244, 0.2));
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
  gap: 24px;
}

.kratom-product-delivery-card {
  order: 3;
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

.kratom-product-adulto__badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 56px;
  height: 32px;
  padding: 0 14px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.22);
  background: rgba(255, 255, 255, 0.12);
  font-size: 14px;
  font-weight: 800;
  letter-spacing: 0.08em;
}

.kratom-product-adulto__eyebrow {
  margin: 0;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgba(247, 241, 232, 0.72);
}

.kratom-product-adulto__title {
  margin: 0;
  max-width: 18ch;
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

.kratom-product-summary__eyebrow,
.kratom-product-panel__eyebrow {
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  color: #f28d1a;
  font-weight: 800;
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

.kratom-product-summary__pricing {
  display: flex;
  align-items: end;
  gap: 12px;
}

:deep(.kratom-product-summary__price .value) {
  font-size: 40px;
  font-weight: 800;
  color: #162014;
}

.kratom-product-summary__facts {
  display: grid;
  gap: 10px;
}

.kratom-product-summary__fact {
  padding: 14px 16px;
  border-radius: 18px;
  background: rgba(247, 239, 231, 0.86);
  display: flex;
  justify-content: space-between;
  gap: 14px;

  span {
    color: #6b7266;
  }

  strong {
    color: #203019;
  }
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
  margin-top: 8px;
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
    width: 56px;
    height: 56px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fffdf8;
    border: 0;
    cursor: pointer;
  }

  span {
    width: 64px;
    font-weight: 800;
    cursor: default;
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
  color: #f28d1a;
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
    grid-template-columns: repeat(5, minmax(0, 1fr));
  }
}

.kratom-product-panel {
  padding: 40px;
}

.kratom-product-panel--notice {
  background: linear-gradient(135deg, rgba(53, 82, 74, 0.08), rgba(138, 90, 43, 0.08));
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

@include mobile {
  .kratom-product-shell {
    padding-top: 24px;
    padding-bottom: 40px;
  }

  .kratom-product-gallery,
  .kratom-product-summary,
  .kratom-product-panel {
    border-radius: 28px;
  }

  .kratom-product-gallery,
  .kratom-product-summary,
  .kratom-product-panel,
  .kratom-product-adulto,
  .kratom-product-delivery-card {
    padding-left: 20px;
    padding-right: 20px;
  }

  .kratom-product-gallery {
    padding-top: 20px;
    padding-bottom: 20px;
  }

  .kratom-product-summary,
  .kratom-product-panel,
  .kratom-product-adulto,
  .kratom-product-delivery-card {
    padding-top: 24px;
    padding-bottom: 24px;
  }

  .kratom-product-gallery__stage {
    height: 360px;
    border-radius: 24px;
  }

  .kratom-product-summary__title {
    font-size: clamp(34px, 10vw, 46px);
  }
}
</style>
