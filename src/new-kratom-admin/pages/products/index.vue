<script setup lang="ts">
import { Plus, Search } from 'lucide-vue-next'

const api = useApi()
const { t, pickLocalizedValue } = useAdminI18n()
const search = ref('')
const page = ref(1)

const { data, pending, refresh } = await useAsyncData(
  'products',
  () => api<any>('/products', { params: { q: search.value, page: page.value } }),
  { watch: [search, page] },
)

const items = computed(() => data.value?.data?.data ?? [])
const meta = computed(() => data.value?.data ?? {})
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-display">{{ t('products.title') }}</h1>
      <NuxtLink to="/products/new" class="btn-primary">
        <Plus :size="18" />{{ t('products.new_product') }}
      </NuxtLink>
    </div>

    <div class="card">
      <div class="flex items-center gap-3 mb-4">
        <Search :size="18" class="text-ink-700/50" />
        <input v-model="search" :placeholder="t('products.search_placeholder')" class="field-input max-w-md" />
      </div>

      <table class="w-full">
        <thead>
          <tr class="border-b border-ink-700/10">
            <th class="table-th">{{ t('products.slug') }}</th>
            <th class="table-th">{{ t('products.name') }}</th>
            <th class="table-th">{{ t('products.color') }}</th>
            <th class="table-th">{{ t('products.strain') }}</th>
            <th class="table-th">{{ t('products.mitragynin') }}</th>
            <th class="table-th">{{ t('products.batch') }}</th>
            <th class="table-th">{{ t('products.stock') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in items" :key="p.id" class="border-b border-ink-700/5 table-row-hover">
            <td class="table-td font-mono">
              <NuxtLink :to="`/products/${p.id}`" class="text-moss-700 hover:underline">{{ p.slug }}</NuxtLink>
            </td>
            <td class="table-td">{{ pickLocalizedValue(p.name) }}</td>
            <td class="table-td">{{ p.color_slug }}</td>
            <td class="table-td">{{ p.strain_slug }}</td>
            <td class="table-td">{{ p.mitragynin }} %</td>
            <td class="table-td font-mono text-xs">{{ p.batch }}</td>
            <td class="table-td">
              <span :class="p.in_stock ? 'text-moss-700' : 'text-terracotta-700'">
                {{ p.in_stock ? t('products.in_stock') : t('products.out_of_stock') }}
              </span>
            </td>
          </tr>
          <tr v-if="!items.length && !pending">
            <td colspan="7" class="table-td text-center text-ink-700/40">{{ t('products.empty') }}</td>
          </tr>
        </tbody>
      </table>

      <div class="flex items-center justify-between mt-4 text-sm text-ink-700/60">
        <span>{{ t('common.total_range', { from: meta.from || 0, to: meta.to || 0, total: meta.total || 0 }) }}</span>
        <div class="flex gap-2">
          <button class="btn-ghost" :disabled="page <= 1" @click="page--">{{ t('products.previous') }}</button>
          <button class="btn-ghost" :disabled="page >= (meta.last_page || 1)" @click="page++">{{ t('products.next') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
