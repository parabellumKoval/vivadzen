<script setup lang="ts">
import { Upload, Trash2, Star, StarOff, GripVertical, Loader2 } from 'lucide-vue-next'
import draggable from 'vuedraggable'

type GalleryImage = {
  id: number
  disk: string
  path: string
  url: string
  alt: string | null
  title: string | null
  position: number
}

const props = defineProps<{
  productId: number | string
  mainImage?: string
}>()

const emit = defineEmits<{
  (e: 'update:mainImage', url: string): void
}>()

const api = useApi()
const { resolve: resolveMediaUrl } = useMediaUrl()

const images = ref<GalleryImage[]>([])
const uploading = ref(false)
const error = ref('')
const dragOver = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)

async function load() {
  try {
    const res = await api<any>(`/products/${props.productId}/images`)
    images.value = res.data ?? []
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось загрузить галерею'
  }
}

async function onFiles(files: FileList | File[] | null) {
  if (!files || (files as FileList).length === 0) return
  const list = Array.from(files as FileList).filter((f) => f.type.startsWith('image/'))
  if (!list.length) return

  const form = new FormData()
  list.forEach((file) => form.append('files[]', file))

  uploading.value = true
  error.value = ''
  try {
    const res = await api<any>(`/products/${props.productId}/images`, {
      method: 'POST',
      body: form,
    })
    images.value = [...images.value, ...(res.data ?? [])]
  } catch (e: any) {
    error.value = e?.data?.message || 'Загрузка не удалась'
  } finally {
    uploading.value = false
    if (fileInput.value) fileInput.value.value = ''
  }
}

function onDrop(event: DragEvent) {
  event.preventDefault()
  dragOver.value = false
  onFiles(event.dataTransfer?.files ?? null)
}

async function saveMeta(image: GalleryImage) {
  try {
    await api(`/products/${props.productId}/images/${image.id}`, {
      method: 'PUT',
      body: { alt: image.alt, title: image.title },
    })
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось сохранить'
  }
}

async function remove(image: GalleryImage) {
  if (!confirm('Удалить изображение?')) return
  try {
    await api(`/products/${props.productId}/images/${image.id}`, { method: 'DELETE' })
    images.value = images.value.filter((i) => i.id !== image.id)
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось удалить'
  }
}

async function persistOrder() {
  try {
    const order = images.value.map((i) => i.id)
    const res = await api<any>(`/products/${props.productId}/images/reorder`, {
      method: 'PUT',
      body: { order },
    })
    images.value = res.data ?? images.value
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось сохранить порядок'
  }
}

function setMain(image: GalleryImage) {
  emit('update:mainImage', image.url)
}

const isMain = (image: GalleryImage) => props.mainImage === image.url

onMounted(load)
</script>

<template>
  <div class="space-y-3">
    <div class="flex items-center justify-between">
      <h2 class="font-display text-lg">Галерея</h2>
      <div class="text-xs text-ink-700/60">{{ images.length }} фото</div>
    </div>

    <div
      class="border-2 border-dashed rounded-lg p-4 text-center transition-colors cursor-pointer"
      :class="dragOver ? 'border-moss-700 bg-moss-700/5' : 'border-ink-700/15 hover:border-moss-300'"
      @click="fileInput?.click()"
      @dragover.prevent="dragOver = true"
      @dragleave.prevent="dragOver = false"
      @drop="onDrop"
    >
      <input
        ref="fileInput"
        type="file"
        class="hidden"
        accept="image/*"
        multiple
        @change="onFiles(($event.target as HTMLInputElement).files)"
      />
      <div class="flex items-center justify-center gap-2 text-sm text-ink-700/80">
        <Loader2 v-if="uploading" :size="18" class="animate-spin" />
        <Upload v-else :size="18" />
        <span>{{ uploading ? 'Загрузка…' : 'Перетащите файлы или нажмите для выбора (можно несколько)' }}</span>
      </div>
    </div>

    <div v-if="error" class="text-sm text-terracotta-700">{{ error }}</div>

    <draggable
      v-model="images"
      item-key="id"
      handle=".drag-handle"
      class="grid grid-cols-1 sm:grid-cols-2 gap-3"
      :animation="150"
      @end="persistOrder"
    >
      <template #item="{ element: image }">
        <div class="border border-ink-700/10 rounded-lg p-3 flex gap-3 items-start bg-white">
          <button class="drag-handle cursor-grab text-ink-700/40 hover:text-ink-700 pt-1" type="button">
            <GripVertical :size="18" />
          </button>

          <img
            :src="resolveMediaUrl(image.url)"
            :alt="image.alt || ''"
            :title="image.title || ''"
            class="w-24 h-24 object-cover rounded-md border border-ink-700/10 flex-shrink-0"
          />

          <div class="flex-1 min-w-0 space-y-2">
            <div>
              <label class="field-label">Alt</label>
              <input
                v-model="image.alt"
                class="field-input"
                placeholder="Описание для поисковых систем"
                @change="saveMeta(image)"
              />
            </div>
            <div>
              <label class="field-label">Title</label>
              <input
                v-model="image.title"
                class="field-input"
                placeholder="Подсказка при наведении"
                @change="saveMeta(image)"
              />
            </div>
            <div class="flex items-center justify-between text-xs text-ink-700/60">
              <span>{{ image.disk }} · {{ image.path.split('/').pop() }}</span>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="inline-flex items-center gap-1 text-xs"
                  :class="isMain(image) ? 'text-amber-600' : 'text-ink-700/60 hover:text-moss-700'"
                  @click="setMain(image)"
                >
                  <component :is="isMain(image) ? Star : StarOff" :size="14" />
                  Главная
                </button>
                <button class="btn-danger !px-2 !py-1" type="button" @click="remove(image)">
                  <Trash2 :size="14" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </template>
    </draggable>
  </div>
</template>
