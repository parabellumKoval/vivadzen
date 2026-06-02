<script setup lang="ts">
import { Plus, Search, FileText } from 'lucide-vue-next'

const api = useApi()
const { t, pickLocalizedValue } = useAdminI18n()
const search = ref('')
const page = ref(1)

const { data, pending } = await useAsyncData(
  'lab-batches',
  () => api<any>('/lab-batches', { params: { q: search.value, page: page.value } }),
  { watch: [search, page] },
)

const items = computed(() => data.value?.data?.data ?? [])
const meta = computed(() => data.value?.data ?? {})
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-display">{{ t('lab_batches.title') }}</h1>
      <NuxtLink to="/lab-batches/new" class="btn-primary">
        <Plus :size="18" />{{ t('lab_batches.new') }}
      </NuxtLink>
    </div>

    <div class="card">
      <div class="flex items-center gap-3 mb-4">
        <Search :size="18" class="text-ink-700/50" />
        <input v-model="search" :placeholder="t('lab_batches.search_placeholder')" class="field-input max-w-md" />
      </div>

      <table class="w-full">
        <thead>
          <tr class="border-b border-ink-700/10">
            <th class="table-th">{{ t('lab_batches.lot') }}</th>
            <th class="table-th">{{ t('lab_batches.product_name') }}</th>
            <th class="table-th">{{ t('lab_batches.strains') }}</th>
            <th class="table-th">{{ t('lab_batches.issued_at') }}</th>
            <th class="table-th">{{ t('lab_batches.pass_ratio') }}</th>
            <th class="table-th">{{ t('lab_batches.files') }}</th>
            <th class="table-th">{{ t('lab_batches.products') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="b in items" :key="b.id" class="border-b border-ink-700/5 table-row-hover">
            <td class="table-td font-mono">
              <NuxtLink :to="`/lab-batches/${b.id}`" class="text-moss-700 hover:underline">{{ b.lot }}</NuxtLink>
            </td>
            <td class="table-td">{{ b.product_name }}</td>
            <td class="table-td text-xs text-ink-700/70">{{ (b.strains || []).join(', ') }}</td>
            <td class="table-td font-mono text-xs">{{ b.issued_at || '—' }}</td>
            <td class="table-td">
              <span v-if="b.summary?.total" class="text-xs">
                {{ Math.round((b.summary.ratio || 0) * 100) }}% ({{ b.summary.passed }}/{{ b.summary.total }})
              </span>
              <span v-else class="text-ink-700/40 text-xs">—</span>
            </td>
            <td class="table-td">
              <span class="inline-flex items-center gap-1 text-xs">
                <FileText :size="14" /> {{ b.files_count }}
              </span>
            </td>
            <td class="table-td text-xs">
              <span v-if="!b.products?.length" class="text-ink-700/40">—</span>
              <span v-else class="text-ink-700/70">
                {{ b.products.map((p: any) => pickLocalizedValue(p.name) || p.slug).join(', ') }}
              </span>
            </td>
          </tr>
          <tr v-if="!items.length && !pending">
            <td colspan="7" class="table-td text-center text-ink-700/40">{{ t('lab_batches.empty') }}</td>
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
