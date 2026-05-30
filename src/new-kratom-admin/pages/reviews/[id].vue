<script setup lang="ts">
import { ArrowLeft, Trash2, Star, X as XIcon } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const api = useApi()
const { pickLocalizedValue } = useAdminI18n()

const id = route.params.id as string
const isNew = id === 'new'

const products = ref<any[]>([])
const photos = ref<File[]>([])
const photoPreviews = ref<string[]>([])
const saving = ref(false)
const error = ref('')

const form = ref<any>({
  product_id: null,
  author_name: '',
  author_email: '',
  rating: 5,
  body: '',
  verified_purchase: false,
  helpful_count: 0,
  status: 'approved',
  published_at: '',
  images: [],
})

async function loadProducts() {
  const res = await api<any>('/products', { params: { per_page: 200 } })
  products.value = (res?.data?.data ?? []).map((p: any) => ({
    id: p.id,
    slug: p.slug,
    name: pickLocalizedValue(p.name),
  }))
}

async function loadReview() {
  if (isNew) return
  const res = await api<any>(`/reviews/${id}`)
  const r = res?.data ?? {}
  form.value = {
    product_id: r.product_id,
    author_name: r.author_name,
    author_email: r.author_email || '',
    rating: r.rating,
    body: r.body,
    verified_purchase: !!r.verified_purchase,
    helpful_count: r.helpful_count || 0,
    status: r.status,
    published_at: r.published_at ? r.published_at.slice(0, 16) : '',
    images: r.images || [],
  }
}

await loadProducts()
await loadReview()

function handlePhotos(e: Event) {
  const target = e.target as HTMLInputElement
  if (!target.files) return
  photos.value = Array.from(target.files).slice(0, 3)
  photoPreviews.value.forEach((url) => URL.revokeObjectURL(url))
  photoPreviews.value = photos.value.map((f) => URL.createObjectURL(f))
}

function removePhotoSelection(i: number) {
  const preview = photoPreviews.value[i]
  if (preview) URL.revokeObjectURL(preview)
  photos.value.splice(i, 1)
  photoPreviews.value.splice(i, 1)
}

async function deleteExistingPhoto(imageId: number) {
  if (!confirm('Удалить фото?')) return
  await api(`/reviews/${id}/photos/${imageId}`, { method: 'DELETE' })
  form.value.images = form.value.images.filter((img: any) => img.id !== imageId)
}

async function save() {
  if (saving.value) return
  error.value = ''
  saving.value = true
  try {
    const fd = new FormData()
    if (form.value.product_id) fd.append('product_id', String(form.value.product_id))
    fd.append('author_name', form.value.author_name || '')
    fd.append('author_email', form.value.author_email || '')
    fd.append('rating', String(form.value.rating || 5))
    fd.append('body', form.value.body || '')
    fd.append('verified_purchase', form.value.verified_purchase ? '1' : '0')
    fd.append('helpful_count', String(form.value.helpful_count || 0))
    fd.append('status', form.value.status || 'pending')
    if (form.value.published_at) fd.append('published_at', form.value.published_at)
    photos.value.forEach((f) => fd.append('photos[]', f))

    const url = isNew ? '/reviews' : `/reviews/${id}`
    const res = await api<any>(url, { method: 'POST', body: fd })
    const savedId = res?.data?.id ?? id
    router.push(`/reviews/${savedId}`)
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось сохранить'
  } finally {
    saving.value = false
  }
}

