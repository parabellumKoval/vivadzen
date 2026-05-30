<script setup lang="ts">
import { ArrowLeft, Ban, CircleCheck, Trash2 } from 'lucide-vue-next'

const route = useRoute()
const api = useApi()
const { t, formatDateTime, statusLabel } = useAdminI18n()

const { data, refresh } = await useAsyncData(`user-${route.params.id}`, () =>
  api<any>(`/users/${route.params.id}`),
)

const user = computed(() => data.value?.data ?? {})
const orders = computed(() => data.value?.orders ?? [])
const reviews = computed(() => data.value?.reviews ?? [])

const form = reactive({ name: '', email: '', phone: '', marketing_consent: false, forum_signature: '', forum_reputation: 0 })
const saving = ref(false)
const saveError = ref('')

watchEffect(() => {
  if (user.value?.id) {
    form.name = user.value.name ?? ''
    form.email = user.value.email ?? ''
    form.phone = user.value.phone ?? ''
    form.marketing_consent = !!user.value.marketing_consent
    form.forum_signature = user.value.forum_signature ?? ''
    form.forum_reputation = user.value.forum_reputation ?? 0
  }
})

async function save() {
  saving.value = true
  saveError.value = ''
  try {
    await api(`/users/${route.params.id}`, { method: 'PUT', body: { ...form } })
    await refresh()
  } catch (e: any) {
    saveError.value = e?.data?.message || t('users.save_error')
  } finally {
    saving.value = false
  }
}

async function toggleBlock() {
  const action = user.value.blocked_at ? 'unblock' : 'block'
  await api(`/users/${route.params.id}/${action}`, { method: 'POST' })
  await refresh()
}

async function remove() {
  if (!window.confirm(t('users.delete_confirm'))) return
  await api(`/users/${route.params.id}`, { method: 'DELETE' })
  navigateTo('/users')
}
</script>

