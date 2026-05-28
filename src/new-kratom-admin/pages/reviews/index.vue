<script setup lang="ts">
import { Plus, Search, Trash2, Check, X, Star, Image as ImageIcon } from 'lucide-vue-next'

const api = useApi()
const { t, formatDateTime } = useAdminI18n()
const search = ref('')
const status = ref('')
const ratingFilter = ref('')
const page = ref(1)

const { data, pending, refresh } = await useAsyncData(
  'reviews',
  () => api<any>('/reviews', {
    params: {
      q: search.value,
      status: status.value,
      rating: ratingFilter.value,
      page: page.value,
    },
  }),
  { watch: [search, status, ratingFilter, page] },
)

const items = computed(() => data.value?.data?.data ?? [])
const meta = computed(() => data.value?.data ?? {})

async function approve(id: number) {
  await api(`/reviews/${id}/approve`, { method: 'POST' })
  await refresh()
}

async function reject(id: number) {
  await api(`/reviews/${id}/reject`, { method: 'POST' })
  await refresh()
}

async function remove(id: number) {
  if (!confirm('Удалить отзыв?')) return
  await api(`/reviews/${id}`, { method: 'DELETE' })
  await refresh()
}

function statusBadge(s: string) {
  if (s === 'approved') return 'bg-moss-50 text-moss-700'
  if (s === 'rejected') return 'bg-terracotta-50 text-terracotta-700'
  return 'bg-amber-50 text-amber-700'
}

function statusLabel(s: string) {
  return ({ pending: 'На модерации', approved: 'Опубликован', rejected: 'Отклонён' } as Record<string,string>)[s] ?? s
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-display">Отзывы</h1>
      <NuxtLink to="/reviews/new" class="btn-primary">
        <Plus :size="18" />Новый отзыв
      </NuxtLink>
    </div>

    <div class="card">
      <div class="flex flex-wrap items-center gap-3 mb-4">
        <Search :size="18" class="text-ink-700/50" />
        <input v-model="search" placeholder="Поиск по автору или тексту…" class="field-input max-w-sm" />

        <select v-model="status" class="field-input max-w-[180px]">
          <option value="">Все статусы</option>
          <option value="pending">На модерации</option>
          <option value="approved">Опубликованные</option>
          <option value="scheduled">Запланированные</option>
          <option value="rejected">Отклонённые</option>
        </select>

        <select v-model="ratingFilter" class="field-input max-w-[120px]">
          <option value="">Все оценки</option>
          <option v-for="n in 5" :key="n" :value="n">{{ n }} ★</option>
        </select>
      </div>

      <table class="w-full">
        <thead>
          <tr class="border-b border-ink-700/10">
            <th class="table-th">Товар</th>
            <th class="table-th">Автор</th>
            <th class="table-th">Оценка</th>
            <th class="table-th">Текст</th>
            <th class="table-th">Фото</th>
            <th class="table-th">Статус</th>
            <th class="table-th text-right">Публикация</th>
            <th class="table-th"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in items" :key="r.id" class="border-b border-ink-700/5 table-row-hover">
            <td class="table-td font-mono text-xs">
              <NuxtLink :to="`/reviews/${r.id}`" class="text-moss-700 hover:underline">{{ r.product?.slug || '—' }}</NuxtLink>
            </td>
            <td class="table-td">
              <div class="font-medium">{{ r.author_name }}</div>
              <div v-if="r.verified_purchase" class="text-xs text-moss-700">✓ Верифицированный</div>
            </td>
            <td class="table-td">
              <div class="flex items-center gap-1 text-amber-500">
                <Star v-for="i in 5" :key="i" :size="14" :fill="i <= r.rating ? 'currentColor' : 'none'" />
              </div>
            </td>
            <td class="table-td max-w-[320px]">
              <div class="line-clamp-2 text-sm text-ink-700">{{ r.body }}</div>
            </td>
            <td class="table-td">
              <span v-if="r.images && r.images.length" class="inline-flex items-center gap-1 text-ink-700/70 text-sm">
                <ImageIcon :size="14" /> {{ r.images.length }}
              </span>
              <span v-else class="text-ink-700/30">—</span>
            </td>
            <td class="table-td">
              <span class="inline-block px-2 py-0.5 rounded text-xs font-medium" :class="statusBadge(r.status)">
                {{ statusLabel(r.status) }}
              </span>
            </td>
            <td class="table-td text-right text-xs text-ink-700/60">{{ r.published_at ? formatDateTime(r.published_at) : '—' }}</td>
            <td class="table-td">
              <div class="flex gap-1 justify-end">
                <button v-if="r.status !== 'approved'" class="btn-ghost p-1" title="Одобрить" @click="approve(r.id)">
                  <Check :size="16" class="text-moss-700" />
                </button>
                <button v-if="r.status !== 'rejected'" class="btn-ghost p-1" title="Отклонить" @click="reject(r.id)">
                  <X :size="16" class="text-terracotta-700" />
                </button>
                <button class="btn-ghost p-1" title="Удалить" @click="remove(r.id)">
                  <Trash2 :size="16" class="text-ink-700/60" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!items.length && !pending">
            <td colspan="8" class="table-td text-center text-ink-700/40">Отзывы не найдены</td>
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
