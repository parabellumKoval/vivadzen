<script setup lang="ts">
import { Plus, Trash2 } from 'lucide-vue-next'

const api = useApi()
const {
  t,
  contentLocales,
  primaryContentLocale,
  emptyLocalizedValue,
  normalizeLocalizedValue,
  pickLocalizedValue,
} = useAdminI18n()

const activeLocale = ref(primaryContentLocale.value)
const { data, refresh } = await useAsyncData('taxonomies', () => api<any>('/taxonomies'))
const items = computed(() => data.value?.data ?? [])

const TYPE_LABEL: Record<string, string> = {
  color: t('taxonomies.types.color'),
  strain: t('taxonomies.types.strain'),
  form: t('taxonomies.types.form'),
  region: t('taxonomies.types.region'),
}

const grouped = computed(() => {
  const groups: Record<string, any[]> = { color: [], strain: [], form: [], region: [] }
  for (const item of items.value) {
    groups[item.type]?.push(item)
  }
  return groups
})

const editing = ref<any>(null)

function createTaxonomy(type: string) {
  return {
    type,
    slug: '',
    label: emptyLocalizedValue(),
    description: emptyLocalizedValue(),
    meta: {
      h1_i18n: emptyLocalizedValue(),
      origin_i18n: emptyLocalizedValue(),
      dose_i18n: emptyLocalizedValue(),
      sub_i18n: emptyLocalizedValue(),
      vein: '',
      accent: '',
      rangeMin: '',
      rangeMax: '',
      comingSoon: false,
    },
    position: 0,
  }
}

function normalizeMeta(meta: any = {}) {
  return {
    ...meta,
    h1_i18n: normalizeLocalizedValue(meta.h1_i18n),
    origin_i18n: normalizeLocalizedValue(meta.origin_i18n),
    dose_i18n: normalizeLocalizedValue(meta.dose_i18n),
    sub_i18n: normalizeLocalizedValue(meta.sub_i18n),
    comingSoon: Boolean(meta.comingSoon),
  }
}

function normalizeTaxonomy(item: any) {
  const defaults = createTaxonomy(item.type)

  return {
    ...defaults,
    ...item,
    label: normalizeLocalizedValue(item.label),
    description: normalizeLocalizedValue(item.description),
    meta: normalizeMeta(item.meta),
  }
}

function startNew(type: string) {
  editing.value = createTaxonomy(type)
  activeLocale.value = primaryContentLocale.value
}

async function save() {
  if (editing.value.id) {
    await api(`/taxonomies/${editing.value.id}`, { method: 'PUT', body: editing.value })
  } else {
    await api('/taxonomies', { method: 'POST', body: editing.value })
  }

  editing.value = null
  await refresh()
}

async function remove(id: number) {
  if (!confirm(t('taxonomies.delete_confirm'))) {
    return
  }

  await api(`/taxonomies/${id}`, { method: 'DELETE' })
  await refresh()
}
</script>

