<script setup lang="ts">
import { AlertTriangle, ArrowLeft, ExternalLink, Eye, EyeOff, Image as ImageIcon, Save, Search, Trash2, Upload, X } from 'lucide-vue-next'
import AdminRichEditor from '~/components/AdminRichEditor.vue'
import AdminMediaPicker from '~/components/AdminMediaPicker.vue'

const route = useRoute()
const router = useRouter()
const api = useApi()
const config = useRuntimeConfig()
const { t } = useAdminI18n()
const { resolve: resolveMediaUrl } = useMediaUrl()

const isNew = computed(() => route.params.id === 'new')

type ArticleForm = {
  wiki_category_id: number | null
  slug: string
  title: string
  excerpt: string
  body: string
  cover_url: string
  cover_alt: string
  seo_keyword: string
  seo_secondary_keywords: string[]
  seo_search_intent: 'informational' | 'navigational' | 'transactional'
  seo_volume_estimate: number | null
  seo_meta_title: string
  seo_meta_description: string
  reading_time_minutes: number | null
  position: number
  status: 'draft' | 'published'
  published_at: string
  related_ids: number[]
}

function emptyForm(): ArticleForm {
  return {
    wiki_category_id: null,
    slug: '',
    title: '',
    excerpt: '',
    body: '',
    cover_url: '',
    cover_alt: '',
    seo_keyword: '',
    seo_secondary_keywords: [],
    seo_search_intent: 'informational',
    seo_volume_estimate: null,
    seo_meta_title: '',
    seo_meta_description: '',
    reading_time_minutes: null,
    position: 0,
    status: 'draft',
    published_at: '',
    related_ids: [],
  }
}

const form = ref<ArticleForm>(emptyForm())
const original = ref<any>(null)
const tab = ref<'content' | 'seo' | 'cover' | 'related'>('content')
const saving = ref(false)
const uploadingCover = ref(false)
const error = ref('')
const newSecondary = ref('')
const relatedSearch = ref('')
const mediaPickerOpen = ref(false)

const { data: catsData } = await useAsyncData('wiki-cats', () => api<any>('/pruvodce/categories'))
const categories = computed(() => catsData.value?.data ?? [])

if (!isNew.value) {
  const { data: articleData } = await useAsyncData(`wiki-article-${route.params.id}`,
    () => api<any>(`/pruvodce/articles/${route.params.id}`))
  if (articleData.value?.data) hydrate(articleData.value.data)
}

const { data: relList } = await useAsyncData('wiki-related-pool',
  () => api<any>('/pruvodce/articles', { params: { per_page: 200, status: 'published' } }))
const relatedCandidatesAll = computed<any[]>(() => {
  const list = relList.value?.data?.data ?? []
  return list.filter((a: any) => String(a.id) !== String(route.params.id))
})
const relatedCandidates = computed(() => {
  const q = relatedSearch.value.trim().toLowerCase()
  if (!q) return relatedCandidatesAll.value
  return relatedCandidatesAll.value.filter(
    (a: any) =>
      (a.title || '').toLowerCase().includes(q) ||
      (a.slug || '').toLowerCase().includes(q) ||
      (a.seo_keyword || '').toLowerCase().includes(q),
  )
})

const relatedSelected = computed(() =>
  form.value.related_ids
    .map((id) => relatedCandidatesAll.value.find((a: any) => a.id === id) || (original.value?.related || []).find((a: any) => a.id === id))
    .filter(Boolean),
)

function hydrate(d: any) {
  original.value = d
  form.value = {
    wiki_category_id: d.category?.id ?? null,
    slug: d.slug ?? '',
    title: d.title ?? '',
    excerpt: d.excerpt ?? '',
    body: d.body ?? '',
    cover_url: d.cover_url ?? '',
    cover_alt: d.cover_alt ?? '',
    seo_keyword: d.seo_keyword ?? '',
    seo_secondary_keywords: Array.isArray(d.seo_secondary_keywords) ? [...d.seo_secondary_keywords] : [],
    seo_search_intent: d.seo_search_intent ?? 'informational',
    seo_volume_estimate: d.seo_volume_estimate ?? null,
    seo_meta_title: d.seo_meta_title ?? '',
    seo_meta_description: d.seo_meta_description ?? '',
    reading_time_minutes: d.reading_time_minutes ?? null,
    position: d.position ?? 0,
    status: d.status ?? 'draft',
    published_at: d.published_at ? String(d.published_at).slice(0, 10) : '',
    related_ids: Array.isArray(d.related_ids) ? [...d.related_ids] : [],
  }
}

