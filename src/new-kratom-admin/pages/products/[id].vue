<script setup lang="ts">
import { Save, Trash2, ArrowLeft } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const api = useApi()
const isNew = route.params.id === 'new'
const {
  t,
  contentLocales,
  primaryContentLocale,
  previewContentLocale,
  emptyLocalizedValue,
  normalizeLocalizedValue,
  pickLocalizedValue,
} = useAdminI18n()

const activeLocale = ref(primaryContentLocale.value)

function createDefaultForm() {
  return {
    slug: '',
    name: emptyLocalizedValue(),
    short: emptyLocalizedValue(),
    description: emptyLocalizedValue(),
    origin: emptyLocalizedValue(),
    grind: emptyLocalizedValue(),
    color_slug: '',
    strain_slug: '',
    form_slug: 'prasek',
    mitragynin: null,
    h7mg: null,
    purity: null,
    batch: '',
    tested_at: '',
    rating: 4.8,
    reviews_count: 0,
    questions_count: 0,
    in_stock: true,
    main_image: '',
    position: 0,
    published_at: new Date().toISOString().slice(0, 10),
    variants: [
      { size: 25, unit: 'g', price: 0, stock: 0, sku: '' },
      { size: 50, unit: 'g', price: 0, stock: 0, sku: '' },
    ],
  }
}

const form = ref<any>(createDefaultForm())
const taxonomies = ref<any[]>([])
const saving = ref(false)
const error = ref('')

function hydrateForm(product: any = {}) {
  const defaults = createDefaultForm()

  form.value = {
    ...defaults,
    ...product,
    name: normalizeLocalizedValue(product.name),
    short: normalizeLocalizedValue(product.short),
    description: normalizeLocalizedValue(product.description),
    origin: normalizeLocalizedValue(product.origin),
    grind: normalizeLocalizedValue(product.grind),
    variants: product.variants?.length ? product.variants : defaults.variants,
  }
}

if (!isNew) {
  const { data } = await useFetch<any>(`/products/${route.params.id}`, {
    baseURL: useRuntimeConfig().public.apiBase,
    headers: { Authorization: `Bearer ${useAuthStore().token}` },
  })

  if (data.value?.data) {
    hydrateForm(data.value.data)
  }
}

const { data: taxData } = await useAsyncData('tax-all', () => api<any>('/taxonomies'))
taxonomies.value = taxData.value?.data ?? []

const colors = computed(() => taxonomies.value.filter((taxonomy: any) => taxonomy.type === 'color'))
const strains = computed(() => taxonomies.value.filter((taxonomy: any) => taxonomy.type === 'strain'))
const forms = computed(() => taxonomies.value.filter((taxonomy: any) => taxonomy.type === 'form'))
const pageTitle = computed(() => isNew
  ? t('products.new_product')
  : pickLocalizedValue(form.value.name, activeLocale.value) || pickLocalizedValue(form.value.name, previewContentLocale.value))

async function save() {
  saving.value = true
  error.value = ''

  try {
    if (isNew) {
      const res = await api<any>('/products', { method: 'POST', body: form.value })
      router.push(`/products/${res.data.id}`)
    } else {
      await api<any>(`/products/${route.params.id}`, { method: 'PUT', body: form.value })
    }
  } catch (e: any) {
    error.value = e?.data?.message || t('products.save_error')
  } finally {
    saving.value = false
  }
}

async function remove() {
  if (!confirm(t('products.delete_confirm'))) {
    return
  }

  await api(`/products/${route.params.id}`, { method: 'DELETE' })
  router.push('/products')
}
</script>

