<script setup lang="ts">
const route = useRoute()
const { t, locale } = useI18n()
const { $api } = useNuxtApp()
const regionPath = useToLocalePath()
const runtimeConfig = useRuntimeConfig()

const slug = computed(() => String(route.params.blog || ''))
const relatedArticlesLimit = 8
const inlineProductsPerGrid = 3
const categorySlug = runtimeConfig.public.kratomStore?.categorySlug || 'kratom'

type GenericRecord = Record<string, any>
type BlogArticleCard = {
  id: number | string
  slug: string
  [key: string]: any
}
type HeadingPoint = {
  sliceIndex: number
  offset: number
}
type ArticleChunk = {
  key: string
  slices: GenericRecord[]
}

const HEADING_PATTERN = /<h([2-4])\b[^>]*>[\s\S]*?<\/h\1>/gi

const shuffleItems = <T,>(items: T[]) => {
  const shuffled = [...items]

  for (let index = shuffled.length - 1; index > 0; index -= 1) {
    const randomIndex = Math.floor(Math.random() * (index + 1))
    ;[shuffled[index], shuffled[randomIndex]] = [shuffled[randomIndex], shuffled[index]]
  }

  return shuffled
}

const collectHeadingPoints = (slices: GenericRecord[]) => {
  const points: HeadingPoint[] = []

  slices.forEach((slice, sliceIndex) => {
    if (slice?.type !== 'html' || typeof slice?.html !== 'string') {
      return
    }

    const html = slice.html
    let match: RegExpExecArray | null

    HEADING_PATTERN.lastIndex = 0

    while ((match = HEADING_PATTERN.exec(html)) !== null) {
      if (sliceIndex === 0 && match.index === 0) {
        continue
      }

      points.push({
        sliceIndex,
        offset: match.index,
      })
    }
  })

  return points
}

const resolveSplitPoints = (slices: GenericRecord[]) => {
  const headingPoints = collectHeadingPoints(slices)

  if (headingPoints.length < 2 || slices.length < 3) {
    return [] as HeadingPoint[]
  }

  if (headingPoints.length >= 5) {
    const first = headingPoints[Math.floor(headingPoints.length / 3)]
    const second = headingPoints[Math.floor((headingPoints.length * 2) / 3)]

    if (first && second && (first.sliceIndex !== second.sliceIndex || first.offset !== second.offset)) {
      return [first, second]
    }
  }

  const middle = headingPoints[Math.floor(headingPoints.length / 2)]
  return middle ? [middle] : []
}

const splitArticleSlices = (slices: GenericRecord[], splitPoints: HeadingPoint[]) => {
  if (!splitPoints.length) {
    return [{
      key: 'article-chunk-0',
      slices,
    }] as ArticleChunk[]
  }

  const groupedPoints = splitPoints.reduce<Record<number, number[]>>((accumulator, point) => {
    if (!accumulator[point.sliceIndex]) {
      accumulator[point.sliceIndex] = []
    }

    accumulator[point.sliceIndex].push(point.offset)
    accumulator[point.sliceIndex].sort((left, right) => left - right)
    return accumulator
  }, {})

  const chunks: ArticleChunk[] = []
  let currentChunk: GenericRecord[] = []

  const pushCurrentChunk = () => {
    const normalizedChunk = currentChunk.filter((slice) => {
      if (slice?.type !== 'html') {
        return true
      }

      return String(slice?.html || '').trim().length > 0
    })

    if (normalizedChunk.length) {
      chunks.push({
        key: `article-chunk-${chunks.length}`,
        slices: normalizedChunk,
      })
    }

    currentChunk = []
  }

  slices.forEach((slice, sliceIndex) => {
    const slicePoints = groupedPoints[sliceIndex]

    if (!slicePoints?.length || slice?.type !== 'html' || typeof slice?.html !== 'string') {
      currentChunk.push(slice)
      return
    }

    let lastOffset = 0
    const html = slice.html

    slicePoints.forEach((offset) => {
      const beforeHtml = html.slice(lastOffset, offset)

      if (beforeHtml.trim()) {
        currentChunk.push({
          ...slice,
          html: beforeHtml,
        })
      }

      pushCurrentChunk()
      lastOffset = offset
    })

    const afterHtml = html.slice(lastOffset)

    if (afterHtml.trim()) {
      currentChunk.push({
        ...slice,
        html: afterHtml,
      })
    }
  })

  pushCurrentChunk()

  return chunks.length
    ? chunks
    : [{
        key: 'article-chunk-0',
        slices,
      }]
}

const fetchCatalogPage = async (page: number) => {
  const response = await useProductStore().catalog({
    with_products: true,
    category_slug: categorySlug,
    per_page: 48,
    page,
    cache: true,
  })

  if (response.error.value) {
    throw response.error.value
  }

  return response.data.value
}

const { data, error } = await useAsyncData(
  () => `kratom-article-${slug.value}-${locale.value}`,
  async () => {
    const response = await $api(`/articles/${slug.value}`)
    if (!response?.data) {
      throw createError({ statusCode: 404, statusMessage: t('error.article_not_found') })
    }
    return response.data
  },
  { server: true },
)

