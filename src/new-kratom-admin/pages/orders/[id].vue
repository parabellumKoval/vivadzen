<script setup lang="ts">
import { ArrowLeft } from 'lucide-vue-next'

const route = useRoute()
const api = useApi()
const { t, formatDateTime, statusLabel } = useAdminI18n()

const { data, refresh } = await useAsyncData(`order-${route.params.id}`, () =>
  api<any>(`/orders/${route.params.id}`),
)

const order = computed(() => data.value?.data ?? {})
const STATUSES = ['pending', 'received', 'paid', 'packed', 'shipped', 'delivered', 'cancelled', 'refunded']

const newStatus = ref('')
const note = ref('')

async function changeStatus() {
  if (!newStatus.value) {
    return
  }

  await api(`/orders/${route.params.id}/status`, {
    method: 'POST',
    body: { status: newStatus.value, note: note.value },
  })

  newStatus.value = ''
  note.value = ''
  await refresh()
}
</script>

<template>
  <div class="space-y-4 max-w-4xl">
    <div class="flex items-center gap-3">
      <NuxtLink to="/orders" class="btn-ghost"><ArrowLeft :size="18" /></NuxtLink>
      <h1 class="text-2xl font-display font-mono">{{ order.public_id }}</h1>
      <span class="px-3 py-1 rounded-full bg-moss-50 text-moss-700 text-sm">{{ statusLabel(order.status) }}</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="card md:col-span-2 space-y-3">
        <h2 class="font-display text-lg">{{ t('orders.items_title') }}</h2>
        <table class="w-full">
          <thead>
            <tr class="border-b border-ink-700/10">
              <th class="table-th">Продукт</th>
              <th class="table-th">{{ t('products.size') }}</th>
              <th class="table-th text-right">{{ t('products.price') }}</th>
              <th class="table-th text-right">Кол-во</th>
              <th class="table-th text-right">Итого</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in order.items" :key="item.id" class="border-b border-ink-700/5">
              <td class="table-td">{{ item.product_name }} <span class="text-xs text-ink-700/50 font-mono">{{ item.product_slug }}</span></td>
              <td class="table-td">{{ item.size }} {{ item.unit }}</td>
              <td class="table-td text-right">{{ item.unit_price }} CZK</td>
              <td class="table-td text-right">{{ item.qty }}</td>
              <td class="table-td text-right font-semibold">{{ item.line_total }} CZK</td>
            </tr>
          </tbody>
          <tfoot>
            <tr><td colspan="4" class="table-td text-right">{{ t('orders.subtotal') }}</td><td class="table-td text-right">{{ order.subtotal }} CZK</td></tr>
            <tr v-if="order.discount"><td colspan="4" class="table-td text-right">{{ t('orders.discount') }}</td><td class="table-td text-right text-terracotta-700">−{{ order.discount }} CZK</td></tr>
            <tr><td colspan="4" class="table-td text-right font-bold">{{ t('orders.total') }}</td><td class="table-td text-right font-bold">{{ order.total }} CZK</td></tr>
          </tfoot>
        </table>
      </div>

      <div class="card space-y-3">
        <h2 class="font-display text-lg">{{ t('orders.customer_title') }}</h2>
        <div class="text-sm space-y-1">
          <div><strong>{{ order.first_name }} {{ order.last_name }}</strong></div>
          <div>{{ order.email }}</div>
          <div>{{ order.phone }}</div>
          <div class="pt-2 border-t border-ink-700/10">
            {{ order.street }}<br>{{ order.zip }} {{ order.city }}, {{ order.country }}
          </div>
          <div class="text-xs text-ink-700/60 pt-2">
            {{ t('orders.delivery') }}: <strong>{{ order.delivery_method }}</strong><br>
            {{ t('orders.payment') }}: <strong>{{ order.payment_method }}</strong>
          </div>
        </div>
      </div>

      <div class="card md:col-span-2 space-y-3">
        <h2 class="font-display text-lg">{{ t('orders.change_status') }}</h2>
        <div class="flex gap-3">
          <select v-model="newStatus" class="field-input max-w-xs">
            <option value="">{{ t('orders.select_status') }}</option>
            <option v-for="item in STATUSES" :key="item" :value="item">{{ statusLabel(item) }}</option>
          </select>
          <input v-model="note" :placeholder="t('orders.note_placeholder')" class="field-input flex-1" />
          <button class="btn-primary" :disabled="!newStatus" @click="changeStatus">{{ t('products.save') }}</button>
        </div>
      </div>

      <div class="card space-y-3">
        <h2 class="font-display text-lg">{{ t('orders.history') }}</h2>
        <ul class="text-xs space-y-2">
          <li v-for="item in order.history" :key="item.id" class="border-l-2 border-moss-500 pl-3">
            <div class="text-ink-700/60">{{ formatDateTime(item.created_at) }}</div>
            <div><strong>{{ statusLabel(item.from_status || 'pending') }} → {{ statusLabel(item.to_status) }}</strong></div>
            <div v-if="item.note" class="text-ink-700/70">{{ item.note }}</div>
            <div v-if="item.admin" class="text-ink-700/50">{{ item.admin.name }}</div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
