<script setup lang="ts">
import { Upload, Trash2 } from 'lucide-vue-next'

const api = useApi()
const config = useRuntimeConfig()
const { t } = useAdminI18n()
const { data, refresh } = await useAsyncData('media', () => api<any>('/media'))
const items = computed(() => data.value?.data?.data ?? [])

const uploading = ref(false)
async function upload(e: Event) {
  const input = e.target as HTMLInputElement
  if (!input.files?.[0]) return
  uploading.value = true
  try {
    const fd = new FormData()
    fd.append('file', input.files[0])
    await api('/media', { method: 'POST', body: fd })
    await refresh()
  } finally {
    uploading.value = false
    input.value = ''
  }
}

async function remove(id: number) {
  if (!confirm(t('media.delete_confirm'))) return
  await api(`/media/${id}`, { method: 'DELETE' })
  await refresh()
}

function url(path: string) {
  return path.startsWith('http') ? path : `${config.public.siteBase}/storage/${path}`
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-display">{{ t('media.title') }}</h1>
      <label class="btn-primary cursor-pointer">
        <Upload :size="16" />{{ uploading ? t('media.uploading') : t('media.upload') }}
        <input type="file" accept="image/*" class="hidden" :disabled="uploading" @change="upload" />
      </label>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
      <div v-for="m in items" :key="m.id" class="card !p-2 group relative">
        <img :src="url(m.path)" :alt="m.alt" class="w-full h-32 object-cover rounded-lg" />
        <div class="mt-2 text-xs text-ink-700/70 truncate">{{ m.filename }}</div>
        <div class="text-[10px] text-ink-700/50">{{ (m.size / 1024).toFixed(0) }} KB</div>
        <button
          class="absolute top-1 right-1 p-1 bg-white/90 rounded-md opacity-0 group-hover:opacity-100 text-terracotta-700"
          @click="remove(m.id)"
        ><Trash2 :size="14" /></button>
      </div>
      <div v-if="!items.length" class="col-span-full text-center text-ink-700/40 py-12">
        {{ t('media.empty') }}
      </div>
    </div>
  </div>
</template>
