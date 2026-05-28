<script setup lang="ts">
import { ArrowLeft, Trash2 } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const api = useApi()
const { pickLocalizedValue } = useAdminI18n()

const id = route.params.id as string
const isNew = id === 'new'

const products = ref<any[]>([])
const saving = ref(false)
const error = ref('')

const form = ref<any>({
  product_id: null,
  author_name: '',
  author_email: '',
  question: '',
  answer: '',
  answered_by: 'Tým Vivadzen',
  answered_at: '',
  helpful_count: 0,
  status: 'approved',
  published_at: '',
})

async function loadProducts() {
  const res = await api<any>('/products', { params: { per_page: 200 } })
  products.value = (res?.data?.data ?? []).map((p: any) => ({
    id: p.id,
    slug: p.slug,
    name: pickLocalizedValue(p.name),
  }))
}

async function loadQuestion() {
  if (isNew) return
  const res = await api<any>(`/questions/${id}`)
  const q = res?.data ?? {}
  form.value = {
    product_id: q.product_id,
    author_name: q.author_name,
    author_email: q.author_email || '',
    question: q.question,
    answer: q.answer || '',
    answered_by: q.answered_by || '',
    answered_at: q.answered_at ? q.answered_at.slice(0, 16) : '',
    helpful_count: q.helpful_count || 0,
    status: q.status,
    published_at: q.published_at ? q.published_at.slice(0, 16) : '',
  }
}

await loadProducts()
await loadQuestion()

async function save() {
  if (saving.value) return
  error.value = ''
  saving.value = true
  try {
    const payload: any = {
      product_id: form.value.product_id,
      author_name: form.value.author_name,
      author_email: form.value.author_email,
      question: form.value.question,
      answer: form.value.answer || null,
      answered_by: form.value.answered_by || null,
      answered_at: form.value.answered_at || null,
      helpful_count: form.value.helpful_count || 0,
      status: form.value.status,
      published_at: form.value.published_at || null,
    }

    if (isNew) {
      const res = await api<any>('/questions', { method: 'POST', body: payload })
      router.push(`/questions/${res?.data?.id}`)
    } else {
      await api(`/questions/${id}`, { method: 'PUT', body: payload })
    }
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось сохранить'
  } finally {
    saving.value = false
  }
}

async function remove() {
  if (!confirm('Удалить вопрос?')) return
  await api(`/questions/${id}`, { method: 'DELETE' })
  router.push('/questions')
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-3">
      <NuxtLink to="/questions" class="btn-ghost"><ArrowLeft :size="16" /> Назад</NuxtLink>
      <h1 class="text-2xl font-display">{{ isNew ? 'Новый вопрос' : 'Редактирование вопроса' }}</h1>
    </div>

    <div v-if="error" class="card border-l-4 border-terracotta-700 bg-terracotta-50 text-terracotta-700">{{ error }}</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="card lg:col-span-2 space-y-4">
        <h2 class="font-display text-xl">Вопрос</h2>

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
            <span class="text-xs uppercase tracking-wide text-ink-700/60">E-mail</span>
            <input v-model="form.author_email" type="email" class="field-input mt-1" maxlength="190" />
          </label>
        </div>

        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Текст вопроса *</span>
          <textarea v-model="form.question" rows="4" class="field-input mt-1" maxlength="2000" />
        </label>

        <h2 class="font-display text-xl pt-4 border-t border-ink-700/10">Ответ от команды</h2>

        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Кто отвечает</span>
          <input v-model="form.answered_by" class="field-input mt-1" maxlength="120" placeholder="Tým Vivadzen" />
        </label>

        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Дата ответа</span>
          <input v-model="form.answered_at" type="datetime-local" class="field-input mt-1" />
        </label>

        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Ответ</span>
          <textarea v-model="form.answer" rows="5" class="field-input mt-1" maxlength="4000" />
        </label>
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
            Можно поставить прошлую дату (задним числом) или будущую (опубликуется автоматически).
          </span>
        </label>

        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Полезных голосов</span>
          <input v-model.number="form.helpful_count" type="number" min="0" class="field-input mt-1" />
        </label>

        <div class="flex flex-col gap-2 pt-2 border-t border-ink-700/10">
          <button class="btn-primary" :disabled="saving" @click="save">{{ saving ? 'Сохранение…' : 'Сохранить' }}</button>
          <button v-if="!isNew" class="btn-ghost text-terracotta-700" @click="remove">
            <Trash2 :size="16" /> Удалить
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