<template>
  <div class="space-y-4 max-w-4xl">
    <div class="flex items-center gap-3">
      <NuxtLink to="/users" class="btn-ghost"><ArrowLeft :size="18" /></NuxtLink>
      <h1 class="text-2xl font-display">{{ user.name }}</h1>
      <span v-if="user.blocked_at" class="px-3 py-1 rounded-full bg-terracotta-700/10 text-terracotta-700 text-sm">{{ t('users.blocked') }}</span>
      <span v-else-if="!user.email_verified_at" class="px-3 py-1 rounded-full bg-amber-500/15 text-amber-700 text-sm">{{ t('users.unverified') }}</span>
      <span v-else class="px-3 py-1 rounded-full bg-moss-50 text-moss-700 text-sm">{{ t('users.active') }}</span>

      <div class="ml-auto flex gap-2">
        <button class="btn-ghost" @click="toggleBlock">
          <component :is="user.blocked_at ? CircleCheck : Ban" :size="16" />
          {{ user.blocked_at ? t('users.unblock') : t('users.block') }}
        </button>
        <button class="btn-ghost text-terracotta-700" @click="remove">
          <Trash2 :size="16" /> {{ t('users.delete') }}
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Profile -->
      <div class="card md:col-span-2 space-y-3">
        <h2 class="font-display text-lg">{{ t('users.profile') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <label class="block">
            <span class="text-xs text-ink-700/60">{{ t('users.name') }}</span>
            <input v-model="form.name" class="field-input w-full" />
          </label>
          <label class="block">
            <span class="text-xs text-ink-700/60">{{ t('users.email') }}</span>
            <input v-model="form.email" type="email" class="field-input w-full" />
          </label>
          <label class="block">
            <span class="text-xs text-ink-700/60">{{ t('users.phone') }}</span>
            <input v-model="form.phone" class="field-input w-full" />
          </label>
          <label class="flex items-center gap-2 mt-5">
            <input v-model="form.marketing_consent" type="checkbox" />
            <span class="text-sm">{{ t('users.marketing') }}</span>
          </label>
          <label class="block sm:col-span-2">
            <span class="text-xs text-ink-700/60">Подпись на форуме</span>
            <textarea v-model="form.forum_signature" class="field-input w-full" rows="2" maxlength="220" />
          </label>
          <label class="block">
            <span class="text-xs text-ink-700/60">Базовая репутация форума</span>
            <input v-model.number="form.forum_reputation" type="number" min="0" class="field-input w-full" />
          </label>
        </div>
        <p v-if="saveError" class="text-terracotta-700 text-sm">{{ saveError }}</p>
        <button class="btn-primary" :disabled="saving" @click="save">{{ saving ? t('users.saving') : t('users.save') }}</button>
      </div>

      <!-- Meta -->
      <div class="card space-y-2 text-sm">
        <h2 class="font-display text-lg">{{ t('users.meta') }}</h2>
        <div><span class="text-ink-700/60">{{ t('users.registered') }}:</span> {{ formatDateTime(user.created_at) }}</div>
        <div><span class="text-ink-700/60">{{ t('users.email_status') }}:</span> {{ user.email_verified_at ? t('users.verified') : t('users.unverified') }}</div>
        <div><span class="text-ink-700/60">{{ t('users.has_password') }}:</span> {{ user.has_password ? '✓' : '—' }}</div>
        <div><span class="text-ink-700/60">Forum:</span> {{ user.forum_topics_count || 0 }} тем · {{ user.forum_posts_count || 0 }} ответов</div>
        <div v-if="user.social_accounts?.length">
          <span class="text-ink-700/60">{{ t('users.social') }}:</span>
          <span v-for="s in user.social_accounts" :key="s.id" class="capitalize ml-1">{{ s.provider }}</span>
        </div>
      </div>

      <!-- Addresses -->
      <div class="card md:col-span-3 space-y-3" v-if="user.addresses?.length">
        <h2 class="font-display text-lg">{{ t('users.addresses') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <div v-for="a in user.addresses" :key="a.id" class="border border-ink-700/10 rounded-lg p-3 text-sm">
            <div class="font-semibold">{{ a.first_name }} {{ a.last_name }}
              <span v-if="a.is_default" class="text-xs text-moss-700">· {{ t('users.default') }}</span>
            </div>
            <div class="text-ink-700/70">{{ a.street }}<br>{{ a.zip }} {{ a.city }}, {{ a.country }}</div>
            <div v-if="a.phone" class="text-ink-700/50 text-xs mt-1">{{ a.phone }}</div>
          </div>
        </div>
      </div>

      <!-- Orders -->
      <div class="card md:col-span-2 space-y-3">
        <h2 class="font-display text-lg">{{ t('users.orders') }}</h2>
        <table class="w-full" v-if="orders.length">
          <thead>
            <tr class="border-b border-ink-700/10">
              <th class="table-th">ID</th>
              <th class="table-th">{{ t('users.status') }}</th>
              <th class="table-th text-right">{{ t('orders.total') }}</th>
              <th class="table-th text-right">{{ t('orders.time') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="o in orders" :key="o.public_id" class="border-b border-ink-700/5">
              <td class="table-td font-mono">
                <NuxtLink :to="`/orders/${o.public_id}`" class="text-moss-700 hover:underline">{{ o.public_id }}</NuxtLink>
              </td>
              <td class="table-td text-xs">{{ statusLabel(o.status) }}</td>
              <td class="table-td text-right">{{ o.total }} CZK</td>
              <td class="table-td text-right text-xs text-ink-700/60">{{ formatDateTime(o.created_at) }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="text-ink-700/40 text-sm">{{ t('users.no_orders') }}</p>
      </div>

      <!-- Reviews -->
      <div class="card space-y-3">
        <h2 class="font-display text-lg">{{ t('users.reviews') }}</h2>
        <ul v-if="reviews.length" class="space-y-2 text-sm">
          <li v-for="r in reviews" :key="r.id" class="border-l-2 border-moss-500 pl-3">
            <div class="text-amber-700">{{ '★'.repeat(r.rating) }}<span class="text-ink-700/30">{{ '★'.repeat(5 - r.rating) }}</span></div>
            <div class="text-ink-700/70 line-clamp-2">{{ r.body }}</div>
            <div class="text-ink-700/40 text-xs">{{ r.status }} · {{ formatDateTime(r.created_at) }}</div>
          </li>
        </ul>
        <p v-else class="text-ink-700/40 text-sm">{{ t('users.no_reviews') }}</p>
      </div>
    </div>
  </div>
</template>