<template>
  <div class="space-y-4">
    <h1 class="text-2xl font-display">{{ t('taxonomies.title') }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="(list, type) in grouped" :key="type" class="card">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-display text-lg">{{ TYPE_LABEL[type] }}</h2>
          <button class="btn-ghost" @click="startNew(type)"><Plus :size="14" />{{ t('taxonomies.add') }}</button>
        </div>
        <ul class="divide-y divide-ink-700/5">
          <li v-for="item in list" :key="item.id" class="flex items-center justify-between py-2">
            <div>
              <div class="font-mono text-xs text-ink-700/60">{{ item.slug }}</div>
              <div class="text-sm">{{ pickLocalizedValue(item.label) }}</div>
            </div>
            <div class="flex gap-2">
              <button class="text-xs text-moss-700 hover:underline" @click="editing = normalizeTaxonomy(item)">{{ t('taxonomies.edit') }}</button>
              <button class="text-xs text-terracotta-700 hover:underline" @click="remove(item.id)">{{ t('taxonomies.delete') }}</button>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <div v-if="editing" class="fixed inset-0 bg-ink-900/60 flex items-center justify-center p-4 z-50" @click.self="editing = null">
      <div class="bg-white rounded-2xl p-6 w-full max-w-2xl space-y-4 max-h-[90vh] overflow-auto">
        <div class="flex items-center justify-between gap-4">
          <h3 class="font-display text-xl">{{ editing.id ? t('taxonomies.edit') : t('taxonomies.new') }} ({{ TYPE_LABEL[editing.type] }})</h3>
          <AdminLocaleTabs v-model="activeLocale" :locales="contentLocales" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="field-label">{{ t('taxonomies.slug') }}</label>
            <input v-model="editing.slug" class="field-input font-mono" />
          </div>
          <div>
            <label class="field-label">{{ t('taxonomies.position') }}</label>
            <input v-model.number="editing.position" type="number" class="field-input" />
          </div>
        </div>

        <div>
          <label class="field-label">{{ t('taxonomies.label') }} ({{ activeLocale.toUpperCase() }})</label>
          <input v-model="editing.label[activeLocale]" class="field-input" />
        </div>

        <div>
          <label class="field-label">{{ t('taxonomies.description') }} ({{ activeLocale.toUpperCase() }})</label>
          <textarea v-model="editing.description[activeLocale]" rows="4" class="field-input" />
        </div>

        <div class="space-y-3">
          <h4 class="font-display text-lg">{{ t('taxonomies.meta') }}</h4>

          <template v-if="editing.type === 'color'">
            <div>
              <label class="field-label">{{ t('taxonomies.h1') }} ({{ activeLocale.toUpperCase() }})</label>
              <input v-model="editing.meta.h1_i18n[activeLocale]" class="field-input" />
            </div>
            <div>
              <label class="field-label">{{ t('taxonomies.dose') }} ({{ activeLocale.toUpperCase() }})</label>
              <input v-model="editing.meta.dose_i18n[activeLocale]" class="field-input" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label">{{ t('taxonomies.vein') }}</label>
                <select v-model="editing.meta.vein" class="field-input">
                  <option value="">—</option>
                  <option value="green">green</option>
                  <option value="white">white</option>
                  <option value="red">red</option>
                  <option value="yellow">yellow</option>
                </select>
              </div>
              <div>
                <label class="field-label">{{ t('taxonomies.accent') }}</label>
                <input v-model="editing.meta.accent" class="field-input" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label">{{ t('taxonomies.range_min') }}</label>
                <input v-model="editing.meta.rangeMin" class="field-input" />
              </div>
              <div>
                <label class="field-label">{{ t('taxonomies.range_max') }}</label>
                <input v-model="editing.meta.rangeMax" class="field-input" />
              </div>
            </div>
          </template>

          <template v-if="editing.type === 'strain'">
            <div>
              <label class="field-label">{{ t('taxonomies.origin') }} ({{ activeLocale.toUpperCase() }})</label>
              <input v-model="editing.meta.origin_i18n[activeLocale]" class="field-input" />
            </div>
          </template>

          <template v-if="editing.type === 'form'">
            <div>
              <label class="field-label">{{ t('taxonomies.sub') }} ({{ activeLocale.toUpperCase() }})</label>
              <input v-model="editing.meta.sub_i18n[activeLocale]" class="field-input" />
            </div>
          </template>

          <label class="flex items-center gap-2 text-sm">
            <input v-model="editing.meta.comingSoon" type="checkbox" />
            {{ t('taxonomies.coming_soon') }}
          </label>
        </div>

        <div class="flex justify-end gap-2">
          <button class="btn-ghost" @click="editing = null">{{ t('taxonomies.cancel') }}</button>
          <button class="btn-primary" @click="save">{{ t('taxonomies.save') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
