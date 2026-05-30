<script setup lang="ts">
import { ArrowLeft, ExternalLink, Trash2 } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const api = useApi()
const config = useRuntimeConfig()
const { formatDateTime } = useAdminI18n()

const saving = ref(false)
const error = ref('')

const { data, refresh } = await useAsyncData(`forum-post-${route.params.id}`, () => api<any>(`/forum/posts/${route.params.id}`))
const post = computed(() => data.value?.data ?? {})

const form = reactive<any>({
  body: '',
  status: 'approved',
  moderation_note: '',
  score: 0,
  published_at: '',
})

watchEffect(() => {
  if (!post.value?.id) return
  form.body = post.value.body ?? ''
  form.status = post.value.status ?? 'pending'
  form.moderation_note = post.value.moderation_note ?? ''
  form.score = post.value.score ?? 0
  form.published_at = post.value.published_at ? post.value.published_at.slice(0, 16) : ''
})

async function save() {
  saving.value = true
  error.value = ''
  try {
    await api(`/forum/posts/${route.params.id}`, { method: 'PUT', body: { ...form, published_at: form.published_at || null } })
    await refresh()
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось сохранить ответ'
  } finally {
    saving.value = false
  }
}

async function remove() {
  if (!confirm('Удалить ответ?')) return
  await api(`/forum/posts/${route.params.id}`, { method: 'DELETE' })
  router.push('/forum/posts')
}
</script>

<template>
  <div class="space-y-4 max-w-4xl">
    <div class="flex items-center gap-3">
      <NuxtLink to="/forum/posts" class="btn-ghost"><ArrowLeft :size="16" /> Назад</NuxtLink>
      <h1 class="text-2xl font-display">Редактирование ответа</h1>
      <a v-if="post.topic?.status === 'approved'" class="btn-ghost ml-auto" :href="`${config.public.siteBase}/forum/tema/${post.topic.slug}#post-${post.id}`" target="_blank">
        <ExternalLink :size="16" /> На сайте
      </a>
    </div>

    <div v-if="error" class="card border-l-4 border-terracotta-700 bg-terracotta-50 text-terracotta-700">{{ error }}</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="card lg:col-span-2 space-y-4">
        <h2 class="font-display text-xl">Ответ</h2>
        <div class="text-sm text-ink-700/60">
          <div>Автор: {{ post.author?.name }} · {{ post.author?.email }}</div>
          <div>Тема: {{ post.topic?.emoji }} {{ post.topic?.title }}</div>
          <div>Создано: {{ formatDateTime(post.created_at) }}</div>
        </div>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Текст ответа</span>
          <textarea v-model="form.body" rows="12" class="field-input mt-1" />
        </label>
      </div>

      <div class="card space-y-4">
        <h2 class="font-display text-xl">Модерация</h2>
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
        </label>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Рейтинг</span>
          <input v-model.number="form.score" type="number" class="field-input mt-1" />
        </label>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Заметка модератора</span>
          <textarea v-model="form.moderation_note" rows="3" class="field-input mt-1" />
        </label>
        <div class="flex flex-col gap-2 pt-2 border-t border-ink-700/10">
          <button class="btn-primary" :disabled="saving" @click="save">{{ saving ? 'Сохранение…' : 'Сохранить' }}</button>
          <button class="btn-ghost text-terracotta-700" @click="remove"><Trash2 :size="16" /> Удалить ответ</button>
        </div>
      </div>
    </div>
  </div>
</template>
