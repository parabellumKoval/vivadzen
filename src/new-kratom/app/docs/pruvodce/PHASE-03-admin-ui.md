# PHASE 03 — Админ-UI (Nuxt 3)

> Зависит от: PHASE-02 (admin API доступно).
> Время реализации: 2–3 часа.

## Что нужно сделать

1. Добавить пункт меню «Průvodce» в `layouts/default.vue` (под «Lab-tests»).
2. Создать сабменю с двумя пунктами: «Články» и «Kategorie» (по образцу
   «Форум»).
3. Создать страницы:
   - `pages/pruvodce/index.vue` — список статей (поиск, фильтр по
     категории, фильтр статуса, бейдж ⚠️ для коммерческих терминов,
     колонка «🎯 Klíčové slovo»).
   - `pages/pruvodce/[id].vue` — форма редактирования статьи (вкладки:
     **Obsah**, **SEO**, **Cover & meta**, **Související**).
   - `pages/pruvodce/categories/index.vue` — CRUD категорий
     (по образцу `pages/taxonomies/index.vue`).
4. Расширить `composables/useAdminI18n.ts` ключами `pruvodce.*`.

---

## 1. Меню в `layouts/default.vue`

После пункта `lab-batches` (строка ~15):

```ts
import { ..., BookOpen, FileText, FolderKanban } from 'lucide-vue-next'

const nav = [
  { to: '/',            label: t('nav.dashboard'),  icon: LayoutDashboard },
  { to: '/products',    label: t('nav.products'),   icon: Package },
  { to: '/lab-batches', label: t('nav.lab_batches'),icon: FlaskConical },
  // ↓ новое
  { to: '/pruvodce',    label: t('nav.pruvodce'),   icon: BookOpen },
  { to: '/taxonomies',  label: t('nav.taxonomies'), icon: Tags },
  // ... остальное
]
```

Под группой forumNav добавь группу `pruvodceNav` (по образцу forum):

```ts
const pruvodceNav = [
  { to: '/pruvodce', label: 'Články', icon: FileText, exact: true },
  { to: '/pruvodce/categories', label: 'Kategorie', icon: FolderKanban },
]
const isPruvodceActive = computed(() => route.path.startsWith('/pruvodce'))
```

В шаблоне (после блока «Форум»):

```vue
<div class="pt-3 mt-3 border-t border-moss-700/40">
  <div
    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium"
    :class="isPruvodceActive ? 'bg-moss-800 text-white' : 'text-cream-50/70'"
  >
    <BookOpen :size="18" />
    <span>{{ t('nav.pruvodce') }}</span>
  </div>
  <div class="mt-1 ml-5 pl-3 border-l border-moss-700/50 space-y-1">
    <NuxtLink
      v-for="item in pruvodceNav"
      :key="item.to"
      :to="item.to"
      class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors"
      :class="isActive(item.to)
        ? 'bg-moss-700 text-white'
        : 'text-cream-50/70 hover:bg-moss-700/60 hover:text-white'"
    >
      <component :is="item.icon" :size="15" />
      <span>{{ item.label }}</span>
    </NuxtLink>
  </div>
</div>
```

(Удали верхний пункт `pruvodce` из главного `nav`, чтобы не было дубля —
оставляем только в групповом сабменю. ИЛИ оставь только в основном nav без
сабменю — на твоё усмотрение. Рекомендация: **только групповое сабменю с
"Články" и "Kategorie"**, как у форума.)

---

## 2. i18n-ключи

В `composables/useAdminI18n.ts` добавь:

```ts
'nav.pruvodce': 'Průvodce',
'pruvodce.title': 'Wiki články',
'pruvodce.new': 'Nový článek',
'pruvodce.search': 'Hledat (title, slug, klíčové slovo)…',
'pruvodce.filter.all_categories': 'Všechny kategorie',
'pruvodce.filter.all_statuses': 'Všechny stavy',
'pruvodce.status.draft': 'Koncept',
'pruvodce.status.published': 'Publikováno',
'pruvodce.col.title': 'Článek',
'pruvodce.col.category': 'Kategorie',
'pruvodce.col.keyword': '🎯 Klíčové slovo',
'pruvodce.col.status': 'Stav',
'pruvodce.col.updated': 'Aktualizace',
'pruvodce.tab.content': 'Obsah',
'pruvodce.tab.seo': 'SEO',
'pruvodce.tab.cover': 'Obálka a meta',
'pruvodce.tab.related': 'Související',
'pruvodce.fields.category': 'Kategorie',
'pruvodce.fields.slug': 'Slug (URL)',
'pruvodce.fields.title': 'Název (H1)',
'pruvodce.fields.excerpt': 'Krátký popis (karta v katalogu)',
'pruvodce.fields.body': 'Tělo článku',
'pruvodce.fields.seo_keyword': 'Primární klíčové slovo',
'pruvodce.fields.seo_keyword_help': 'Hlavní hledaný výraz, pod který je článek zacílen.',
'pruvodce.fields.seo_secondary': 'Sekundární klíčová slova',
'pruvodce.fields.seo_volume': 'Odhadovaný měsíční objem hledání',
'pruvodce.fields.seo_intent': 'Intent',
'pruvodce.fields.meta_title': 'Meta title (override)',
'pruvodce.fields.meta_description': 'Meta description (override)',
'pruvodce.fields.cover': 'Obálka',
'pruvodce.fields.cover_alt': 'Alt obálky',
'pruvodce.fields.related': 'Související články (max 8)',
'pruvodce.actions.save': 'Uložit',
'pruvodce.actions.publish': 'Publikovat',
'pruvodce.actions.unpublish': 'Vrátit do konceptu',
'pruvodce.actions.delete': 'Smazat',
'pruvodce.actions.preview': 'Zobrazit na webu',
'pruvodce.warn.commercial': 'Pozor: ve slugu/názvu je komerční termín ({terms}). Tento článek může konkurovat /kratom/* katalogu.',
```

---

## 3. `pages/pruvodce/index.vue` — список

Структура (за основу взять `pages/lab-batches/index.vue` или
`pages/forum/topics/index.vue`):