if (error.value) {
  throw createError({
    statusCode: error.value.statusCode || error.value.status || 404,
    statusMessage: error.value.statusMessage || error.value.message || t('error.article_not_found'),
    fatal: true,
  })
}

const article = computed(() => data.value)
const articleSplitPoints = computed(() => resolveSplitPoints(article.value?.content_slices || []))
const articleChunks = computed(() => splitArticleSlices(article.value?.content_slices || [], articleSplitPoints.value))
const inlineProductGridCount = computed(() => Math.max(articleSplitPoints.value.length, 1))

const { data: inlineProductsData } = await useAsyncData(
  () => `kratom-article-inline-products-${slug.value}-${locale.value}-${inlineProductGridCount.value}`,
  async () => {
    const neededProducts = inlineProductGridCount.value * inlineProductsPerGrid

    try {
      const firstPage = await fetchCatalogPage(1)
      const firstPageProducts = firstPage?.products?.data || []
      const lastPage = Number(firstPage?.products?.meta?.last_page || 1)
      const restPages = lastPage > 1
        ? await Promise.all(Array.from({ length: lastPage - 1 }, (_, index) => fetchCatalogPage(index + 2)))
        : []

      const allProducts = [
        ...firstPageProducts,
        ...restPages.flatMap((pageData) => pageData?.products?.data || []),
      ]

      const seen = new Set<string | number>()
      const uniqueProducts = allProducts.filter((product: GenericRecord | null) => {
        const key = product?.id ?? product?.slug

        if (!key || seen.has(key)) {
          return false
        }

        seen.add(key)
        return true
      })

      return shuffleItems(uniqueProducts).slice(0, neededProducts)
    } catch (fetchError) {
      console.error('Failed to load inline article products', fetchError)
      return []
    }
  },
  { server: true, default: () => [] },
)

const { data: relatedArticlesData } = await useAsyncData(
  () => `kratom-article-related-${slug.value}-${locale.value}`,
  async () => {
    const response = await $api('/articles', {
      query: {
        per_page: 24,
      },
    })

    const articles = Array.isArray(response?.data) ? response.data as BlogArticleCard[] : []

    return shuffleItems(
      articles.filter((item) => item?.slug && item.slug !== slug.value),
    ).slice(0, relatedArticlesLimit)
  },
  { server: true, default: () => [] },
)

const inlineProductGrids = computed(() => {
  return Array.from({ length: inlineProductGridCount.value }, (_, index) => {
    return (inlineProductsData.value || []).slice(
      index * inlineProductsPerGrid,
      (index + 1) * inlineProductsPerGrid,
    )
  }).filter((products) => products.length === inlineProductsPerGrid)
})

const relatedArticles = computed(() => relatedArticlesData.value || [])
const allArticlesPath = computed(() => regionPath('/blog'))
const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.blog'), item: '/blog' },
  { name: article.value?.title || '', item: `/blog/${slug.value}` },
])

useSeo().setPageSeo('article', {
  params: () => ({
    title: article.value?.title || '',
  }),
  title: () => article.value?.seo?.meta_title,
  description: () => article.value?.seo?.meta_description,
  image: () => article.value?.image?.src,
  ogType: 'article',
})
</script>

