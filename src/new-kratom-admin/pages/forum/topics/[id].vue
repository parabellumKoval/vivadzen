<script setup lang="ts">
import { ArrowLeft, ExternalLink, MessageSquarePlus, Trash2 } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const api = useApi()
const config = useRuntimeConfig()
const { formatDateTime } = useAdminI18n()

const saving = ref(false)
const error = ref('')

const { data: categoriesData } = await useAsyncData('forum-categories-topic-edit', () => api<any>('/forum/categories'))
const categories = computed(() => categoriesData.value?.data ?? [])

const { data, refresh } = await useAsyncData(`forum-topic-${route.params.id}`, () => api<any>(`/forum/topics/${route.params.id}`))
const topic = computed(() => data.value?.data ?? {})
const posts = computed(() => data.value?.posts ?? [])

const form = reactive<any>({
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

watchEffect(() => {
  if (!topic.value?.id) return
  form.forum_category_id = topic.value.forum_category_id
  form.title = topic.value.title ?? ''
  form.emoji = topic.value.emoji ?? '💬'
  form.body = topic.value.body ?? ''
  form.status = topic.value.status ?? 'pending'
  form.moderation_note = topic.value.moderation_note ?? ''
  form.is_pinned = !!topic.value.is_pinned
  form.is_locked = !!topic.value.is_locked
  form.is_featured = !!topic.value.is_featured
  form.score = topic.value.score ?? 0
  form.published_at = topic.value.published_at ? topic.value.published_at.slice(0, 16) : ''
})

async function save() {
  saving.value = true
  error.value = ''
  try {
    await api(`/forum/topics/${route.params.id}`, { method: 'PUT', body: { ...form, published_at: form.published_at || null } })
    await refresh()
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось сохранить тему'
  } finally {
    saving.value = false
  }
}

async function remove() {
  if (!confirm('Удалить тему вместе с ответами?')) return
  await api(`/forum/topics/${route.params.id}`, { method: 'DELETE' })
  router.push('/forum/topics')
}
</script>

<template>
  <div class="space-y-4 max-w-5xl">
    <div class="flex items-center gap-3">
      <NuxtLink to="/forum/topics" class="btn-ghost"><ArrowLeft :size="16" /> Назад</NuxtLink>
      <h1 class="text-2xl font-display">Редактирование темы</h1>
      <NuxtLink v-if="topic.id" class="btn-ghost ml-auto" :to="`/forum/posts/new?topic_id=${topic.id}`">
        <MessageSquarePlus :size="16" /> Добавить ответ
      </NuxtLink>
      <a v-if="topic.status === 'approved'" class="btn-ghost" :href="`${config.public.siteBase}/forum/tema/${topic.slug}`" target="_blank">
        <ExternalLink :size="16" /> Открыть на сайте
      </a>
    </div>

    <div v-if="error" class="card border-l-4 border-terracotta-700 bg-terracotta-50 text-terracotta-700">{{ error }}</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="card lg:col-span-2 space-y-4">
        <h2 class="font-display text-xl">Содержание</h2>
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
          <select v-model="form.forum_category_id" class="field-input mt-1">
            <option :value="null">— без раздела —</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.icon }} {{ c.label }}</option>
          </select>
        </label>
        <label class="block">
          <span class="text-xs uppercase tracking-wide text-ink-700/60">Текст темы</span>
          <textarea v-model="form.body" rows="12" class="field-input mt-1" />
        </label>
      </div>

      <div class="card space-y-4">
        <h2 class="font-display text-xl">Модерация</h2>
        <div class="text-sm text-ink-700/60">
          <div>Автор: {{ topic.author?.name }} · {{ topic.author?.email }}</div>
          <div>Создано: {{ formatDateTime(topic.created_at) }}</div>
          <div>Ответов: {{ topic.approved_posts_count || 0 }}</div>
        </div>
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
        <div class="flex flex-col gap-2 pt-2 border-t border-ink-700/10">
          <button class="btn-primary" :disabled="saving" @click="save">{{ saving ? 'Сохранение…' : 'Сохранить' }}</button>
          <button class="btn-ghost text-terracotta-700" @click="remove"><Trash2 :size="16" /> Удалить тему</button>
        </div>
      </div>
    </div>

    <div class="card space-y-3">
      <h2 class="font-display text-xl">Последние ответы</h2>
      <ul v-if="posts.length" class="space-y-2">
        <li v-for="p in posts" :key="p.id" class="border border-ink-700/10 rounded-lg p-3 text-sm">
          <div class="flex items-center justify-between gap-3 mb-1">
            <NuxtLink :to="`/forum/posts/${p.id}`" class="font-medium text-moss-700 hover:underline">{{ p.author?.name || '—' }}</NuxtLink>
            <span class="text-xs text-ink-700/50">{{ p.status }} · {{ formatDateTime(p.created_at) }}</span>
          </div>
          <p class="text-ink-700/80 line-clamp-2">{{ p.body }}</p>
        </li>
      </ul>
      <p v-else class="text-sm text-ink-700/40">Ответов нет</p>
    </div>
  </div>
</template>