<template>
  <div class="space-y-4 max-w-6xl">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <NuxtLink to="/products" class="btn-ghost"><ArrowLeft :size="18" /></NuxtLink>
        <h1 class="text-2xl font-display">{{ pageTitle }}</h1>
      </div>
      <div class="flex gap-2">
        <button v-if="!isNew" class="btn-danger" @click="remove"><Trash2 :size="16" />{{ t('products.delete') }}</button>
        <button class="btn-primary" :disabled="saving" @click="save"><Save :size="16" />{{ saving ? t('products.saving') : t('products.save') }}</button>
      </div>
    </div>

    <div v-if="error" class="card border-terracotta-500 text-terracotta-700">{{ error }}</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="card lg:col-span-2 space-y-4">
        <div class="flex items-center justify-between gap-4">
          <h2 class="font-display text-lg">{{ t('products.content') }}</h2>
          <AdminLocaleTabs v-model="activeLocale" :locales="contentLocales" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="field-label">{{ t('products.slug') }} *</label>
            <input v-model="form.slug" class="field-input font-mono" />
          </div>
          <div>
            <label class="field-label">{{ t('products.batch') }}</label>
            <input v-model="form.batch" class="field-input font-mono" />
          </div>
        </div>

        <div>
          <label class="field-label">{{ t('products.name') }} ({{ activeLocale.toUpperCase() }}) *</label>
          <input v-model="form.name[activeLocale]" class="field-input" />
        </div>

        <div>
          <label class="field-label">{{ t('products.short') }} ({{ activeLocale.toUpperCase() }})</label>
          <input v-model="form.short[activeLocale]" class="field-input" />
        </div>

        <div>
          <label class="field-label">{{ t('products.description') }} ({{ activeLocale.toUpperCase() }})</label>
          <AdminRichEditor
            :key="`desc-${activeLocale}`"
            v-model="form.description[activeLocale]"
            placeholder="Опишите продукт — поддерживается форматирование и вставка изображений"
            min-height="220px"
          />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="field-label">{{ t('products.origin') }} ({{ activeLocale.toUpperCase() }})</label>
            <input v-model="form.origin[activeLocale]" class="field-input" />
          </div>
          <div>
            <label class="field-label">{{ t('products.grind') }} ({{ activeLocale.toUpperCase() }})</label>
            <input v-model="form.grind[activeLocale]" class="field-input" />
          </div>
        </div>
      </div>

      <div class="card space-y-4">
        <h2 class="font-display text-lg">{{ t('products.classification') }}</h2>
        <div>
          <label class="field-label">{{ t('products.color') }}</label>
          <select v-model="form.color_slug" class="field-input">
            <option value="">{{ t('products.select_empty') }}</option>
            <option v-for="item in colors" :key="item.id" :value="item.slug">{{ pickLocalizedValue(item.label) }}</option>
          </select>
        </div>
        <div>
          <label class="field-label">{{ t('products.strain') }}</label>
          <select v-model="form.strain_slug" class="field-input">
            <option value="">{{ t('products.select_empty') }}</option>
            <option v-for="item in strains" :key="item.id" :value="item.slug">{{ pickLocalizedValue(item.label) }}</option>
          </select>
        </div>
        <div>
          <label class="field-label">{{ t('products.form') }}</label>
          <select v-model="form.form_slug" class="field-input">
            <option v-for="item in forms" :key="item.id" :value="item.slug">{{ pickLocalizedValue(item.label) }}</option>
          </select>
        </div>
        <div>
          <label class="field-label">{{ t('products.position') }}</label>
          <input v-model.number="form.position" type="number" class="field-input" />
        </div>
        <div>
          <label class="field-label">{{ t('products.published_at') }}</label>
          <input v-model="form.published_at" type="date" class="field-input" />
        </div>
        <label class="flex items-center gap-2 text-sm">
          <input v-model="form.in_stock" type="checkbox" />
          {{ t('products.stock_checkbox') }}
        </label>
      </div>

      <div class="card lg:col-span-2 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-display text-lg">{{ t('products.lab') }}</h2>
          <NuxtLink to="/lab-batches" class="text-xs text-moss-700 hover:underline">
            {{ t('nav.lab_batches') }} →
          </NuxtLink>
        </div>
        <p class="text-xs text-ink-700/60">
          Полные данные тестов с разбивкой по партиям и оригинальными PDF — в разделе «{{ t('nav.lab_batches') }}». Ниже — кэшированные показатели для быстрых фильтров каталога.
        </p>
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="field-label">{{ t('products.mitragynin') }} %</label>
            <input v-model.number="form.mitragynin" type="number" step="0.01" class="field-input" />
          </div>
          <div>
            <label class="field-label">7-OH-MG %</label>
            <input v-model.number="form.h7mg" type="number" step="0.001" class="field-input" />
          </div>
          <div>
            <label class="field-label">Чистота %</label>
            <input v-model.number="form.purity" type="number" step="0.01" class="field-input" />
          </div>
        </div>
        <div>
          <label class="field-label">{{ t('products.tested_at') }}</label>
          <input v-model="form.tested_at" type="date" class="field-input" />
        </div>
      </div>

      <div class="card lg:col-span-3 space-y-3">
        <h2 class="font-display text-lg">{{ t('products.variants') }}</h2>
        <table class="w-full">
          <thead>
            <tr>
              <th class="table-th">{{ t('products.size') }}</th>
              <th class="table-th">{{ t('products.unit') }}</th>
              <th class="table-th">{{ t('products.price') }}</th>
              <th class="table-th">{{ t('products.stock') }}</th>
              <th class="table-th">{{ t('products.sku') }}</th>
              <th />
            </tr>
          </thead>
          <tbody>
            <tr v-for="(variant, index) in form.variants" :key="index">
              <td class="table-td"><input v-model.number="variant.size" type="number" class="field-input w-24" /></td>
              <td class="table-td">
                <select v-model="variant.unit" class="field-input w-20">
                  <option value="g">g</option>
                  <option value="ml">ml</option>
                </select>
              </td>
              <td class="table-td"><input v-model.number="variant.price" type="number" class="field-input w-32" /></td>
              <td class="table-td"><input v-model.number="variant.stock" type="number" class="field-input w-24" /></td>
              <td class="table-td"><input v-model="variant.sku" class="field-input w-40" /></td>
              <td class="table-td">
                <button class="btn-danger" @click="form.variants.splice(index, 1)"><Trash2 :size="14" /></button>
              </td>
            </tr>
          </tbody>
        </table>
        <button class="btn-ghost" @click="form.variants.push({ size: 25, unit: 'g', price: 0, stock: 0, sku: '' })">
          {{ t('products.add_variant') }}
        </button>
      </div>

      <div v-if="!isNew" class="card lg:col-span-3 space-y-4">
        <AdminProductGallery
          :product-id="route.params.id as string"
          :main-image="form.main_image"
          @update:main-image="form.main_image = $event"
        />
      </div>
      <div v-else class="card lg:col-span-3 text-sm text-ink-700/60">
        Сохраните товар, чтобы загружать изображения галереи.
      </div>
    </div>
  </div>
</template>