```vue
<script setup lang="ts">
import { Plus, AlertTriangle, ExternalLink } from 'lucide-vue-next'

definePageMeta({ middleware: 'auth' })

const api = useApi()
const config = useRuntimeConfig()
const { t } = useAdminI18n()
const router = useRouter()

const search = ref('')
const categoryId = ref<number | null>(null)
const status = ref<string>('')
const page = ref(1)

const { data, refresh, pending } = await useAsyncData(
  'wiki-articles',
  () => api('/pruvodce/articles', {
    query: { q: search.value, category_id: categoryId.value, status: status.value, page: page.value },
  }),
  { watch: [search, categoryId, status, page] },
)

const { data: cats } = await useAsyncData(
  'wiki-categories',
  () => api('/pruvodce/categories'),
)
const categories = computed(() => cats.value?.data ?? [])
const articles = computed(() => data.value?.data?.data ?? [])
const meta = computed(() => data.value?.data ?? {})

function openArticle(id: number | string) {
  router.push(`/pruvodce/${id}`)
}
function previewArticle(article: any) {
  const url = `${config.public.siteBase}/pruvodce/${article.category.slug}/${article.slug}`
  window.open(url, '_blank')
}
</script>

<template>
  <div>
    <header class="flex items-center justify-between mb-5">
      <h1 class="text-2xl font-display">{{ t('pruvodce.title') }}</h1>
      <NuxtLink to="/pruvodce/new"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-moss-700 text-white text-sm hover:bg-moss-800">
        <Plus :size="16" /> {{ t('pruvodce.new') }}
      </NuxtLink>
    </header>

    <div class="grid grid-cols-12 gap-3 mb-4">
      <input v-model="search" :placeholder="t('pruvodce.search')"
        class="col-span-5 px-3 py-2 rounded-lg border border-ink-700/15 text-sm" />
      <select v-model="categoryId" class="col-span-4 px-3 py-2 rounded-lg border border-ink-700/15 text-sm">
        <option :value="null">{{ t('pruvodce.filter.all_categories') }}</option>
        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.title }}</option>
      </select>
      <select v-model="status" class="col-span-3 px-3 py-2 rounded-lg border border-ink-700/15 text-sm">
        <option value="">{{ t('pruvodce.filter.all_statuses') }}</option>
        <option value="draft">{{ t('pruvodce.status.draft') }}</option>
        <option value="published">{{ t('pruvodce.status.published') }}</option>
      </select>
    </div>

    <div class="bg-white rounded-xl border border-ink-700/10 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-ink-700/[0.03] text-ink-700/70">
          <tr>
            <th class="text-left px-4 py-2">{{ t('pruvodce.col.title') }}</th>
            <th class="text-left px-4 py-2">{{ t('pruvodce.col.category') }}</th>
            <th class="text-left px-4 py-2">{{ t('pruvodce.col.keyword') }}</th>
            <th class="text-left px-4 py-2">{{ t('pruvodce.col.status') }}</th>
            <th class="text-left px-4 py-2">{{ t('pruvodce.col.updated') }}</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in articles" :key="a.id"
            class="border-t border-ink-700/[0.06] hover:bg-ink-700/[0.02] cursor-pointer"
            @click="openArticle(a.id)">
            <td class="px-4 py-3">
              <div class="font-medium">{{ a.title }}</div>
              <div class="text-xs text-ink-700/50">/{{ a.category?.slug }}/{{ a.slug }}</div>
            </td>
            <td class="px-4 py-3">{{ a.category?.title }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <span class="text-moss-700">{{ a.seo_keyword || '—' }}</span>
                <span v-if="a.commercial_warning?.length"
                  :title="t('pruvodce.warn.commercial').replace('{terms}', a.commercial_warning.join(', '))">
                  <AlertTriangle :size="14" class="text-amber-600" />
                </span>
              </div>
            </td>
            <td class="px-4 py-3">
              <span :class="a.status === 'published'
                ? 'inline-block px-2 py-0.5 rounded text-xs bg-moss-700/10 text-moss-700'
                : 'inline-block px-2 py-0.5 rounded text-xs bg-ink-700/10 text-ink-700/60'">
                {{ t('pruvodce.status.' + a.status) }}
              </span>
            </td>
            <td class="px-4 py-3 text-ink-700/60 text-xs">
              {{ new Date(a.updated_at).toLocaleString('cs-CZ') }}
            </td>
            <td class="px-4 py-3 text-right">
              <button v-if="a.status === 'published'" @click.stop="previewArticle(a)"
                class="text-ink-700/40 hover:text-moss-700">
                <ExternalLink :size="16" />
              </button>
            </td>
          </tr>
          <tr v-if="!articles.length && !pending">
            <td colspan="6" class="px-4 py-10 text-center text-ink-700/40">Nic nenalezeno</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Простая пагинация (по образцу products/index.vue) -->
  </div>
</template>
```

---

## 4. `pages/pruvodce/[id].vue` — форма

Скелет (детали — по образцу `pages/lab-batches/[id].vue`):

