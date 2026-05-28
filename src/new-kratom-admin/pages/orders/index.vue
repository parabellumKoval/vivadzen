<script setup lang="ts">
const api = useApi()
const { t, formatDateTime, statusLabel } = useAdminI18n()
const search = ref('')
const status = ref('')
const page = ref(1)

const { data, pending } = await useAsyncData(
  'orders',
  () => api<any>('/orders', { params: { q: search.value, status: status.value, page: page.value } }),
  { watch: [search, status, page] },
)

const items = computed(() => data.value?.data?.data ?? [])
const meta = computed(() => data.value?.data ?? {})

const STATUSES = ['', 'pending', 'received', 'paid', 'packed', 'shipped', 'delivered', 'cancelled', 'refunded']
</script>

<template>
  <div class="space-y-4">
    <h1 class="text-2xl font-display">{{ t('orders.title') }}</h1>

    <div class="card">
      <div class="flex flex-wrap items-center gap-3 mb-4">
        <input v-model="search" :placeholder="t('orders.search_placeholder')" class="field-input max-w-sm" />
        <select v-model="status" class="field-input max-w-xs">
          <option value="">{{ t('orders.all_statuses') }}</option>
          <option v-for="item in STATUSES.slice(1)" :key="item" :value="item">{{ statusLabel(item) }}</option>
        </select>
      </div>

      <table class="w-full">
        <thead>
          <tr class="border-b border-ink-700/10">
            <th class="table-th">ID</th>
            <th class="table-th">{{ t('orders.customer') }}</th>
            <th class="table-th">{{ t('orders.email') }}</th>
            <th class="table-th">{{ t('orders.status') }}</th>
            <th class="table-th text-right">{{ t('orders.items') }}</th>
            <th class="table-th text-right">{{ t('orders.total') }}</th>
            <th class="table-th text-right">{{ t('orders.time') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="o in items" :key="o.public_id" class="border-b border-ink-700/5 table-row-hover">
            <td class="table-td font-mono">
              <NuxtLink :to="`/orders/${o.public_id}`" class="text-moss-700 hover:underline">{{ o.public_id }}</NuxtLink>
            </td>
            <td class="table-td">{{ o.first_name }} {{ o.last_name }}</td>
            <td class="table-td text-xs">{{ o.email }}</td>
            <td class="table-td"><span class="text-xs uppercase">{{ statusLabel(o.status) }}</span></td>
            <td class="table-td text-right">{{ o.items_count }}</td>
            <td class="table-td text-right">{{ o.total }} CZK</td>
            <td class="table-td text-right text-ink-700/60 text-xs">{{ formatDateTime(o.created_at) }}</td>
          </tr>
          <tr v-if="!items.length && !pending">
            <td colspan="7" class="table-td text-center text-ink-700/40">{{ t('orders.empty') }}</td>
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
