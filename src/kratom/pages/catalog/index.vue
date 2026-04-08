<script setup lang="ts">
const { t, locale } = useI18n()
const route = useRoute()
const router = useRouter()
const runtimeConfig = useRuntimeConfig()
const categorySlug = runtimeConfig.public.kratomStore?.categorySlug || 'kratom'

const page = computed(() => Math.max(Math.floor(Number(route.query.page) || 1), 1))

const { data, pending, error } = await useAsyncData(
  () => `kratom-catalog-${locale.value}-${page.value}`,
  async () => {
    const response = await useProductStore().catalog({
      with_products: true,
      category_slug: categorySlug,
      per_page: 16,
      page: page.value,
      cache: true,
    })

    if (response.error.value) {
      throw response.error.value
    }

    return response.data.value
  },
  { server: true },
)

if (error.value) {
  throw createError({
    statusCode: error.value.statusCode || error.value.status || 500,
    statusMessage: error.value.statusMessage || error.value.message || 'Catalog load failed',
    fatal: true,
  })
}

const products = computed(() => data.value?.products?.data || [])
const meta = computed(() => data.value?.products?.meta || { current_page: 1, last_page: 1, total: 0 })

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.catalog'), item: '/catalog' },
])

const goToPage = async (nextPage: number) => {
  const query = { ...route.query } as Record<string, any>
  if (nextPage <= 1) {
    delete query.page
  } else {
    query.page = String(nextPage)
  }
  await router.push({ query })
}

useSeo().setPageSeo('catalog', {
  params: () => ({
    pageSuffix: page.value > 1 ? ` - ${t('label.page', { page: page.value })}` : '',
  }),
  fallbackTitle: () => t('title.catalog'),
})
</script>

<template>
  <div class="page-base kratom-catalog-page">
    <div class="container kratom-page-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />

      <section class="kratom-page-hero">
        <p class="kratom-page-hero__eyebrow">{{ t('kratom.catalog.eyebrow') }}</p>
        <h1 class="kratom-page-hero__title">{{ t('title.catalog') }}</h1>
        <p class="kratom-page-hero__text">{{ t('kratom.catalog.text') }}</p>
        <div class="kratom-page-hero__meta">
          <span>{{ meta.total }} {{ t('title.products') }}</span>
          <span>{{ t('kratom.catalog.fulfilment') }}</span>
          <span>{{ t('kratom.catalog.age_verification') }}</span>
        </div>
      </section>

      <div v-if="pending" class="kratom-catalog-grid kratom-catalog-grid--skeleton">
        <div v-for="i in 8" :key="i" class="kratom-catalog-skeleton"></div>
      </div>

      <div v-else-if="products.length" class="kratom-catalog-grid">
        <KratomProductCard v-for="product in products" :key="product.id" :product="product" />
      </div>

      <div v-else class="kratom-empty-state">
        {{ t('messages.no_catalog_results') }}
      </div>

      <div v-if="meta.last_page > 1" class="kratom-catalog-pagination">
        <simple-pagination :current="meta.current_page" :total="meta.last_page" @update:current="goToPage" />
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-page-shell {
  padding-top: 24px;
}

.kratom-page-hero {
  margin-bottom: 40px;
  // padding: 48px 40px;
  border-radius: 40px;
  // background:
  //   radial-gradient(circle at top right, rgba(242, 141, 26, 0.15), transparent 60%),
  //   linear-gradient(135deg, #18241b, #25382b);
  border: 1px solid rgba(255, 255, 255, 0.08);
  // box-shadow: 0 40px 100px rgba(24, 36, 27, 0.15);
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.kratom-page-hero__eyebrow {
  margin-bottom: 16px;
  font-size: 13px;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: #f28d1a;
  font-weight: 800;
}

.kratom-page-hero__title {
  margin-bottom: 18px;
  font-size: clamp(48px, 6vw, 72px);
  line-height: 1.05;
  color: $color-0;
}

.kratom-page-hero__text {
  max-width: 680px;
  color: $color-3;
  line-height: 1.7;
  font-size: 18px;
}

.kratom-page-hero__meta {
  margin-top: 24px;
  display: flex;
  flex-wrap: wrap;
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

.kratom-catalog-grid {
  display: grid;
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

.kratom-catalog-grid--skeleton {
  .kratom-catalog-skeleton {
    height: 430px;
    border-radius: 28px;
    background: linear-gradient(120deg, rgba(225, 215, 202, 0.8), rgba(247, 240, 232, 1), rgba(225, 215, 202, 0.8));
    background-size: 200% 100%;
    animation: pulse 1.5s linear infinite;
  }
}

.kratom-catalog-pagination {
  margin-top: 32px;
  display: flex;
  justify-content: center;
}

.kratom-empty-state {
  padding: 40px;
  border-radius: 28px;
  text-align: center;
  background: rgba(255, 255, 255, 0.72);
}

@keyframes pulse {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
