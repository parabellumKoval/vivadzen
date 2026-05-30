<script setup lang="ts">
import { ArrowLeft, Save } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const api = useApi()

const saving = ref(false)
const error = ref('')

const queryTopicId = Number(route.query.topic_id)

const { data: usersData } = await useAsyncData('forum-post-create-users', () => api<any>('/users', { params: { per_page: 1000 } }))
const users = computed(() => usersData.value?.data?.data ?? [])

const { data: topicsData } = await useAsyncData('forum-post-create-topics', () => api<any>('/forum/topics', { params: { per_page: 1000 } }))
const topics = computed(() => topicsData.value?.data?.data ?? [])

const form = reactive<any>({
  forum_topic_id: Number.isFinite(queryTopicId) ? queryTopicId : null,
  user_id: null,
  body: '',
  status: 'approved',
  moderation_note: '',
  score: 0,
  published_at: '',
})

async function save() {
  saving.value = true
  error.value = ''
  try {
    const res = await api<any>('/forum/posts', { method: 'POST', body: { ...form, published_at: form.published_at || null } })
    router.push(`/forum/posts/${res.data.id}`)
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось создать ответ'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="space-y-4 max-w-4xl">
    <div class="flex items-center gap-3">
      <NuxtLink to="/forum/posts" class="btn-ghost"><ArrowLeft :size="16" /> Назад</NuxtLink>
      <h1 class="text-2xl font-display">Новый ответ форума</h1>
    </div>

    <div v-if="error" class="card border-l-4 border-terracotta-700 bg-terracotta-50 text-terracotta-700">{{ error }}</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="card lg:col-span-2 space-y-4">
        <h2 class="font-display text-xl">Ответ</h2>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Тема</span>
          <select v-model.number="form.forum_topic_id" class="field-input mt-1">
            <option :value="null">Выберите тему</option>
            <option v-for="t in topics" :key="t.id" :value="t.id">{{ t.emoji }} {{ t.title }}</option>
          </select>
        </label>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Автор</span>
          <select v-model.number="form.user_id" class="field-input mt-1">
            <option :value="null">Выберите пользователя</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} · {{ u.email }}</option>
          </select>
        </label>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Текст ответа</span>
          <textarea v-model="form.body" rows="14" class="field-input mt-1" />
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
        <button class="btn-primary w-full" :disabled="saving" @click="save">
          <Save :size="16" /> {{ saving ? 'Создание…' : 'Создать ответ' }}
        </button>
      </div>
    </div>
  </div>
</template>
