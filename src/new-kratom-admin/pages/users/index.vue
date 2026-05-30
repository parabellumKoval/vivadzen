<script setup lang="ts">
const api = useApi()
const { t, formatDateTime } = useAdminI18n()
const search = ref('')
const status = ref('')
const page = ref(1)

const { data, pending } = await useAsyncData(
  'users',
  () => api<any>('/users', { params: { q: search.value, status: status.value, page: page.value } }),
  { watch: [search, status, page] },
)

const items = computed(() => data.value?.data?.data ?? [])
const meta = computed(() => data.value?.data ?? {})
</script>

<template>
  <div class="space-y-4">
    <h1 class="text-2xl font-display">{{ t('users.title') }}</h1>

    <div class="card">
      <div class="flex flex-wrap items-center gap-3 mb-4">
        <input v-model="search" :placeholder="t('users.search_placeholder')" class="field-input max-w-sm" />
        <select v-model="status" class="field-input max-w-xs">
          <option value="">{{ t('users.all') }}</option>
          <option value="active">{{ t('users.active') }}</option>
          <option value="blocked">{{ t('users.blocked') }}</option>
        </select>
      </div>

      <table class="w-full">
        <thead>
          <tr class="border-b border-ink-700/10">
            <th class="table-th">{{ t('users.name') }}</th>
            <th class="table-th">{{ t('users.email') }}</th>
            <th class="table-th">{{ t('users.phone') }}</th>
            <th class="table-th text-right">{{ t('users.orders') }}</th>
            <th class="table-th text-right">{{ t('users.reviews') }}</th>
            <th class="table-th">{{ t('users.status') }}</th>
            <th class="table-th text-right">{{ t('users.registered') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in items" :key="u.id" class="border-b border-ink-700/5 table-row-hover">
            <td class="table-td">
              <NuxtLink :to="`/users/${u.id}`" class="text-moss-700 hover:underline">{{ u.name }}</NuxtLink>
            </td>
            <td class="table-td text-xs">{{ u.email }}</td>
            <td class="table-td text-xs">{{ u.phone || '—' }}</td>
            <td class="table-td text-right">{{ u.orders_count }}</td>
            <td class="table-td text-right">{{ u.reviews_count }}</td>
            <td class="table-td">
              <span v-if="u.blocked_at" class="px-2 py-0.5 rounded-full bg-terracotta-700/10 text-terracotta-700 text-xs">{{ t('users.blocked') }}</span>
              <span v-else-if="!u.email_verified_at" class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-700 text-xs">{{ t('users.unverified') }}</span>
              <span v-else class="px-2 py-0.5 rounded-full bg-moss-50 text-moss-700 text-xs">{{ t('users.active') }}</span>
            </td>
            <td class="table-td text-right text-ink-700/60 text-xs">{{ formatDateTime(u.created_at) }}</td>
          </tr>
          <tr v-if="!items.length && !pending">
            <td colspan="7" class="table-td text-center text-ink-700/40">{{ t('users.empty') }}</td>
          </tr>
        </tbody>
      </table>

      <div class="flex items-center justify-between mt-4 text-sm text-ink-700/60">
        <span>{{ t('common.total_range', { from: meta.from || 0, to: meta.to || 0, total: meta.total || 0 }) }}</span>
        <div class="flex gap-2">
          <button class="btn-ghost" :disabled="page <= 1" @click="page--">←</button>
          <button class="btn-ghost" :disabled="page >= (meta.last_page || 1)" @click="page++">→</button>
        </div>
      </div>
    </div>
  </div>
</template>
