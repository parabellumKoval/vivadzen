<script setup lang="ts">
const { t, locale } = useI18n()
const regionPath = useToLocalePath()
const runtimeConfig = useRuntimeConfig()
const categorySlug = runtimeConfig.public.kratomStore?.categorySlug || 'kratom'
const perPage = 48

const fetchCatalogPage = async (page: number) => {
  const response = await useProductStore().catalog({
    with_products: true,
    category_slug: categorySlug,
    per_page: perPage,
    page,
    cache: true,
  })

  if (response.error.value) {
    throw response.error.value
  }

  return response.data.value
}

const { data, pending } = await useAsyncData(
  () => `landing-product-grid-${locale.value}`,
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

    return allProducts.filter((product: Record<string, any> | null) => {
      const key = product?.id ?? product?.slug
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

const products = computed(() => data.value || [])
</script>

<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section v-if="pending || products.length" class="landing-product-grid">
    <div class="container landing-product-grid__container">
      <div class="landing-product-grid__intro">
        <p class="landing-product-grid__eyebrow">{{ t('eyebrow') }}</p>
        <h2 class="landing-product-grid__title">{{ t('title') }}</h2>
        <p class="landing-product-grid__description">{{ t('description') }}</p>
        <div class="landing-product-grid__meta">
          <span>{{ t('meta.safe_purchase') }}</span>
          <span>{{ t('meta.age_verification') }}</span>
          <span>{{ t('meta.delivery') }}</span>
        </div>
      </div>

      <div v-if="pending" class="landing-product-grid__list landing-product-grid__list--skeleton">
        <div v-for="i in 8" :key="i" class="landing-product-grid__skeleton"></div>
      </div>

      <div v-else class="landing-product-grid__list">
        <KratomProductCard v-for="product in products" :key="product.id" :product="product" />
      </div>

      <div v-if="!pending && products.length" class="landing-product-grid__actions">
        <NuxtLink :to="regionPath('/catalog')" class="button secondary">
          {{ t('cta') }}
        </NuxtLink>
      </div>
    </div>
  </section>
</template>

<style scoped lang="scss">
.landing-product-grid {
  padding: 88px 0;
  background-color: $color-bg;
}

.landing-product-grid__container {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 50px;
}

.landing-product-grid__intro {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.landing-product-grid__eyebrow {
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: #f28d1a;
}

.landing-product-grid__title {
  font-size: clamp(34px, 5vw, 60px);
  line-height: 1.05;
  color: $color-0;
}

.landing-product-grid__description {
  max-width: 760px;
  font-size: 18px;
  line-height: 1.7;
  color: $color-3;
}

.landing-product-grid__meta {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 12px;

  span {
    padding: 10px 18px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(12px);
    border: 1px solid $color-green;
    color: $color-2;
    font-weight: 500;
  }
}

.landing-product-grid__list {
  display: grid;
  align-self: stretch;
  width: 100%;
  gap: 20px;

  @include tablet {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  @include desktop {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }

  @include xld {
    grid-template-columns: repeat(6, minmax(0, 1fr));
  }
}

.landing-product-grid__list--skeleton {
  .landing-product-grid__skeleton {
    height: 430px;
    border-radius: 28px;
    background: linear-gradient(120deg, rgba(225, 215, 202, 0.8), rgba(247, 240, 232, 1), rgba(225, 215, 202, 0.8));
    background-size: 200% 100%;
    animation: landing-product-grid-pulse 1.5s linear infinite;
  }
}

.landing-product-grid__actions {
  display: flex;
  justify-content: center;
}

@keyframes landing-product-grid-pulse {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}
</style>
