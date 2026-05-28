<script setup lang="ts">
import { Plus, Search, Trash2, Check, X } from 'lucide-vue-next'

const api = useApi()
const { formatDateTime } = useAdminI18n()
const search = ref('')
const status = ref('')
const page = ref(1)

const { data, pending, refresh } = await useAsyncData(
  'questions',
  () => api<any>('/questions', {
    params: { q: search.value, status: status.value, page: page.value },
  }),
  { watch: [search, status, page] },
)

const items = computed(() => data.value?.data?.data ?? [])
const meta = computed(() => data.value?.data ?? {})

async function approve(id: number) {
  await api(`/questions/${id}/approve`, { method: 'POST' })
  await refresh()
}

async function reject(id: number) {
  await api(`/questions/${id}/reject`, { method: 'POST' })
  await refresh()
}

async function remove(id: number) {
  if (!confirm('Удалить вопрос?')) return
  await api(`/questions/${id}`, { method: 'DELETE' })
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
      <h1 class="text-2xl font-display">Вопросы и ответы</h1>
      <NuxtLink to="/questions/new" class="btn-primary">
        <Plus :size="18" />Новый вопрос
      </NuxtLink>
    </div>

    <div class="card">
      <div class="flex flex-wrap items-center gap-3 mb-4">
        <Search :size="18" class="text-ink-700/50" />
        <input v-model="search" placeholder="Поиск по автору, вопросу, ответу…" class="field-input max-w-sm" />

        <select v-model="status" class="field-input max-w-[200px]">
          <option value="">Все статусы</option>
          <option value="pending">На модерации</option>
          <option value="unanswered">Без ответа</option>
          <option value="approved">Опубликованные</option>
          <option value="scheduled">Запланированные</option>
          <option value="rejected">Отклонённые</option>
        </select>
      </div>

      <table class="w-full">
        <thead>
          <tr class="border-b border-ink-700/10">
            <th class="table-th">Товар</th>
            <th class="table-th">Автор</th>
            <th class="table-th">Вопрос</th>
            <th class="table-th">Ответ</th>
            <th class="table-th">Статус</th>
            <th class="table-th text-right">Публикация</th>
            <th class="table-th"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="q in items" :key="q.id" class="border-b border-ink-700/5 table-row-hover">
            <td class="table-td font-mono text-xs">
              <NuxtLink :to="`/questions/${q.id}`" class="text-moss-700 hover:underline">{{ q.product?.slug || '—' }}</NuxtLink>
            </td>
            <td class="table-td">{{ q.author_name }}</td>
            <td class="table-td max-w-[260px]">
              <div class="line-clamp-2 text-sm text-ink-700">{{ q.question }}</div>
            </td>
            <td class="table-td max-w-[260px]">
              <div v-if="q.answer" class="line-clamp-2 text-sm text-moss-700">{{ q.answer }}</div>
              <span v-else class="text-xs text-amber-700">Нет ответа</span>
            </td>
            <td class="table-td">
              <span class="inline-block px-2 py-0.5 rounded text-xs font-medium" :class="statusBadge(q.status)">
                {{ statusLabel(q.status) }}
              </span>
            </td>
            <td class="table-td text-right text-xs text-ink-700/60">{{ q.published_at ? formatDateTime(q.published_at) : '—' }}</td>
            <td class="table-td">
              <div class="flex gap-1 justify-end">
                <button v-if="q.status !== 'approved'" class="btn-ghost p-1" title="Одобрить" @click="approve(q.id)">
                  <Check :size="16" class="text-moss-700" />
                </button>
                <button v-if="q.status !== 'rejected'" class="btn-ghost p-1" title="Отклонить" @click="reject(q.id)">
                  <X :size="16" class="text-terracotta-700" />
                </button>
                <button class="btn-ghost p-1" title="Удалить" @click="remove(q.id)">
                  <Trash2 :size="16" class="text-ink-700/60" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!items.length && !pending">
            <td colspan="7" class="table-td text-center text-ink-700/40">Вопросов нет</td>
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
