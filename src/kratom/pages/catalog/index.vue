<script setup lang="ts">
const { t, locale } = useI18n()
const route = useRoute()
const router = useRouter()
const runtimeConfig = useRuntimeConfig()
const categorySlug = runtimeConfig.public.kratomStore?.categorySlug || 'kratom'

const page = computed(() => Math.max(Number(route.query.page) || 1, 1))

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

useSeo().setPageSeo(t('title.catalog'))
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
  padding-top: 32px;
}

.kratom-page-hero {
  margin-bottom: 32px;
  padding: 28px;
  border-radius: 32px;
  background:
    radial-gradient(circle at top left, rgba(142, 177, 129, 0.22), transparent 35%),
    linear-gradient(135deg, rgba(255, 247, 236, 0.96), rgba(245, 236, 225, 0.82));
  border: 1px solid rgba(74, 91, 68, 0.1);
}

.kratom-page-hero__eyebrow {
  margin-bottom: 12px;
  font-size: 12px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #8a5a2b;
  font-weight: 700;
}

.kratom-page-hero__title {
  margin-bottom: 14px;
  font-size: 52px;
  line-height: 0.98;
  color: #182116;
}

.kratom-page-hero__text {
  max-width: 680px;
  color: #5f6458;
  line-height: 1.7;
}

.kratom-page-hero__meta {
  margin-top: 18px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;

  span {
    padding: 10px 14px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.74);
    border: 1px solid rgba(74, 91, 68, 0.08);
    color: #334130;
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