const commercialWarning = computed(() => {
  const bad = ['koupit', 'cena', 'levně', 'levne', 'sleva', 'akce', 'nejlepší', 'nejlepsi', 'doporučujeme', 'doporucujeme', 'prodej']
  const hay = (form.value.slug + ' ' + form.value.title + ' ' + form.value.seo_keyword).toLowerCase()
  return bad.filter((b) => hay.includes(b))
})

const coverPreview = computed(() => {
  if (original.value?.cover_display_url) return resolveMediaUrl(original.value.cover_display_url)
  if (form.value.cover_url) return resolveMediaUrl(form.value.cover_url)
  return ''
})

const headerTitle = computed(() => {
  if (isNew.value) return t('pruvodce.new')
  return form.value.title || `#${route.params.id}`
})

const publicUrl = computed(() => {
  const cat = categories.value.find((c: any) => c.id === form.value.wiki_category_id)
  if (!cat) return ''
  return `${config.public.siteBase}/pruvodce/${cat.slug}/${form.value.slug}`
})

async function save() {
  saving.value = true
  error.value = ''
  try {
    const payload = {
      ...form.value,
      published_at: form.value.published_at || null,
      seo_volume_estimate: form.value.seo_volume_estimate || null,
      reading_time_minutes: form.value.reading_time_minutes || null,
    }
    const url = isNew.value ? '/pruvodce/articles' : `/pruvodce/articles/${route.params.id}`
    const method = isNew.value ? 'POST' : 'PUT'
    const res = await api<any>(url, { method, body: payload })
    if (isNew.value) {
      router.push(`/pruvodce/${res.data.id}`)
    } else {
      hydrate(res.data)
    }
  } catch (e: any) {
    error.value = e?.data?.message || 'Nepodařilo se uložit'
  } finally {
    saving.value = false
  }
}

async function togglePublish() {
  if (isNew.value) {
    await save()
    return
  }
  const action = form.value.status === 'published' ? 'unpublish' : 'publish'
  try {
    const res = await api<any>(`/pruvodce/articles/${route.params.id}/${action}`, { method: 'POST' })
    hydrate(res.data)
  } catch (e: any) {
    error.value = e?.data?.message || 'Nepodařilo se změnit stav'
  }
}

async function removeArticle() {
  if (!confirm(t('pruvodce.actions.delete_confirm'))) return
  await api(`/pruvodce/articles/${route.params.id}`, { method: 'DELETE' })
  router.push('/pruvodce')
}

function previewArticle() {
  if (!publicUrl.value || form.value.status !== 'published') return
  window.open(publicUrl.value, '_blank')
}

function addSecondary() {
  const v = newSecondary.value.trim()
  if (!v) return
  if (form.value.seo_secondary_keywords.includes(v)) {
    newSecondary.value = ''
    return
  }
  form.value.seo_secondary_keywords.push(v)
  newSecondary.value = ''
}
function removeSecondary(i: number) {
  form.value.seo_secondary_keywords.splice(i, 1)
}

async function uploadCover(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file || isNew.value) return
  uploadingCover.value = true
  error.value = ''
  try {
    const fd = new FormData()
    fd.append('cover', file)
    if (form.value.cover_alt) fd.append('alt', form.value.cover_alt)
    const res = await api<any>(`/pruvodce/articles/${route.params.id}/cover`, { method: 'POST', body: fd })
    hydrate(res.data)
  } catch (e: any) {
    error.value = e?.data?.message || 'Nepodařilo se nahrát obálku'
  } finally {
    uploadingCover.value = false
    input.value = ''
  }
}

function onMediaPicked(url: string) {
  form.value.cover_url = url
  mediaPickerOpen.value = false
}

