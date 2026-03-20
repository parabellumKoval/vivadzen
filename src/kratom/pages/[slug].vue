<script setup lang="ts">
const route = useRoute()
const { t, locale } = useI18n()
const regionPath = useToLocalePath()
const cartStore = useCartStore()
const quantity = ref(1)
const galleryIndex = ref(0)

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
      throw createError({ statusCode: 404, statusMessage: 'Product not found' })
    }
    return normalized
  },
  { server: true },
)

if (error.value) {
  throw createError({
    statusCode: error.value.statusCode || error.value.status || 404,
    statusMessage: error.value.statusMessage || error.value.message || 'Product not found',
    fatal: true,
  })
}

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
    <div class="container kratom-product-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />

      <section class="kratom-product-hero">
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
          <p>{{ t('kratom.product.checkout_note_text') }}</p>
        </article>
      </section>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-product-shell { max-width: 920px; padding-top: 32px; }
.kratom-product-hero { display: grid; gap: 22px; align-items: start; }
.kratom-product-gallery, .kratom-product-summary, .kratom-product-panel { border-radius: 32px; border: 1px solid rgba(74, 91, 68, 0.1); background: rgba(255, 250, 244, 0.92); box-shadow: 0 28px 80px rgba(39, 49, 36, 0.08); }
.kratom-product-gallery { padding: 22px; }
.kratom-product-gallery__stage { min-height: 420px; display: flex; align-items: center; justify-content: center; border-radius: 24px; background: radial-gradient(circle at top, rgba(143, 180, 134, 0.2), transparent 48%), linear-gradient(180deg, rgba(243, 233, 221, 0.92), rgba(255, 255, 255, 0.45)); }
.kratom-product-gallery__thumbs { margin-top: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(88px, 1fr)); gap: 10px; }
.kratom-product-gallery__thumb { border: 1px solid rgba(74, 91, 68, 0.12); border-radius: 18px; background: #fffdf8; padding: 10px; cursor: pointer; &.is-active { border-color: #35524a; } }
.kratom-product-summary { padding: 28px; display: flex; flex-direction: column; gap: 18px; }
.kratom-product-summary__eyebrow, .kratom-product-panel__eyebrow { font-size: 12px; text-transform: uppercase; letter-spacing: 0.12em; color: #8a5a2b; font-weight: 700; }
.kratom-product-summary__title { font-size: clamp(42px, 5vw, 66px); line-height: 0.96; color: #162014; }
.kratom-product-summary__text, .kratom-product-panel__content, .kratom-product-panel p { color: #5c6559; line-height: 1.75; }
.kratom-product-summary__pricing { display: flex; align-items: end; gap: 12px; }
:deep(.kratom-product-summary__price .value) { font-size: 40px; font-weight: 800; color: #162014; }
.kratom-product-summary__facts { display: grid; gap: 10px; }
.kratom-product-summary__fact { padding: 14px 16px; border-radius: 18px; background: rgba(247, 239, 231, 0.86); display: flex; justify-content: space-between; gap: 14px; span { color: #6b7266; } strong { color: #203019; } }
.kratom-product-summary__mods-title { margin-bottom: 12px; font-weight: 700; color: #293626; }
.kratom-product-summary__mods-grid { display: flex; flex-wrap: wrap; gap: 10px; }
.kratom-product-summary__mod { padding: 11px 16px; border-radius: 999px; border: 1px solid rgba(74, 91, 68, 0.14); text-decoration: none; color: #324030; &.is-active { background: #20301d; color: #fff7ec; border-color: #20301d; } }
.kratom-product-summary__buy-box { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; }
.kratom-product-summary__qty { display: inline-flex; align-items: center; border-radius: 999px; overflow: hidden; border: 1px solid rgba(74, 91, 68, 0.15); button, span { width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; background: #fffdf8; border: 0; } span { width: 54px; font-weight: 700; } }
.kratom-product-summary__buy-btn { flex: 1; min-height: 52px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; gap: 10px; }
.kratom-product-stack { margin-top: 28px; display: grid; gap: 22px; }
.kratom-product-panel { padding: 26px; }
.kratom-product-panel--notice { background: linear-gradient(135deg, rgba(53, 82, 74, 0.08), rgba(138, 90, 43, 0.08)); }
.kratom-product-attributes { display: grid; gap: 12px; }
.kratom-product-attributes__row { padding: 14px 0; display: flex; justify-content: space-between; gap: 16px; border-bottom: 1px solid rgba(74, 91, 68, 0.08); span { color: #6b7266; } strong { color: #1f2b1d; text-align: right; } }
</style>