```vue
<script setup lang="ts">
import { Save, Trash2, Eye, ArrowLeft, AlertTriangle } from 'lucide-vue-next'
import AdminRichEditor from '~/components/AdminRichEditor.vue'

const route = useRoute()
const router = useRouter()
const api = useApi()
const config = useRuntimeConfig()
const { t } = useAdminI18n()

const isNew = route.params.id === 'new'

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
  seo_search_intent: string
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
const categories = ref<any[]>([])
const relatedCandidates = ref<any[]>([])
const tab = ref<'content' | 'seo' | 'cover' | 'related'>('content')
const saving = ref(false)
const error = ref('')
const newSecondary = ref('')

const { data: catsData } = await useAsyncData('wiki-cats', () => api('/pruvodce/categories'))
categories.value = catsData.value?.data ?? []

if (!isNew) {
  const { data } = await useAsyncData(`wiki-article-${route.params.id}`,
    () => api(`/pruvodce/articles/${route.params.id}`))
  if (data.value?.data) hydrate(data.value.data)
}

// загрузить все опубликованные статьи как кандидатов для related
const { data: relList } = await useAsyncData('wiki-related-pool',
  () => api('/pruvodce/articles', { query: { per_page: 200, status: 'published' } }))
relatedCandidates.value = (relList.value?.data?.data ?? [])
  .filter((a: any) => String(a.id) !== String(route.params.id))

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
    seo_secondary_keywords: d.seo_secondary_keywords ?? [],
    seo_search_intent: d.seo_search_intent ?? 'informational',
    seo_volume_estimate: d.seo_volume_estimate ?? null,
    seo_meta_title: d.seo_meta_title ?? '',
    seo_meta_description: d.seo_meta_description ?? '',
    reading_time_minutes: d.reading_time_minutes ?? null,
    position: d.position ?? 0,
    status: d.status ?? 'draft',
    published_at: d.published_at ? d.published_at.slice(0, 10) : '',
    related_ids: d.related_ids ?? [],
  }
}

const commercialWarning = computed(() => {
  const bad = ['koupit', 'cena', 'levně', 'sleva', 'akce', 'nejlepší', 'doporučujeme', 'prodej']
  const hay = (form.value.slug + ' ' + form.value.title + ' ' + form.value.seo_keyword).toLowerCase()
  return bad.filter(b => hay.includes(b))
})

async function save() {
  saving.value = true
  error.value = ''
  try {
    const payload = { ...form.value }
    const url = isNew ? '/pruvodce/articles' : `/pruvodce/articles/${route.params.id}`
    const method = isNew ? 'POST' : 'PUT'
    const res = await api(url, { method, body: payload })
    if (isNew) router.push(`/pruvodce/${res.data.id}`)
    else hydrate(res.data)
  } catch (e: any) {
    error.value = e?.data?.message || 'Nepodařilo se uložit'
  } finally {
    saving.value = false
  }
}

async function togglePublish() {
  if (isNew) { await save(); return }
  const action = form.value.status === 'published' ? 'unpublish' : 'publish'
  const res = await api(`/pruvodce/articles/${route.params.id}/${action}`, { method: 'POST' })
  hydrate(res.data)
}

async function removeArticle() {
  if (!confirm('Smazat článek?')) return
  await api(`/pruvodce/articles/${route.params.id}`, { method: 'DELETE' })
  router.push('/pruvodce')
}

function previewArticle() {
  if (!original.value || form.value.status !== 'published') return
  const cat = categories.value.find(c => c.id === form.value.wiki_category_id)
  if (!cat) return
  window.open(`${config.public.siteBase}/pruvodce/${cat.slug}/${form.value.slug}`, '_blank')
}

function addSecondary() {
  const v = newSecondary.value.trim()
  if (!v) return
  form.value.seo_secondary_keywords.push(v)
  newSecondary.value = ''
}
function removeSecondary(i: number) { form.value.seo_secondary_keywords.splice(i, 1) }
</script>

<template>
  <div>
    <!-- Header с back, save, publish, preview, delete (по образцу lab-batches/[id].vue) -->
    <!-- Tabs: Obsah | SEO | Obálka a meta | Související -->

    <!-- Tab: Obsah -->
    <div v-show="tab === 'content'" class="space-y-4">
      <!-- category select, slug, title, excerpt, body (AdminRichEditor) -->
    </div>

    <!-- Tab: SEO -->
    <div v-show="tab === 'seo'" class="space-y-4">
      <div v-if="commercialWarning.length"
        class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-start gap-2">
        <AlertTriangle :size="16" class="mt-0.5" />
        <div>{{ t('pruvodce.warn.commercial').replace('{terms}', commercialWarning.join(', ')) }}</div>
      </div>

      <div>
        <label class="text-xs uppercase tracking-wide text-ink-700/60">{{ t('pruvodce.fields.seo_keyword') }}</label>
        <input v-model="form.seo_keyword" class="w-full mt-1 px-3 py-2 border rounded-lg" />
        <p class="text-xs text-ink-700/50 mt-1">{{ t('pruvodce.fields.seo_keyword_help') }}</p>
      </div>

      <div>
        <label class="text-xs uppercase tracking-wide text-ink-700/60">{{ t('pruvodce.fields.seo_secondary') }}</label>
        <div class="flex flex-wrap gap-2 mt-1">
          <span v-for="(k, i) in form.seo_secondary_keywords" :key="i"
            class="inline-flex items-center gap-1 px-2 py-1 bg-moss-700/10 text-moss-800 text-xs rounded">
            {{ k }}
            <button @click="removeSecondary(i)" class="text-moss-700/60 hover:text-moss-800">×</button>
          </span>
        </div>
        <div class="flex gap-2 mt-2">
          <input v-model="newSecondary" @keyup.enter="addSecondary" class="flex-1 px-3 py-2 border rounded-lg" />
          <button @click="addSecondary" class="px-3 py-2 bg-moss-700 text-white rounded-lg text-sm">+</button>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-xs uppercase">{{ t('pruvodce.fields.seo_volume') }}</label>
          <input v-model.number="form.seo_volume_estimate" type="number" min="0"
            class="w-full mt-1 px-3 py-2 border rounded-lg" />
        </div>
        <div>
          <label class="text-xs uppercase">{{ t('pruvodce.fields.seo_intent') }}</label>
          <select v-model="form.seo_search_intent" class="w-full mt-1 px-3 py-2 border rounded-lg">
            <option value="informational">informational</option>
            <option value="navigational">navigational</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tab: Cover & meta — обычный обложка-аплоад + meta_title + meta_description -->
    <!-- Tab: Související — multi-select из relatedCandidates, max 8, drag-reorder optional -->
  </div>
</template>
```

