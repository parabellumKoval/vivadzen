<script setup lang="ts">
import { Check, ExternalLink, Plus, Search, Trash2, X } from 'lucide-vue-next'

const api = useApi()
const config = useRuntimeConfig()
const { formatDateTime } = useAdminI18n()

const search = ref('')
const status = ref('')
const category = ref('')
const page = ref(1)

const { data: categoriesData } = await useAsyncData('forum-categories-filter', () => api<any>('/forum/categories'))
const categories = computed(() => categoriesData.value?.data ?? [])

const { data, pending, refresh } = await useAsyncData(
  'forum-topics',
  () => api<any>('/forum/topics', {
    params: { q: search.value, status: status.value, category: category.value, page: page.value },
  }),
  { watch: [search, status, category, page] },
)

const items = computed(() => data.value?.data?.data ?? [])
const meta = computed(() => data.value?.data ?? {})

function statusBadge(s: string) {
  if (s === 'approved') return 'bg-moss-50 text-moss-700'
  if (s === 'rejected') return 'bg-terracotta-50 text-terracotta-700'
  return 'bg-amber-50 text-amber-700'
}

function statusLabel(s: string) {
  return ({ pending: 'На модерации', approved: 'Опубликована', rejected: 'Отклонена' } as Record<string, string>)[s] ?? s
}

async function approve(id: number) {
  await api(`/forum/topics/${id}/approve`, { method: 'POST' })
  await refresh()
}

async function reject(id: number) {
  await api(`/forum/topics/${id}/reject`, { method: 'POST' })
  await refresh()
}

async function remove(id: number) {
  if (!confirm('Удалить тему вместе с ответами?')) return
  await api(`/forum/topics/${id}`, { method: 'DELETE' })
  await refresh()
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-display">Темы форума</h1>
      <NuxtLink to="/forum/topics/new" class="btn-primary">
        <Plus :size="18" /> Новая тема
      </NuxtLink>
    </div>

    <div class="card">
      <div class="flex flex-wrap items-center gap-3 mb-4">
        <Search :size="18" class="text-ink-700/50" />
        <input v-model="search" placeholder="Поиск по теме или тексту…" class="field-input max-w-sm" />
        <select v-model="status" class="field-input max-w-[190px]">
          <option value="">Все статусы</option>
          <option value="pending">На модерации</option>
          <option value="approved">Опубликованные</option>
          <option value="rejected">Отклонённые</option>
        </select>
        <select v-model="category" class="field-input max-w-[220px]">
          <option value="">Все разделы</option>
          <option v-for="c in categories" :key="c.id" :value="c.slug">{{ c.icon }} {{ c.label }}</option>
        </select>
      </div>

      <table class="w-full">
        <thead>
          <tr class="border-b border-ink-700/10">
            <th class="table-th">Тема</th>
            <th class="table-th">Автор</th>
            <th class="table-th">Раздел</th>
            <th class="table-th text-right">Ответы</th>
            <th class="table-th">Статус</th>
            <th class="table-th text-right">Активность</th>
            <th class="table-th"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="t in items" :key="t.id" class="border-b border-ink-700/5 table-row-hover">
            <td class="table-td max-w-[360px]">
              <NuxtLink :to="`/forum/topics/${t.id}`" class="font-medium text-moss-700 hover:underline">
                {{ t.emoji }} {{ t.title }}
              </NuxtLink>
              <div class="text-xs text-ink-700/50 line-clamp-1">{{ t.body }}</div>
            </td>
            <td class="table-td text-sm">
              <div>{{ t.author?.name || '—' }}</div>
              <div class="text-xs text-ink-700/50">{{ t.author?.email }}</div>
            </td>
            <td class="table-td text-sm">{{ t.category?.icon }} {{ t.category?.label || '—' }}</td>
            <td class="table-td text-right">{{ t.approved_posts_count || 0 }}</td>
            <td class="table-td">
              <span class="inline-block px-2 py-0.5 rounded text-xs font-medium" :class="statusBadge(t.status)">
                {{ statusLabel(t.status) }}
              </span>
            </td>
            <td class="table-td text-right text-xs text-ink-700/60">{{ formatDateTime(t.last_post_at || t.created_at) }}</td>
            <td class="table-td">
              <div class="flex gap-1 justify-end">
                <a v-if="t.status === 'approved'" class="btn-ghost p-1" :href="`${config.public.siteBase}/forum/tema/${t.slug}`" target="_blank">
                  <ExternalLink :size="16" />
                </a>
                <button v-if="t.status !== 'approved'" class="btn-ghost p-1" title="Одобрить" @click="approve(t.id)">
                  <Check :size="16" class="text-moss-700" />
                </button>
                <button v-if="t.status !== 'rejected'" class="btn-ghost p-1" title="Отклонить" @click="reject(t.id)">
                  <X :size="16" class="text-terracotta-700" />
                </button>
                <button class="btn-ghost p-1" title="Удалить" @click="remove(t.id)">
                  <Trash2 :size="16" class="text-ink-700/60" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!items.length && !pending">
            <td colspan="7" class="table-td text-center text-ink-700/40">Темы не найдены</td>
          </tr>
        </tbody>
      </table>

      <div class="flex items-center justify-between mt-4 text-sm text-ink-700/60">
        <span>{{ meta.from || 0 }}–{{ meta.to || 0 }} из {{ meta.total || 0 }}</span>
        <div class="flex gap-2">
          <button class="btn-ghost" :disabled="page <= 1" @click="page--">← Назад</button>
          <button class="btn-ghost" :disabled="page >= (meta.last_page || 1)" @click="page++">Далее →</button>
        </div>
      </div>
    </div>
  </div>
</template>
