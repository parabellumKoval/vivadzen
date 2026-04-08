<script setup lang="ts">
const { t, locale } = useI18n()
const route = useRoute()
const router = useRouter()
const { $api } = useNuxtApp()

const page = computed(() => Math.max(Math.floor(Number(route.query.page) || 1), 1))

const { data, error, pending } = await useAsyncData(
  () => `kratom-articles-${locale.value}-${page.value}`,
  async () => {
    return await $api('/articles', {
      query: {
        per_page: 12,
        page: page.value,
      },
    })
  },
  { server: true },
)

if (error.value) {
  throw createError({
    statusCode: error.value.statusCode || error.value.status || 500,
    statusMessage: error.value.statusMessage || error.value.message || 'Blog load failed',
    fatal: true,
  })
}

const articles = computed(() => data.value?.data || [])
const meta = computed(() => data.value?.meta || { current_page: 1, last_page: 1 })
const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.blog'), item: '/blog' },
])

const goToPage = async (nextPage: number) => {
  const query = { ...route.query } as Record<string, any>
  if (nextPage <= 1) delete query.page
  else query.page = String(nextPage)
  await router.push({ query })
}

useSeo().setPageSeo('blog', {
  params: () => ({
    pageSuffix: page.value > 1 ? ` - ${t('label.page', { page: page.value })}` : '',
  }),
  fallbackTitle: () => t('title.blog'),
})
</script>

<template>
  <div class="page-base kratom-blog-page">
    <div class="container kratom-page-container kratom-page-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />

      <section class="kratom-page-hero">
        <p class="kratom-page-hero__eyebrow">{{ t('kratom.blog.eyebrow') }}</p>
        <h1 class="kratom-page-hero__title">{{ t('title.blog') }}</h1>
        <p class="kratom-page-hero__text">{{ t('kratom.blog.text') }}</p>
      </section>

      <div v-if="pending" class="kratom-articles-grid kratom-articles-grid--pending">
        <div v-for="i in 6" :key="i" class="kratom-article-skeleton"></div>
      </div>
      <div v-else-if="articles.length" class="kratom-articles-grid">
        <article-card v-for="(article, index) in articles" :key="article.id" :item="article" :index="index" />
      </div>
      <div v-else class="kratom-empty-state">{{ t('messages.no_results') }}</div>

      <div v-if="meta.last_page > 1" class="kratom-catalog-pagination">
        <simple-pagination :current="meta.current_page" :total="meta.last_page" @update:current="goToPage" />
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-page-shell { padding-top: 16px; padding-bottom: 32px; }
.kratom-page-hero { margin-bottom: 32px; padding: 0; border: 0; background: none; box-shadow: none; display: flex; flex-direction: column; justify-content: center; }
.kratom-page-hero__eyebrow { margin-bottom: 14px; color: #8a5a2b; text-transform: uppercase; letter-spacing: .15em; font-size: 13px; font-weight: 800; }
.kratom-page-hero__title { margin-bottom: 16px; font-size: clamp(40px, 6vw, 64px); line-height: 1.05; color: #1f2b1d; }
.kratom-page-hero__text { max-width: 700px; color: #5f6458; line-height: 1.7; font-size: 18px; }
.kratom-articles-grid { display: grid; gap: 20px; @include tablet { grid-template-columns: repeat(2, minmax(0, 1fr)); } @include desktop { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
.kratom-articles-grid--pending .kratom-article-skeleton { height: 340px; border-radius: 28px; background: linear-gradient(120deg, rgba(225, 215, 202, 0.8), rgba(247, 240, 232, 1), rgba(225, 215, 202, 0.8)); background-size: 200% 100%; animation: pulse 1.5s linear infinite; }
.kratom-catalog-pagination { margin-top: 32px; display: flex; justify-content: center; }
.kratom-empty-state { padding: 40px; border-radius: 28px; text-align: center; background: rgba(255,255,255,.72); }
@keyframes pulse { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
</style>
