<script setup lang="ts">
import { Plus, Trash2, X } from 'lucide-vue-next'

const api = useApi()
const { t } = useAdminI18n()

const ACCENTS = ['grass', 'terra', 'amber', 'cream', 'moss']

type CategoryForm = {
  id?: number
  slug: string
  title: string
  eyebrow: string
  description: string
  icon: string
  accent: string
  position: number
  is_active: boolean
}

function emptyForm(): CategoryForm {
  return {
    slug: '',
    title: '',
    eyebrow: '',
    description: '',
    icon: '',
    accent: '',
    position: 0,
    is_active: true,
  }
}

const { data, refresh, pending } = await useAsyncData('wiki-categories-admin',
  () => api<any>('/pruvodce/categories'))
const items = computed<any[]>(() => data.value?.data ?? [])

const editing = ref<CategoryForm | null>(null)
const saving = ref(false)
const error = ref('')

function startNew() {
  editing.value = emptyForm()
  error.value = ''
}
function startEdit(item: any) {
  editing.value = {
    id: item.id,
    slug: item.slug ?? '',
    title: item.title ?? '',
    eyebrow: item.eyebrow ?? '',
    description: item.description ?? '',
    icon: item.icon ?? '',
    accent: item.accent ?? '',
    position: item.position ?? 0,
    is_active: item.is_active ?? true,
  }
  error.value = ''
}
function closeModal() {
  editing.value = null
}

async function save() {
  if (!editing.value) return
  saving.value = true
  error.value = ''
  try {
    const payload = {
      slug: editing.value.slug,
      title: editing.value.title,
      eyebrow: editing.value.eyebrow || null,
      description: editing.value.description || null,
      icon: editing.value.icon || null,
      accent: editing.value.accent || null,
      position: editing.value.position,
      is_active: editing.value.is_active,
    }
    if (editing.value.id) {
      await api(`/pruvodce/categories/${editing.value.id}`, { method: 'PUT', body: payload })
    } else {
      await api('/pruvodce/categories', { method: 'POST', body: payload })
    }
    closeModal()
    await refresh()
  } catch (e: any) {
    error.value = e?.data?.message || 'Nepodařilo se uložit'
  } finally {
    saving.value = false
  }
}

async function remove(item: any) {
  if (!confirm(t('pruvodce.categories.delete_confirm'))) return
  try {
    await api(`/pruvodce/categories/${item.id}`, { method: 'DELETE' })
    await refresh()
  } catch (e: any) {
    error.value = e?.data?.message || 'Nepodařilo se smazat'
  }
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-display">{{ t('pruvodce.categories.title') }}</h1>
      <button class="btn-primary" @click="startNew">
        <Plus :size="18" /> {{ t('pruvodce.categories.add') }}
      </button>
    </div>

    <div v-if="error && !editing" class="card border-terracotta-500 text-terracotta-700">{{ error }}</div>

    <div class="card">
      <table class="w-full">
        <thead>
          <tr class="border-b border-ink-700/10">
            <th class="table-th w-16">{{ t('pruvodce.categories.position') }}</th>
            <th class="table-th">{{ t('pruvodce.categories.name') }}</th>
            <th class="table-th">{{ t('pruvodce.categories.slug') }}</th>
            <th class="table-th">{{ t('pruvodce.categories.accent') }}</th>
            <th class="table-th text-right">{{ t('pruvodce.categories.articles_count') }}</th>
            <th class="table-th">{{ t('pruvodce.categories.is_active') }}</th>
            <th class="table-th"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in items" :key="c.id" class="border-b border-ink-700/5 table-row-hover">
            <td class="table-td text-xs text-ink-700/50">{{ c.position }}</td>
            <td class="table-td">
              <button class="font-medium text-moss-700 hover:underline text-left" @click="startEdit(c)">
                {{ c.icon }} {{ c.title }}
              </button>
              <div v-if="c.eyebrow" class="text-xs text-ink-700/50">{{ c.eyebrow }}</div>
            </td>
            <td class="table-td font-mono text-xs">{{ c.slug }}</td>
            <td class="table-td text-xs">{{ c.accent || '—' }}</td>
            <td class="table-td text-right text-sm">{{ c.articles_count ?? 0 }}</td>
            <td class="table-td">
              <span
                class="inline-block px-2 py-0.5 rounded text-xs"
                :class="c.is_active ? 'bg-moss-50 text-moss-700' : 'bg-ink-700/10 text-ink-700/60'"
              >
                {{ c.is_active ? 'on' : 'off' }}
              </span>
            </td>
            <td class="table-td text-right">
              <button class="btn-ghost p-1 text-terracotta-700" @click="remove(c)">
                <Trash2 :size="16" />
              </button>
            </td>
          </tr>
          <tr v-if="!items.length && !pending">
            <td colspan="7" class="table-td text-center text-ink-700/40">
              {{ t('pruvodce.categories.empty') }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      v-if="editing"
      class="fixed inset-0 bg-ink-900/60 flex items-center justify-center p-4 z-50"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-2xl p-6 w-full max-w-2xl space-y-4 max-h-[90vh] overflow-auto">
        <div class="flex items-center justify-between gap-4">
          <h3 class="font-display text-xl">
            {{ editing.id ? t('pruvodce.categories.edit') : t('pruvodce.categories.add') }}
          </h3>
          <button class="btn-ghost" @click="closeModal"><X :size="18" /></button>
        </div>

        <div v-if="error" class="text-sm text-terracotta-700">{{ error }}</div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="field-label">{{ t('pruvodce.categories.slug') }} *</label>
            <input v-model="editing.slug" class="field-input font-mono" placeholder="botanika-a-veda" />
          </div>
          <div>
            <label class="field-label">{{ t('pruvodce.categories.position') }}</label>
            <input v-model.number="editing.position" type="number" class="field-input" />
          </div>
        </div>

        <div>
          <label class="field-label">{{ t('pruvodce.categories.name') }} *</label>
          <input v-model="editing.title" class="field-input" placeholder="Botanika a věda" />
        </div>

        <div>
          <label class="field-label">{{ t('pruvodce.categories.eyebrow') }}</label>
          <input v-model="editing.eyebrow" class="field-input" placeholder="Vědecká encyklopedie" />
        </div>

        <div>
          <label class="field-label">{{ t('pruvodce.categories.description') }}</label>
          <textarea v-model="editing.description" rows="4" class="field-input" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="field-label">{{ t('pruvodce.categories.icon') }}</label>
            <input v-model="editing.icon" class="field-input" placeholder="🌿" />
          </div>
          <div>
            <label class="field-label">{{ t('pruvodce.categories.accent') }}</label>
            <select v-model="editing.accent" class="field-input">
              <option value="">—</option>
              <option v-for="a in ACCENTS" :key="a" :value="a">{{ a }}</option>
            </select>
          </div>
        </div>

        <label class="flex items-center gap-2 text-sm">
          <input v-model="editing.is_active" type="checkbox" />
          {{ t('pruvodce.categories.is_active') }}
        </label>

        <div class="flex justify-end gap-2 pt-2">
          <button class="btn-ghost" @click="closeModal">{{ t('pruvodce.categories.cancel') }}</button>
          <button class="btn-primary" :disabled="saving" @click="save">
            {{ t('pruvodce.categories.save') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