<template>
  <div class="page-base kratom-article-page">
    <div v-if="article" class="container kratom-page-container kratom-article-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />
      <section class="kratom-article-hero" :class="{ 'kratom-article-hero--no-image': !article.image?.src }">
        <div class="kratom-article-hero__content">
          <p class="kratom-page-hero__eyebrow">{{ t('kratom.article.reading_time', { minutes: Number(article.time || 0).toFixed(0) }) }}</p>
          <h1 class="kratom-page-hero__title">{{ article.title }}</h1>
        </div>
        <div v-if="article.image?.src" class="kratom-article-hero__media">
          <nuxt-img
            :src="article.image.src"
            :alt="article.image.alt || article.title"
            width="1200"
            height="900"
            sizes="mobile:100vw tablet:100vw desktop:960px"
            format="webp"
            fit="cover"
            class="kratom-article-hero__image"
          />
        </div>
      </section>

      <section class="kratom-article-layout">
        <template v-for="(chunk, chunkIndex) in articleChunks" :key="chunk.key">
          <article class="kratom-article-content">
            <slice-area :slices="chunk.slices" class="article-text" />
          </article>

          <section
            v-if="articleSplitPoints.length && chunkIndex < articleChunks.length - 1 && inlineProductGrids[chunkIndex]"
            class="kratom-article-products"
          >
            <div class="kratom-article-products__header">
              <div class="kratom-article-products__title">{{ t('kratom.article.products_title') }}</div>
              <NuxtLink :to="regionPath('/catalog')" class="kratom-article-products__link">
                {{ t('kratom.article.products_action') }}
              </NuxtLink>
            </div>
            <div class="kratom-article-products__grid">
              <KratomProductCard
                v-for="product in inlineProductGrids[chunkIndex]"
                :key="product.id ?? product.slug"
                :product="product"
              />
            </div>
          </section>
        </template>

        <section v-if="!articleSplitPoints.length && inlineProductGrids[0]" class="kratom-article-products">
          <div class="kratom-article-products__header">
            <div class="kratom-article-products__title">{{ t('kratom.article.products_title') }}</div>
            <NuxtLink :to="regionPath('/catalog')" class="kratom-article-products__link">
              {{ t('kratom.article.products_action') }}
            </NuxtLink>
          </div>
          <div class="kratom-article-products__grid">
            <KratomProductCard
              v-for="product in inlineProductGrids[0]"
              :key="product.id ?? product.slug"
              :product="product"
            />
          </div>
        </section>
      </section>
    </div>

    <div v-if="relatedArticles.length" class="container kratom-page-container kratom-article-related-shell">
      <section class="kratom-article-related">
        <div class="kratom-article-related__intro">
          <h2 class="kratom-article-related__title">{{ t('kratom.article.more_articles_title') }}</h2>
        </div>

        <div class="kratom-article-related__grid">
          <article-card
            v-for="(relatedArticle, index) in relatedArticles"
            :key="relatedArticle.id"
            :item="relatedArticle"
            :index="index"
          />
        </div>

        <div class="kratom-article-related__actions">
          <NuxtLink :to="allArticlesPath" class="button primary kratom-article-related__button">
            {{ t('kratom.article.all_articles') }}
          </NuxtLink>
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-article-shell { padding-top: 24px; padding-bottom: 48px; max-width: 1000px; }
.kratom-article-hero { margin-bottom: 32px; display: flex; flex-direction: column; align-items: center; gap: 24px; padding: 0; background: none; border: 0; box-shadow: none; text-align: center; }
.kratom-article-hero--no-image { max-width: none; }
.kratom-article-hero__content { display: flex; flex-direction: column; justify-content: center; align-items: flex-start; min-width: 0; width: 100%; max-width: none; text-align: left; }
.kratom-page-hero__eyebrow { margin-bottom: 14px; color: #8a5a2b; text-transform: uppercase; letter-spacing: .15em; font-size: 13px; font-weight: 800; }
.kratom-page-hero__title { font-size: clamp(36px, 5vw, 60px); line-height: 1.05; color: #1f2b1d; }
.kratom-article-hero__media { width: 100%; max-width: none; min-height: 280px; border-radius: 28px; overflow: hidden; background: #fff4e6; }
.kratom-article-hero__image { display: block; width: 100%; height: 100%; object-fit: cover; object-position: center; }
.kratom-article-layout { width: 100%; max-width: none; margin: 0; display: flex; flex-direction: column; gap: 24px; }
.kratom-article-content { width: 100%; padding: 24px; border-radius: 32px; background: rgba(255, 250, 244, 0.96); border: 1px solid rgba(74, 91, 68, 0.08); box-shadow: 0 24px 60px rgba(39, 49, 36, 0.05); font-size: 18px; line-height: 1.7; color: #434d3d; @include tablet { padding: 40px; } }
.kratom-article-content :deep(.slice-area) { gap: 2rem; }
.kratom-article-content :deep(.rich-text) { font-size: inherit; line-height: inherit; }
.kratom-article-products { width: 100%; }
.kratom-article-products__header { margin-bottom: 20px; display: flex; flex-direction: column; gap: 12px; align-items: flex-start; @include tablet { flex-direction: row; justify-content: space-between; align-items: center; } }
.kratom-article-products__title { font-size: clamp(15px, 2.4vw, 23px); line-height: 1.05; color: #1f2b1d; }
.kratom-article-products__link { font-size: 13px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #8a5a2b; transition: color .2s ease; &:hover { color: #1f2b1d; } }
.kratom-article-products__grid { display: grid; gap: 20px; @include tablet { grid-template-columns: repeat(2, minmax(0, 1fr)); } @include desktop { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
.kratom-article-related-shell { padding-bottom: 48px; }
.kratom-article-related { display: flex; flex-direction: column; gap: 24px; }
.kratom-article-related__title { font-size: clamp(28px, 4vw, 44px); line-height: 1.05; color: #1f2b1d; }
.kratom-article-related__grid { display: grid; gap: 20px; @include tablet { grid-template-columns: repeat(2, minmax(0, 1fr)); } @include desktop { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
.kratom-article-related__actions { display: flex; justify-content: center; }
.kratom-article-related__button { min-width: 220px; justify-content: center; }
@include desktop { .kratom-article-hero__image { max-height: 600px; } }
</style>
<i18n lang="yaml">
cs:
  kratom:
    article:
      more_articles_title: Další články
      all_articles: Všechny články
      products_title: Koupit kratom v Česku
      products_action: Více kratomu
en:
  kratom:
    article:
      more_articles_title: More articles
      all_articles: All articles
      products_title: Buy kratom in Czechia
      products_action: More kratom
ru:
  kratom:
    article:
      more_articles_title: Другие статьи
      all_articles: Все статьи
      products_title: Купить кратом в Чехии
      products_action: Больше кратома
uk:
  kratom:
    article:
      more_articles_title: Інші статті
      all_articles: Усі статті
      products_title: Купити кратом у Чехії
      products_action: Більше кратому
</i18n>
