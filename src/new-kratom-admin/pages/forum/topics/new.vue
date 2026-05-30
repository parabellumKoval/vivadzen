<script setup lang="ts">
import { ArrowLeft, Save } from 'lucide-vue-next'

const router = useRouter()
const api = useApi()

const saving = ref(false)
const error = ref('')

const { data: categoriesData } = await useAsyncData('forum-categories-topic-create', () => api<any>('/forum/categories'))
const categories = computed(() => categoriesData.value?.data ?? [])

const { data: usersData } = await useAsyncData('forum-topic-create-users', () => api<any>('/users', { params: { per_page: 1000 } }))
const users = computed(() => usersData.value?.data?.data ?? [])

const form = reactive<any>({
  user_id: null,
  forum_category_id: null,
  title: '',
  emoji: '💬',
  body: '',
  status: 'approved',
  moderation_note: '',
  is_pinned: false,
  is_locked: false,
  is_featured: false,
  score: 0,
  published_at: '',
})

async function save() {
  saving.value = true
  error.value = ''
  try {
    const res = await api<any>('/forum/topics', { method: 'POST', body: { ...form, published_at: form.published_at || null } })
    router.push(`/forum/topics/${res.data.id}`)
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось создать тему'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="space-y-4 max-w-5xl">
    <div class="flex items-center gap-3">
      <NuxtLink to="/forum/topics" class="btn-ghost"><ArrowLeft :size="16" /> Назад</NuxtLink>
      <h1 class="text-2xl font-display">Новая тема форума</h1>
    </div>

    <div v-if="error" class="card border-l-4 border-terracotta-700 bg-terracotta-50 text-terracotta-700">{{ error }}</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="card lg:col-span-2 space-y-4">
        <h2 class="font-display text-xl">Содержание</h2>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Автор</span>
          <select v-model.number="form.user_id" class="field-input mt-1">
            <option :value="null">Выберите пользователя</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} · {{ u.email }}</option>
          </select>
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-[90px_1fr] gap-3">
          <label class="block">
            <span class="text-xs uppercase tracking-wide text-ink-700/60">Emoji</span>
            <input v-model="form.emoji" class="field-input mt-1" maxlength="16" />
          </label>
          <label class="block">
            <span class="text-xs uppercase tracking-wide text-ink-700/60">Заголовок</span>
            <input v-model="form.title" class="field-input mt-1" maxlength="160" />
          </label>
        </div>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Раздел</span>
          <select v-model.number="form.forum_category_id" class="field-input mt-1">
            <option :value="null">— без раздела —</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.icon }} {{ c.label }}</option>
          </select>
        </label>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Текст темы</span>
          <textarea v-model="form.body" rows="14" class="field-input mt-1" />
        </label>
      </div>

      <div class="card space-y-4">
        <h2 class="font-display text-xl">Модерация</h2>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Статус</span>
          <select v-model="form.status" class="field-input mt-1">
            <option value="pending">На модерации</option>
            <option value="approved">Опубликована</option>
            <option value="rejected">Отклонена</option>
          </select>
        </label>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Дата публикации</span>
          <input v-model="form.published_at" type="datetime-local" class="field-input mt-1" />
        </label>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Репутация темы</span>
          <input v-model.number="form.score" type="number" class="field-input mt-1" />
        </label>
        <label class="flex items-center gap-2"><input v-model="form.is_pinned" type="checkbox" /> <span class="text-sm">Закрепить</span></label>
        <label class="flex items-center gap-2"><input v-model="form.is_featured" type="checkbox" /> <span class="text-sm">В избранное</span></label>
        <label class="flex items-center gap-2"><input v-model="form.is_locked" type="checkbox" /> <span class="text-sm">Закрыть ответы</span></label>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Заметка модератора</span>
          <textarea v-model="form.moderation_note" rows="3" class="field-input mt-1" />
        </label>
        <button class="btn-primary w-full" :disabled="saving" @click="save">
          <Save :size="16" /> {{ saving ? 'Создание…' : 'Создать тему' }}
        </button>
      </div>
    </div>
  </div>
</template>
