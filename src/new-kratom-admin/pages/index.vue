<script setup lang="ts">
import { Package, ShoppingCart, Coins, Clock } from 'lucide-vue-next'

const api = useApi()
const { t, formatDateTime } = useAdminI18n()
const { data, pending } = await useAsyncData('dashboard', () =>
  api<any>('/dashboard'),
)

const counters = computed(() => data.value?.counters ?? {})
const recent = computed(() => data.value?.recent_orders ?? [])

const cards = computed(() => [
  { label: t('dashboard.products'), value: counters.value.products ?? 0, icon: Package },
  { label: t('dashboard.orders_today'), value: counters.value.orders_today ?? 0, icon: ShoppingCart },
  { label: t('dashboard.orders_month'), value: counters.value.orders_month ?? 0, icon: ShoppingCart },
  { label: t('dashboard.revenue_month'), value: `${counters.value.revenue_month ?? 0} CZK`, icon: Coins },
  { label: t('dashboard.pending_orders'), value: counters.value.pending_orders ?? 0, icon: Clock },
])
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-display">{{ t('dashboard.title') }}</h1>

    <div v-if="pending" class="text-ink-700/50">{{ t('dashboard.loading') }}</div>

    <div v-else class="grid grid-cols-2 md:grid-cols-5 gap-4">
      <div v-for="c in cards" :key="c.label" class="card flex items-center gap-4">
        <div class="p-3 rounded-lg bg-moss-50 text-moss-700">
          <component :is="c.icon" :size="22" />
        </div>
        <div>
          <div class="text-xs text-ink-700/60 uppercase tracking-wide">{{ c.label }}</div>
          <div class="text-2xl font-semibold mt-1">{{ c.value }}</div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-display text-xl">{{ t('dashboard.recent_orders') }}</h2>
        <NuxtLink to="/orders" class="text-sm text-moss-700 hover:underline">{{ t('dashboard.all_orders') }} →</NuxtLink>
      </div>
      <table class="w-full">
        <thead>
          <tr class="border-b border-ink-700/10">
            <th class="table-th">ID</th>
            <th class="table-th">{{ t('orders.customer') }}</th>
            <th class="table-th">{{ t('orders.status') }}</th>
            <th class="table-th text-right">{{ t('orders.total') }}</th>
            <th class="table-th text-right">{{ t('orders.time') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="o in recent" :key="o.public_id" class="border-b border-ink-700/5 table-row-hover">
            <td class="table-td font-mono">
              <NuxtLink :to="`/orders/${o.public_id}`" class="text-moss-700 hover:underline">{{ o.public_id }}</NuxtLink>
            </td>
            <td class="table-td">{{ o.first_name }} {{ o.last_name }}</td>
            <td class="table-td"><span class="text-xs uppercase">{{ o.status }}</span></td>
            <td class="table-td text-right">{{ o.total }} CZK</td>
            <td class="table-td text-right text-ink-700/60 text-xs">{{ formatDateTime(o.created_at) }}</td>
          </tr>
          <tr v-if="!recent.length">
            <td colspan="5" class="table-td text-center text-ink-700/40">{{ t('dashboard.no_orders') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
