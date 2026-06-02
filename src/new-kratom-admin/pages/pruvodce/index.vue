<script setup lang="ts">
import { AlertTriangle, ExternalLink, Plus, Search } from 'lucide-vue-next'

const api = useApi()
const config = useRuntimeConfig()
const router = useRouter()
const { t, formatDateTime } = useAdminI18n()

const search = ref('')
const categoryId = ref<number | ''>('')
const status = ref('')
const page = ref(1)

const { data, pending, refresh } = await useAsyncData(
  'wiki-articles',
  () => api<any>('/pruvodce/articles', {
    params: {
      q: search.value,
      category_id: categoryId.value === '' ? undefined : categoryId.value,
      status: status.value,
      page: page.value,
    },
  }),
  { watch: [search, categoryId, status, page] },
)

const { data: catsData } = await useAsyncData('wiki-categories', () => api<any>('/pruvodce/categories'))
const categories = computed(() => catsData.value?.data ?? [])
const articles = computed(() => data.value?.data?.data ?? [])
const meta = computed(() => data.value?.data ?? {})

function openArticle(id: number | string) {
  router.push(`/pruvodce/${id}`)
}

function previewArticle(article: any) {
  if (!article.category?.slug) return
  const url = `${config.public.siteBase}/pruvodce/${article.category.slug}/${article.slug}`
  window.open(url, '_blank')
}

function warningTitle(terms: string[]) {
  return t('pruvodce.warn.commercial').replace('{terms}', terms.join(', '))
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-display">{{ t('pruvodce.title') }}</h1>
      <NuxtLink to="/pruvodce/new" class="btn-primary">
        <Plus :size="18" /> {{ t('pruvodce.new') }}
      </NuxtLink>
    </div>

    <div class="card">
      <div class="flex flex-wrap items-center gap-3 mb-4">
        <Search :size="18" class="text-ink-700/50" />
        <input v-model="search" :placeholder="t('pruvodce.search')" class="field-input max-w-sm" />
        <select v-model="categoryId" class="field-input max-w-[220px]">
          <option :value="''">{{ t('pruvodce.filter.all_categories') }}</option>
          <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.title }}</option>
        </select>
        <select v-model="status" class="field-input max-w-[190px]">
          <option value="">{{ t('pruvodce.filter.all_statuses') }}</option>
          <option value="draft">{{ t('pruvodce.status.draft') }}</option>
          <option value="published">{{ t('pruvodce.status.published') }}</option>
        </select>
      </div>

      <table class="w-full">
        <thead>
          <tr class="border-b border-ink-700/10">
            <th class="table-th">{{ t('pruvodce.col.title') }}</th>
            <th class="table-th">{{ t('pruvodce.col.category') }}</th>
            <th class="table-th">{{ t('pruvodce.col.keyword') }}</th>
            <th class="table-th">{{ t('pruvodce.col.status') }}</th>
            <th class="table-th">{{ t('pruvodce.col.updated') }}</th>
            <th class="table-th"></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="a in articles"
            :key="a.id"
            class="border-b border-ink-700/5 table-row-hover cursor-pointer"
            @click="openArticle(a.id)"
          >
            <td class="table-td max-w-[420px]">
              <div class="font-medium text-moss-700">{{ a.title }}</div>
              <div class="text-xs text-ink-700/50 font-mono">/{{ a.category?.slug }}/{{ a.slug }}</div>
            </td>
            <td class="table-td text-sm">{{ a.category?.title || '—' }}</td>
            <td class="table-td">
              <div class="flex items-center gap-2">
                <span class="text-sm text-moss-700">{{ a.seo_keyword || '—' }}</span>
                <span
                  v-if="a.commercial_warning?.length"
                  :title="warningTitle(a.commercial_warning)"
                  class="text-amber-600"
                  @click.stop
                >
                  <AlertTriangle :size="14" />
                </span>
              </div>
            </td>
            <td class="table-td">
              <span
                class="inline-block px-2 py-0.5 rounded text-xs font-medium"
                :class="a.status === 'published'
                  ? 'bg-moss-50 text-moss-700'
                  : 'bg-ink-700/10 text-ink-700/60'"
              >
                {{ t('pruvodce.status.' + a.status) }}
              </span>
            </td>
            <td class="table-td text-xs text-ink-700/60">{{ formatDateTime(a.updated_at) }}</td>
            <td class="table-td text-right">
              <button
                v-if="a.status === 'published'"
                class="btn-ghost p-1"
                :title="t('pruvodce.actions.preview')"
                @click.stop="previewArticle(a)"
              >
                <ExternalLink :size="16" />
              </button>
            </td>
          </tr>
          <tr v-if="!articles.length && !pending">
            <td colspan="6" class="table-td text-center text-ink-700/40">{{ t('pruvodce.empty') }}</td>
          </tr>
        </tbody>
      </table>

      <div class="flex items-center justify-between mt-4 text-sm text-ink-700/60">
        <span>{{ meta.from || 0 }}–{{ meta.to || 0 }} / {{ meta.total || 0 }}</span>
        <div class="flex gap-2">
          <button class="btn-ghost" :disabled="page <= 1" @click="page--">← {{ t('products.previous') }}</button>
          <button class="btn-ghost" :disabled="page >= (meta.last_page || 1)" @click="page++">{{ t('products.next') }} →</button>
        </div>
      </div>
    </div>
  </div>
</template>
