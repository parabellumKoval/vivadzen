<script setup lang="ts">
const route = useRoute()
const { t, locale } = useI18n()

const slug = computed(() => String(route.params.blog || ''))
const tableOfContents = ref<Array<{ id: string; title: string; tag: string }>>([])
const html = ref<any>(null)

const { data, error } = await useAsyncData(
  () => `kratom-article-${slug.value}-${locale.value}`,
  async () => {
    const response = await useArticleStore().show(slug.value)
    if (!response?.data) {
      throw createError({ statusCode: 404, statusMessage: 'Article not found' })
    }
    return response.data
  },
  { server: true },
)

if (error.value) {
  throw createError({
    statusCode: error.value.statusCode || error.value.status || 404,
    statusMessage: error.value.statusMessage || error.value.message || 'Article not found',
    fatal: true,
  })
}

const article = computed(() => data.value)
const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.blog'), item: '/blog' },
  { name: article.value?.title || '', item: `/blog/${slug.value}` },
])

const scrollHandler = (id: string) => {
  nextTick(() => {
    const target = document.getElementById(id)
    target?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
}

watch(() => html.value, (value) => {
  const container = value?.$el ?? value
  if (!container || typeof container.querySelectorAll !== 'function') {
    return
  }

  tableOfContents.value = []
  const headers = container.querySelectorAll('h2, h3')
  headers.forEach((item: HTMLElement, index: number) => {
    const id = `header-${index + 1}`
    const headerText = item.innerText || item.textContent
    if (headerText) {
      tableOfContents.value.push({ id, title: headerText, tag: item.tagName })
    }
    item.setAttribute('id', id)
  })
}, { immediate: true })

useHead(() => ({
  title: article.value?.seo?.meta_title || article.value?.title,
  meta: [
    {
      name: 'description',
      content: article.value?.seo?.meta_description || article.value?.excerpt || article.value?.title,
    },
  ],
}))
</script>

<template>
  <div class="page-base kratom-article-page">
    <div v-if="article" class="container kratom-article-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />
      <section class="kratom-article-hero">
        <div>
          <p class="kratom-page-hero__eyebrow">{{ t('kratom.article.reading_time', { minutes: Number(article.time || 0).toFixed(0) }) }}</p>
          <h1 class="kratom-page-hero__title">{{ article.title }}</h1>
        </div>
        <nuxt-img
          v-if="article.image?.src"
          :src="article.image.src"
          :alt="article.image.alt || article.title"
          width="1400"
          height="800"
          sizes="mobile:100vw tablet:100vw desktop:960px"
          format="webp"
          class="kratom-article-hero__image"
        />
      </section>

      <section class="kratom-article-layout">
        <div class="kratom-article-content">
          <slice-area :slices="article.content_slices" ref="html" class="article-text" />
        </div>

        <aside v-if="tableOfContents.length" class="kratom-article-toc">
          <p class="kratom-article-toc__title">{{ t('kratom.article.content') }}</p>
          <ol>
            <li v-for="item in tableOfContents" :key="item.id">
              <button type="button" @click="scrollHandler(item.id)">{{ item.title }}</button>
            </li>
          </ol>
        </aside>
      </section>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-article-shell { padding-top: 32px; max-width: 1120px; }
.kratom-article-hero { margin-bottom: 28px; display: grid; gap: 22px; padding: 28px; border-radius: 32px; background: linear-gradient(135deg, rgba(255,247,236,.96), rgba(240,231,219,.92)); border: 1px solid rgba(74,91,68,.1); }
.kratom-page-hero__eyebrow { margin-bottom: 12px; color: #8a5a2b; text-transform: uppercase; letter-spacing: .12em; font-size: 12px; font-weight: 700; }
.kratom-page-hero__title { font-size: clamp(42px, 6vw, 72px); line-height: .98; }
.kratom-article-hero__image { width: 100%; border-radius: 24px; }
.kratom-article-layout { display: grid; gap: 24px; @include desktop { grid-template-columns: minmax(0, 1fr) 280px; } }
.kratom-article-content { padding: 28px; border-radius: 32px; background: rgba(255,250,244,.92); border: 1px solid rgba(74,91,68,.1); }
.kratom-article-toc { padding: 24px; border-radius: 28px; background: rgba(255,250,244,.92); border: 1px solid rgba(74,91,68,.1); height: fit-content; @include desktop { position: sticky; top: 98px; } }
.kratom-article-toc__title { margin-bottom: 14px; font-size: 12px; text-transform: uppercase; letter-spacing: .12em; color: #8a5a2b; font-weight: 700; }
.kratom-article-toc ol { display: grid; gap: 10px; }
.kratom-article-toc button { border: 0; background: none; text-align: left; color: #31402f; line-height: 1.5; cursor: pointer; }
</style>
