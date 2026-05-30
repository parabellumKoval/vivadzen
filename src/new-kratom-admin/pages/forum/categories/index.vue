<script setup lang="ts">
import { Plus, Save, Trash2 } from 'lucide-vue-next'

const api = useApi()

const { data, refresh } = await useAsyncData('forum-categories', () => api<any>('/forum/categories'))
const items = computed(() => data.value?.data ?? [])

const editing = ref<any | null>(null)
const saving = ref(false)
const error = ref('')

function blank() {
  return { id: null, label: '', slug: '', icon: '💬', description: '', position: 0, is_active: true }
}

function edit(item: any = null) {
  editing.value = item ? { ...item } : blank()
  error.value = ''
}

async function save() {
  if (!editing.value || saving.value) return
  saving.value = true
  error.value = ''
  try {
    const body = { ...editing.value }
    if (body.id) {
      await api(`/forum/categories/${body.id}`, { method: 'PUT', body })
    } else {
      await api('/forum/categories', { method: 'POST', body })
    }
    editing.value = null
    await refresh()
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось сохранить раздел'
  } finally {
    saving.value = false
  }
}

async function remove(item: any) {
  if (!confirm(`Удалить раздел "${item.label}"? Темы останутся без раздела.`)) return
  await api(`/forum/categories/${item.id}`, { method: 'DELETE' })
  await refresh()
}
</script>

<template>
  <div class="space-y-4 max-w-5xl">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-display">Разделы форума</h1>
      <button class="btn-primary" @click="edit()"><Plus :size="16" /> Новый раздел</button>
    </div>

    <div v-if="editing" class="card space-y-4">
      <h2 class="font-display text-xl">{{ editing.id ? 'Редактирование раздела' : 'Новый раздел' }}</h2>
      <div v-if="error" class="text-terracotta-700 text-sm">{{ error }}</div>
      <div class="grid grid-cols-1 md:grid-cols-[90px_1fr_1fr_120px] gap-3">
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Icon</span>
          <input v-model="editing.icon" class="field-input mt-1" maxlength="16" />
        </label>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Название</span>
          <input v-model="editing.label" class="field-input mt-1" maxlength="120" />
        </label>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Slug</span>
          <input v-model="editing.slug" class="field-input mt-1" maxlength="64" placeholder="auto-from-title" />
        </label>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Позиция</span>
          <input v-model.number="editing.position" type="number" min="0" class="field-input mt-1" />
        </label>
      </div>
      <label class="block">
        <span class="text-xs uppercase tracking-wide text-ink-700/60">Описание</span>
        <textarea v-model="editing.description" rows="3" class="field-input mt-1" />
      </label>
      <label class="flex items-center gap-2">
        <input v-model="editing.is_active" type="checkbox" />
        <span class="text-sm">Активен на сайте</span>
      </label>
      <div class="flex gap-2">
        <button class="btn-primary" :disabled="saving" @click="save"><Save :size="16" /> {{ saving ? 'Сохранение…' : 'Сохранить' }}</button>
        <button class="btn-ghost" @click="editing = null">Отмена</button>
      </div>
    </div>

    <div class="card">
      <table class="w-full">
        <thead>
          <tr class="border-b border-ink-700/10">
            <th class="table-th">Раздел</th>
            <th class="table-th">Slug</th>
            <th class="table-th text-right">Темы</th>
            <th class="table-th text-right">Позиция</th>
            <th class="table-th">Статус</th>
            <th class="table-th"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in items" :key="c.id" class="border-b border-ink-700/5 table-row-hover">
            <td class="table-td">
              <button class="font-medium text-moss-700 hover:underline" @click="edit(c)">{{ c.icon }} {{ c.label }}</button>
              <div class="text-xs text-ink-700/50 line-clamp-1">{{ c.description || '—' }}</div>
            </td>
            <td class="table-td font-mono text-xs">{{ c.slug }}</td>
            <td class="table-td text-right">{{ c.topics_count || 0 }}</td>
            <td class="table-td text-right">{{ c.position }}</td>
            <td class="table-td">
              <span v-if="c.is_active" class="px-2 py-0.5 rounded-full bg-moss-50 text-moss-700 text-xs">Активен</span>
              <span v-else class="px-2 py-0.5 rounded-full bg-ink-700/10 text-ink-700/60 text-xs">Скрыт</span>
            </td>
            <td class="table-td">
              <button class="btn-ghost p-1 text-terracotta-700" @click="remove(c)"><Trash2 :size="16" /></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