> **Реализовать полностью**: разметка вкладок, header (back + save +
> publish + preview + delete), обложка через FormData POST на
> `/pruvodce/articles/{id}/cover` (аналог `lab-batches/[id].vue` storeFile).
> Related — простой `multi-select` или чекбокс-список с поиском, лимит 8.

---

## 5. `pages/pruvodce/categories/index.vue`

Один экран — таблица + модал для add/edit. За образец возьми
`pages/taxonomies/index.vue` (он близкий по структуре — inline-add, edit,
reorder через position). Поля: `slug, title, eyebrow, description, icon,
accent, position, is_active`.

---

## 6. Проверка (Definition of Done)

```bash
cd src/new-kratom-admin
npm run dev
# Открыть http://localhost:3002/pruvodce
# 1) Видеть пустой список + кнопку «Nový článek»
# 2) Создать статью с категорией, slug, title, body (TipTap), seo_keyword
# 3) Сохранить — должна появиться в списке
# 4) Если slug содержит «koupit» — на вкладке SEO появляется amber-плашка ⚠️
# 5) Publish — статья получает status=published и кнопка Preview ведёт на
#    /pruvodce/{kategorie}/{slug} (фронт ещё не работает — это PHASE-04)
```

Коммит:
```
git add -A && git commit -m "pruvodce-phase-03: admin UI for wiki articles and categories"
```

Дальше — PHASE-04 (публичный фронт).