async function remove() {
  if (!confirm('Удалить отзыв?')) return
  await api(`/reviews/${id}`, { method: 'DELETE' })
  router.push('/reviews')
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-3">
      <NuxtLink to="/reviews" class="btn-ghost"><ArrowLeft :size="16" /> Назад</NuxtLink>
      <h1 class="text-2xl font-display">{{ isNew ? 'Новый отзыв' : 'Редактирование отзыва' }}</h1>
    </div>

    <div v-if="error" class="card border-l-4 border-terracotta-700 bg-terracotta-50 text-terracotta-700">{{ error }}</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="card lg:col-span-2 space-y-4">
        <h2 class="font-display text-xl">Содержание</h2>

        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Товар *</span>
          <select v-model="form.product_id" class="field-input mt-1">
            <option :value="null">— выберите товар —</option>
            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ p.slug }})</option>
          </select>
        </label>

        <div class="grid grid-cols-2 gap-3">
          <label class="block">
            <span class="text-xs uppercase tracking-wide text-ink-700/60">Имя автора *</span>
            <input v-model="form.author_name" class="field-input mt-1" maxlength="120" />
          </label>
          <label class="block">
            <span class="text-xs uppercase tracking-wide text-ink-700/60">E-mail (не публикуется)</span>
            <input v-model="form.author_email" type="email" class="field-input mt-1" maxlength="190" />
          </label>
        </div>

        <div class="flex items-center gap-3">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Оценка *</span>
          <div class="flex gap-1">
            <button
              v-for="n in 5"
              :key="n"
              type="button"
              class="text-2xl leading-none"
              :class="n <= form.rating ? 'text-amber-500' : 'text-ink-700/20'"
              @click="form.rating = n"
            >★</button>
          </div>
        </div>

        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Текст отзыва *</span>
          <textarea v-model="form.body" rows="6" class="field-input mt-1" maxlength="4000" />
        </label>

        <div>
          <span class="text-xs uppercase tracking-wide text-ink-700/60 block mb-2">Фото (до 3, JPG/PNG/WebP, до 5 MB)</span>
          <input type="file" multiple accept="image/jpeg,image/png,image/webp" @change="handlePhotos" />

          <div v-if="photoPreviews.length" class="flex gap-2 mt-3 flex-wrap">
            <div v-for="(p, i) in photoPreviews" :key="i" class="relative w-24 h-24 rounded overflow-hidden border border-ink-700/10">
              <img :src="p" alt="" class="w-full h-full object-cover" />
              <button class="absolute top-1 right-1 bg-black/60 text-white rounded-full w-5 h-5 text-xs" @click="removePhotoSelection(i)">×</button>
            </div>
          </div>

          <div v-if="form.images?.length" class="mt-4">
            <div class="text-xs text-ink-700/60 mb-2">Уже загружено:</div>
            <div class="flex gap-2 flex-wrap">
              <div v-for="img in form.images" :key="img.id" class="relative w-24 h-24 rounded overflow-hidden border border-ink-700/10">
                <img :src="img.path" alt="" class="w-full h-full object-cover" />
                <button class="absolute top-1 right-1 bg-black/60 text-white rounded-full w-5 h-5 text-xs" @click="deleteExistingPhoto(img.id)">×</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card space-y-4">
        <h2 class="font-display text-xl">Публикация</h2>

        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Статус</span>
          <select v-model="form.status" class="field-input mt-1">
            <option value="pending">На модерации</option>
            <option value="approved">Опубликован</option>
            <option value="rejected">Отклонён</option>
          </select>
        </label>

        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Дата публикации</span>
          <input v-model="form.published_at" type="datetime-local" class="field-input mt-1" />
          <span class="text-xs text-ink-700/50 block mt-1">
            Можно указать прошлую дату (отзыв задним числом) или будущую (он появится автоматически).
          </span>
        </label>

        <label class="flex items-center gap-2">
          <input v-model="form.verified_purchase" type="checkbox" />
          <span class="text-sm">Верифицированная покупка</span>
        </label>

        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Полезных голосов</span>
          <input v-model.number="form.helpful_count" type="number" min="0" class="field-input mt-1" />
        </label>

        <div class="flex flex-col gap-2 pt-2 border-t border-ink-700/10">
          <button class="btn-primary" :disabled="saving" @click="save">{{ saving ? 'Сохранение…' : 'Сохранить' }}</button>
          <button v-if="!isNew" class="btn-ghost text-terracotta-700" @click="remove">
            <Trash2 :size="16" /> Удалить отзыв
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
