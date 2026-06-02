<script setup lang="ts">
import { Upload, X, Loader2, Search, Check } from 'lucide-vue-next'

type MediaItem = {
  id: number
  disk: string
  path: string
  url?: string
  filename: string
  mime: string
  size: number
  alt?: string | null
}

const props = defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'pick', url: string, item: MediaItem): void
}>()

const api = useApi()
const { resolve: resolveMediaUrl } = useMediaUrl()

const items = ref<MediaItem[]>([])
const loading = ref(false)
const uploading = ref(false)
const error = ref('')
const search = ref('')
const selectedId = ref<number | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return items.value
  return items.value.filter((m) => m.filename.toLowerCase().includes(q) || (m.alt || '').toLowerCase().includes(q))
})

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await api<any>('/media', { params: { per_page: 200 } })
    items.value = res?.data?.data ?? []
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось загрузить медиа'
  } finally {
    loading.value = false
  }
}

async function upload(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return
  uploading.value = true
  error.value = ''
  try {
    const fd = new FormData()
    fd.append('file', file)
    const res = await api<any>('/media', { method: 'POST', body: fd })
    const created = res?.data
    if (created) {
      items.value = [created, ...items.value]
      selectedId.value = created.id
    }
  } catch (e: any) {
    error.value = e?.data?.message || 'Загрузка не удалась'
  } finally {
    uploading.value = false
    if (fileInput.value) fileInput.value.value = ''
  }
}

function itemUrl(m: MediaItem) {
  return m.url || `/storage/${m.path}`
}

function pickItem(m: MediaItem) {
  selectedId.value = m.id
}

function confirm() {
  const m = items.value.find((i) => i.id === selectedId.value)
  if (!m) return
  emit('pick', resolveMediaUrl(itemUrl(m)), m)
  close()
}

function close() {
  emit('update:modelValue', false)
}

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      selectedId.value = null
      search.value = ''
      load()
    }
  },
)
</script>

<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-[1000] flex items-center justify-center bg-ink-700/40 p-4"
      @click.self="close"
    >
      <div class="bg-white rounded-xl shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-ink-700/10">
          <h3 class="font-display text-lg">Медиа-библиотека</h3>
          <button class="btn-ghost" type="button" @click="close"><X :size="18" /></button>
        </div>

        <div class="flex items-center gap-2 px-4 py-2 border-b border-ink-700/10 bg-ink-700/[0.02]">
          <div class="relative flex-1">
            <Search :size="16" class="absolute left-2 top-1/2 -translate-y-1/2 text-ink-700/40" />
            <input v-model="search" class="field-input pl-8" placeholder="Поиск по имени файла или alt…" />
          </div>
          <label class="btn-primary cursor-pointer">
            <Loader2 v-if="uploading" :size="16" class="animate-spin" />
            <Upload v-else :size="16" />
            {{ uploading ? 'Загрузка…' : 'Загрузить' }}
            <input
              ref="fileInput"
              type="file"
              accept="image/*"
              class="hidden"
              :disabled="uploading"
              @change="upload"
            />
          </label>
        </div>

        <div v-if="error" class="mx-4 mt-2 text-sm text-terracotta-700">{{ error }}</div>

        <div class="flex-1 overflow-y-auto p-4">
          <div v-if="loading" class="flex items-center justify-center py-12 text-ink-700/60">
            <Loader2 :size="20" class="animate-spin mr-2" /> Загрузка…
          </div>
          <div v-else-if="!filtered.length" class="text-center text-ink-700/40 py-12">
            Ничего не найдено
          </div>
          <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            <button
              v-for="m in filtered"
              :key="m.id"
              type="button"
              class="relative border rounded-lg overflow-hidden bg-white text-left transition-all hover:shadow-md"
              :class="selectedId === m.id ? 'border-moss-700 ring-2 ring-moss-700/30' : 'border-ink-700/10'"
              @click="pickItem(m)"
              @dblclick="pickItem(m); confirm()"
            >
              <img :src="resolveMediaUrl(itemUrl(m))" :alt="m.alt || ''" class="w-full h-32 object-cover" />
              <div class="absolute top-1 right-1 w-6 h-6 rounded-full bg-white/90 flex items-center justify-center text-moss-700"
                v-if="selectedId === m.id">
                <Check :size="14" />
              </div>
              <div class="px-2 py-1.5 text-xs text-ink-700/70 truncate">{{ m.filename }}</div>
            </button>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 px-4 py-3 border-t border-ink-700/10 bg-ink-700/[0.02]">
          <button class="btn-ghost" type="button" @click="close">Отмена</button>
          <button class="btn-primary" type="button" :disabled="selectedId === null" @click="confirm">
            Вставить
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