function removeCover() {
  form.value.cover_url = ''
  if (original.value) {
    original.value.cover_display_url = null
    original.value.cover_path = null
    original.value.cover_url = null
  }
}

function toggleRelated(id: number) {
  const idx = form.value.related_ids.indexOf(id)
  if (idx === -1) {
    if (form.value.related_ids.length >= 8) return
    form.value.related_ids.push(id)
  } else {
    form.value.related_ids.splice(idx, 1)
  }
}

function moveRelated(id: number, dir: -1 | 1) {
  const idx = form.value.related_ids.indexOf(id)
  if (idx === -1) return
  const newIdx = idx + dir
  if (newIdx < 0 || newIdx >= form.value.related_ids.length) return
  const list = [...form.value.related_ids]
  list.splice(idx, 1)
  list.splice(newIdx, 0, id)
  form.value.related_ids = list
}

const tabs = [
  { key: 'content', label: t('pruvodce.tab.content') },
  { key: 'seo', label: t('pruvodce.tab.seo') },
  { key: 'cover', label: t('pruvodce.tab.cover') },
  { key: 'related', label: t('pruvodce.tab.related') },
] as const
</script>

<template>
  <div class="space-y-4 max-w-6xl">
    <div class="flex items-center justify-between gap-4">
      <div class="flex items-center gap-3 min-w-0">
        <NuxtLink to="/pruvodce" class="btn-ghost" :title="t('pruvodce.actions.back')">
          <ArrowLeft :size="18" />
        </NuxtLink>
        <div class="min-w-0">
          <h1 class="text-2xl font-display truncate">{{ headerTitle }}</h1>
          <div v-if="!isNew && form.slug" class="text-xs text-ink-700/50 font-mono truncate">
            {{ original?.category?.slug ? `/${original.category.slug}/${form.slug}` : form.slug }}
          </div>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a
          v-if="!isNew && form.status === 'published' && publicUrl"
          :href="publicUrl"
          target="_blank"
          class="btn-ghost"
        >
          <ExternalLink :size="16" />
          {{ t('pruvodce.actions.preview') }}
        </a>
        <button
          v-if="!isNew"
          class="btn-ghost"
          :title="form.status === 'published' ? t('pruvodce.actions.unpublish') : t('pruvodce.actions.publish')"
          @click="togglePublish"
        >
          <component :is="form.status === 'published' ? EyeOff : Eye" :size="16" />
          {{ form.status === 'published' ? t('pruvodce.actions.unpublish') : t('pruvodce.actions.publish') }}
        </button>
        <button v-if="!isNew" class="btn-danger" @click="removeArticle">
          <Trash2 :size="16" /> {{ t('pruvodce.actions.delete') }}
        </button>
        <button class="btn-primary" :disabled="saving" @click="save">
          <Save :size="16" />
          {{ saving ? t('pruvodce.actions.saving') : t('pruvodce.actions.save') }}
        </button>
      </div>
    </div>

    <div v-if="error" class="card border-terracotta-500 text-terracotta-700">{{ error }}</div>

    <div v-if="commercialWarning.length"
      class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-start gap-2">
      <AlertTriangle :size="16" class="mt-0.5 shrink-0" />
      <div>{{ t('pruvodce.warn.commercial').replace('{terms}', commercialWarning.join(', ')) }}</div>
    </div>

    <div class="flex gap-1 border-b border-ink-700/10">
      <button
        v-for="tb in tabs"
        :key="tb.key"
        type="button"
        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
        :class="tab === tb.key
          ? 'border-moss-700 text-moss-700'
          : 'border-transparent text-ink-700/60 hover:text-ink-700'"
        @click="tab = tb.key"
      >
        {{ tb.label }}
      </button>
    </div>

    <!-- Tab: Obsah -->
    <div v-show="tab === 'content'" class="space-y-4">
      <div class="card space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="field-label">{{ t('pruvodce.fields.category') }} *</label>
            <select v-model="form.wiki_category_id" class="field-input">
              <option :value="null">—</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.title }}</option>
            </select>
          </div>
          <div>
            <label class="field-label">{{ t('pruvodce.fields.slug') }} *</label>
            <input v-model="form.slug" class="field-input font-mono" placeholder="mitragyna-speciosa" />
          </div>
        </div>

        <div>
          <label class="field-label">{{ t('pruvodce.fields.title') }} *</label>
          <input v-model="form.title" class="field-input" />
        </div>

        <div>
          <label class="field-label">{{ t('pruvodce.fields.excerpt') }}</label>
          <textarea v-model="form.excerpt" rows="3" class="field-input" maxlength="320" />
          <div class="text-xs text-ink-700/40 mt-1 text-right">{{ form.excerpt.length }}/320</div>
        </div>
      </div>

      <div class="card space-y-3">
        <label class="field-label">{{ t('pruvodce.fields.body') }} *</label>
        <AdminRichEditor v-model="form.body" min-height="420px" />
      </div>
    </div>

    <!-- Tab: SEO -->
    <div v-show="tab === 'seo'" class="space-y-4">
      <div class="card space-y-4">
        <div>
          <label class="field-label">{{ t('pruvodce.fields.seo_keyword') }}</label>
          <input v-model="form.seo_keyword" class="field-input" />
          <p class="text-xs text-ink-700/50 mt-1">{{ t('pruvodce.fields.seo_keyword_help') }}</p>
        </div>

        <div>
          <label class="field-label">{{ t('pruvodce.fields.seo_secondary') }}</label>
          <div v-if="form.seo_secondary_keywords.length" class="flex flex-wrap gap-2 mb-2">
            <span
              v-for="(k, i) in form.seo_secondary_keywords"
              :key="i"
              class="inline-flex items-center gap-1 px-2 py-1 bg-moss-100 text-moss-900 text-xs rounded"
            >
              {{ k }}
              <button class="text-moss-700 hover:text-terracotta-700" @click="removeSecondary(i)">
                <X :size="12" />
              </button>
            </span>
          </div>
          <div class="flex gap-2">
            <input
              v-model="newSecondary"
              class="field-input"
              placeholder="historie kratomu"
              @keydown.enter.prevent="addSecondary"
            />
            <button class="btn-ghost" @click="addSecondary">+</button>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="field-label">{{ t('pruvodce.fields.seo_volume') }}</label>
            <input v-model.number="form.seo_volume_estimate" type="number" min="0" class="field-input" />
          </div>
          <div>
            <label class="field-label">{{ t('pruvodce.fields.seo_intent') }}</label>
            <select v-model="form.seo_search_intent" class="field-input">
              <option value="informational">informational</option>
              <option value="navigational">navigational</option>
              <option value="transactional">transactional</option>
            </select>
          </div>
        </div>

        <div>
          <label class="field-label">{{ t('pruvodce.fields.meta_title') }}</label>
          <input v-model="form.seo_meta_title" class="field-input" maxlength="200" />
          <div class="text-xs text-ink-700/40 mt-1 text-right">{{ form.seo_meta_title.length }}/200</div>
        </div>

        <div>
          <label class="field-label">{{ t('pruvodce.fields.meta_description') }}</label>
          <textarea v-model="form.seo_meta_description" rows="3" class="field-input" maxlength="320" />
          <div class="text-xs text-ink-700/40 mt-1 text-right">{{ form.seo_meta_description.length }}/320</div>
        </div>
      </div>
    </div>

    <!-- Tab: Cover & meta -->
    <div v-show="tab === 'cover'" class="space-y-4">
      <div class="card space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-1">
            <div class="aspect-[4/3] bg-ink-700/[0.04] border border-ink-700/10 rounded-lg overflow-hidden flex items-center justify-center">
              <img v-if="coverPreview" :src="coverPreview" :alt="form.cover_alt" class="w-full h-full object-cover" />
              <div v-else class="text-ink-700/30 text-xs flex flex-col items-center gap-2">
                <ImageIcon :size="32" />
                <span>—</span>
              </div>
            </div>
          </div>
          <div class="md:col-span-2 space-y-3">
            <div>
              <label class="field-label">{{ t('pruvodce.cover.url_placeholder') }}</label>
              <input v-model="form.cover_url" class="field-input font-mono" placeholder="https://…" />
            </div>
            <div>
              <label class="field-label">{{ t('pruvodce.fields.cover_alt') }}</label>
              <input v-model="form.cover_alt" class="field-input" />
            </div>

            <div class="flex flex-wrap gap-2 pt-2">
              <button type="button" class="btn-ghost" @click="mediaPickerOpen = true">
                <ImageIcon :size="16" /> {{ t('pruvodce.cover.from_library') }}
              </button>

              <label v-if="!isNew" class="btn-ghost cursor-pointer">
                <Upload :size="16" />
                {{ uploadingCover ? t('pruvodce.cover.uploading') : t('pruvodce.cover.upload') }}
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  class="hidden"
                  :disabled="uploadingCover"
                  @change="uploadCover"
                />
              </label>

              <button
                v-if="coverPreview"
                type="button"
                class="btn-ghost text-terracotta-700"
                @click="removeCover"
              >
                <Trash2 :size="16" /> {{ t('pruvodce.cover.remove') }}
              </button>
            </div>

            <p v-if="isNew" class="text-xs text-ink-700/50">{{ t('pruvodce.cover.save_first') }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab: Související -->
    <div v-show="tab === 'related'" class="space-y-4">
      <div class="card space-y-4">
        <div>
          <label class="field-label">{{ t('pruvodce.fields.related') }}</label>
          <div class="text-xs text-ink-700/50 mb-2">
            {{ form.related_ids.length }} / 8
            <span v-if="form.related_ids.length >= 8" class="text-amber-600 ml-2">
              {{ t('pruvodce.related.limit_reached') }}
            </span>
          </div>
          <ol v-if="relatedSelected.length" class="space-y-1 mb-3 border border-ink-700/10 rounded-lg divide-y divide-ink-700/5">
            <li
              v-for="(a, i) in relatedSelected"
              :key="a.id"
              class="flex items-center gap-2 px-3 py-2 text-sm bg-cream-50/40"
            >
              <span class="text-xs text-ink-700/40 font-mono w-6">{{ i + 1 }}.</span>
              <div class="flex-1 min-w-0">
                <div class="truncate font-medium">{{ a.title }}</div>
                <div class="text-xs text-ink-700/50 font-mono truncate">{{ a.category?.slug }}/{{ a.slug }}</div>
              </div>
              <button type="button" class="btn-ghost p-1 text-xs" :disabled="i === 0" @click="moveRelated(a.id, -1)">↑</button>
              <button
                type="button"
                class="btn-ghost p-1 text-xs"
                :disabled="i === relatedSelected.length - 1"
                @click="moveRelated(a.id, 1)"
              >↓</button>
              <button type="button" class="btn-ghost p-1 text-terracotta-700" @click="toggleRelated(a.id)">
                <X :size="14" />
              </button>
            </li>
          </ol>
        </div>

        <div>
          <div class="relative mb-2">
            <Search :size="16" class="absolute left-2 top-1/2 -translate-y-1/2 text-ink-700/40" />
            <input v-model="relatedSearch" class="field-input pl-8" :placeholder="t('pruvodce.related.search')" />
          </div>
          <div class="max-h-96 overflow-auto border border-ink-700/10 rounded-lg divide-y divide-ink-700/5">
            <label
              v-for="a in relatedCandidates"
              :key="a.id"
              class="flex items-center gap-2 px-3 py-2 text-sm cursor-pointer hover:bg-cream-50/40"
            >
              <input
                type="checkbox"
                :checked="form.related_ids.includes(a.id)"
                :disabled="!form.related_ids.includes(a.id) && form.related_ids.length >= 8"
                @change="toggleRelated(a.id)"
              />
              <div class="flex-1 min-w-0">
                <div class="truncate">{{ a.title }}</div>
                <div class="text-xs text-ink-700/50 font-mono truncate">{{ a.category?.slug }}/{{ a.slug }}</div>
              </div>
              <span class="text-xs text-ink-700/40">{{ a.category?.title }}</span>
            </label>
            <div v-if="!relatedCandidates.length" class="px-3 py-6 text-center text-xs text-ink-700/40">
              {{ t('pruvodce.related.no_candidates') }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <AdminMediaPicker v-model="mediaPickerOpen" @pick="onMediaPicked" />
  </div>
</template>
