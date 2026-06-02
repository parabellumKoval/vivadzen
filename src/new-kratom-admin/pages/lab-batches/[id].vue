<script setup lang="ts">
import { Save, Trash2, ArrowLeft, Plus, Upload, FileText, ExternalLink } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const api = useApi()
const config = useRuntimeConfig()
const { t, pickLocalizedValue } = useAdminI18n()

const isNew = route.params.id === 'new'

type TestRow = {
  name: string
  symbol?: string
  value: string | number
  uncertainty?: number | null
  below_loq?: boolean
  unit?: string
  limit?: string | number | null
  status?: 'V' | 'Vn' | 'N' | 'X'
}

type LabBatchForm = {
  lot: string
  product_name: string
  strains: string[]
  package: string
  mass: string
  lab_name: string
  received_at: string
  issued_at: string
  published_at: string
  tests: Record<string, TestRow[]>
  product_ids: number[]
}

const GROUPS = [
  { key: 'active', label: 'Аktivní látky (mitragynin, 7-OH)' },
  { key: 'metals', label: 'Těžké kovy (Pb, Cd, Hg, As, Ni)' },
  { key: 'mycotoxins', label: 'Mykotoxiny (aflatoxiny)' },
  { key: 'pah', label: 'Polyaromatické uhlovodíky (PAU)' },
  { key: 'microbiology', label: 'Mikrobiologie (CPM, E. coli…)' },
] as const

function emptyForm(): LabBatchForm {
  return {
    lot: '',
    product_name: '',
    strains: [],
    package: 'Doypack ZIP, PE sáček',
    mass: '',
    lab_name: 'VŠCHT Praha',
    received_at: '',
    issued_at: '',
    published_at: new Date().toISOString().slice(0, 10),
    tests: {
      active: [],
      metals: [],
      mycotoxins: [],
      pah: [],
      microbiology: [],
    },
    product_ids: [],
  }
}

const form = ref<LabBatchForm>(emptyForm())
const files = ref<any[]>([])
const products = ref<any[]>([])
const saving = ref(false)
const uploading = ref(false)
const error = ref('')
const newFileMeta = ref({ file_no: '', label: '', tested_at: '' })
const newStrain = ref('')

function hydrate(payload: any) {
  form.value = {
    lot: payload.lot || '',
    product_name: payload.product_name || '',
    strains: Array.isArray(payload.strains) ? payload.strains : [],
    package: payload.package || '',
    mass: payload.mass || '',
    lab_name: payload.lab_name || 'VŠCHT Praha',
    received_at: payload.received_at || '',
    issued_at: payload.issued_at || '',
    published_at: payload.published_at || '',
    tests: {
      active: payload.tests?.active || [],
      metals: payload.tests?.metals || [],
      mycotoxins: payload.tests?.mycotoxins || [],
      pah: payload.tests?.pah || [],
      microbiology: payload.tests?.microbiology || [],
    },
    product_ids: payload.product_ids || [],
  }
  files.value = payload.files || []
}

if (!isNew) {
  const { data } = await useFetch<any>(`/lab-batches/${route.params.id}`, {
    baseURL: config.public.apiBase,
    headers: { Authorization: `Bearer ${useAuthStore().token}` },
  })
  if (data.value?.data) {
    hydrate(data.value.data)
  }
}

// Загружаем список товаров для multi-select привязки
{
  const { data: prods } = await useAsyncData('lab-products', () => api<any>('/products', { params: { per_page: 200 } }))
  products.value = prods.value?.data?.data ?? []
}

function addStrain() {
  const v = newStrain.value.trim()
  if (!v) return
  if (!form.value.strains.includes(v)) {
    form.value.strains.push(v)
  }
  newStrain.value = ''
}

function removeStrain(i: number) {
  form.value.strains.splice(i, 1)
}

function addRow(group: string) {
  form.value.tests[group].push({
    name: '',
    value: '',
    unit: '',
    uncertainty: null,
    limit: null,
    status: 'V',
  })
}

function removeRow(group: string, i: number) {
  form.value.tests[group].splice(i, 1)
}

function toggleProduct(id: number) {
  const idx = form.value.product_ids.indexOf(id)
  if (idx === -1) form.value.product_ids.push(id)
  else form.value.product_ids.splice(idx, 1)
}

async function save() {
  saving.value = true
  error.value = ''
  try {
    const payload = {
      ...form.value,
      // Числовые поля могут прийти строками — нормализуем (минимально)
      tests: Object.fromEntries(
        Object.entries(form.value.tests).map(([g, rows]) => [
          g,
          rows.map((r) => ({
            ...r,
            uncertainty: r.uncertainty === '' || r.uncertainty == null ? null : Number(r.uncertainty),
          })),
        ]),
      ),
    }

    if (isNew) {
      const res = await api<any>('/lab-batches', { method: 'POST', body: payload })
      router.push(`/lab-batches/${res.data.id}`)
    } else {
      const res = await api<any>(`/lab-batches/${route.params.id}`, { method: 'PUT', body: payload })
      hydrate(res.data)
    }
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось сохранить'
  } finally {
    saving.value = false
  }
}

async function remove() {
  if (!confirm(t('lab_batches.delete_confirm'))) return
  await api(`/lab-batches/${route.params.id}`, { method: 'DELETE' })
  router.push('/lab-batches')
}

async function uploadFiles(event: Event) {
  const input = event.target as HTMLInputElement
  if (!input.files?.length) return

  const fd = new FormData()
  for (const f of Array.from(input.files)) fd.append('files[]', f)
  if (newFileMeta.value.file_no) fd.append('file_no', newFileMeta.value.file_no)
  if (newFileMeta.value.label) fd.append('label', newFileMeta.value.label)
  if (newFileMeta.value.tested_at) fd.append('tested_at', newFileMeta.value.tested_at)

  uploading.value = true
  try {
    const res = await api<any>(`/lab-batches/${route.params.id}/files`, { method: 'POST', body: fd })
    files.value.push(...(res.data || []))
    newFileMeta.value = { file_no: '', label: '', tested_at: '' }
    input.value = ''
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось загрузить'
  } finally {
    uploading.value = false
  }
}

async function deleteFile(id: number) {
  if (!confirm('Удалить файл?')) return
  await api(`/lab-batches/${route.params.id}/files/${id}`, { method: 'DELETE' })
  files.value = files.value.filter((f) => f.id !== id)
}

async function patchFile(file: any) {
  const res = await api<any>(`/lab-batches/${route.params.id}/files/${file.id}`, {
    method: 'PUT',
    body: { file_no: file.file_no, label: file.label, tested_at: file.tested_at },
  })
  Object.assign(file, res.data)
}

function fileUrl(file: any): string {
  if (!file.url) return '#'
  if (file.url.startsWith('http')) return file.url
  return (config.public.siteBase as string).replace(/\/$/, '') + file.url
}
</script>

<template>
  <div class="space-y-4 max-w-6xl">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <NuxtLink to="/lab-batches" class="btn-ghost"><ArrowLeft :size="18" /></NuxtLink>
        <h1 class="text-2xl font-display">
          {{ isNew ? t('lab_batches.new') : `Šarže ${form.lot}` }}
        </h1>
      </div>
      <div class="flex gap-2">
        <a v-if="!isNew && form.lot" :href="`${config.public.siteBase}/cs/sarze/${form.lot}`" target="_blank" class="btn-ghost">
          <ExternalLink :size="16" />
          {{ t('lab_batches.open_public') }}
        </a>
        <button v-if="!isNew" class="btn-danger" @click="remove"><Trash2 :size="16" />{{ t('lab_batches.delete') }}</button>
        <button class="btn-primary" :disabled="saving" @click="save">
          <Save :size="16" />{{ saving ? t('products.saving') : t('products.save') }}
        </button>
      </div>
    </div>

    <div v-if="error" class="card border-terracotta-500 text-terracotta-700">{{ error }}</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Meta -->
      <div class="card lg:col-span-2 space-y-4">
        <h2 class="font-display text-lg">{{ t('lab_batches.meta') }}</h2>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="field-label">{{ t('lab_batches.lot') }} *</label>
            <input v-model="form.lot" class="field-input font-mono" placeholder="L08202504" />
          </div>
          <div>
            <label class="field-label">{{ t('lab_batches.lab_name') }}</label>
            <input v-model="form.lab_name" class="field-input" />
          </div>
        </div>

        <div>
          <label class="field-label">{{ t('lab_batches.product_name') }}</label>
          <input v-model="form.product_name" class="field-input" placeholder="Rurut Kratom prášek" />
        </div>

        <div>
          <label class="field-label">{{ t('lab_batches.strains') }}</label>
          <div class="flex flex-wrap gap-2 mb-2">
            <span v-for="(s, i) in form.strains" :key="i" class="inline-flex items-center gap-1 bg-moss-100 text-moss-900 text-xs px-2 py-1 rounded">
              {{ s }}
              <button class="text-moss-700 hover:text-terracotta-700" @click="removeStrain(i)">×</button>
            </span>
          </div>
          <div class="flex gap-2">
            <input v-model="newStrain" class="field-input" placeholder="Bílý Slon" @keydown.enter.prevent="addStrain" />
            <button class="btn-ghost" @click="addStrain"><Plus :size="14" />{{ t('lab_batches.add_strain') }}</button>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="field-label">{{ t('lab_batches.package') }}</label>
            <input v-model="form.package" class="field-input" />
          </div>
          <div>
            <label class="field-label">{{ t('lab_batches.mass') }}</label>
            <input v-model="form.mass" class="field-input" placeholder="400 g" />
          </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="field-label">{{ t('lab_batches.received_at') }}</label>
            <input v-model="form.received_at" type="date" class="field-input" />
          </div>
          <div>
            <label class="field-label">{{ t('lab_batches.issued_at') }}</label>
            <input v-model="form.issued_at" type="date" class="field-input" />
          </div>
          <div>
            <label class="field-label">{{ t('lab_batches.published_at') }}</label>
            <input v-model="form.published_at" type="date" class="field-input" />
          </div>
        </div>
      </div>

      <!-- Products link -->
      <div class="card space-y-3">
        <h2 class="font-display text-lg">{{ t('lab_batches.linked_products') }}</h2>
        <p class="text-xs text-ink-700/60">{{ t('lab_batches.linked_products_hint') }}</p>
        <div class="max-h-96 overflow-auto space-y-1 border border-ink-700/10 rounded p-2">
          <label v-for="p in products" :key="p.id" class="flex items-center gap-2 text-sm py-1 cursor-pointer hover:bg-cream-50/50 px-1 rounded">
            <input type="checkbox" :checked="form.product_ids.includes(p.id)" @change="toggleProduct(p.id)" />
            <span class="flex-1">{{ pickLocalizedValue(p.name) || p.slug }}</span>
            <span class="text-xs font-mono text-ink-700/40">{{ p.slug }}</span>
          </label>
          <div v-if="!products.length" class="text-xs text-ink-700/40 text-center py-4">—</div>
        </div>
      </div>

      <!-- Tests groups -->
      <div v-for="g in GROUPS" :key="g.key" class="card lg:col-span-3 space-y-3">
        <div class="flex items-center justify-between">
          <h2 class="font-display text-lg">{{ g.label }}</h2>
          <button class="btn-ghost" @click="addRow(g.key)">
            <Plus :size="14" />{{ t('lab_batches.add_row') }}
          </button>
        </div>

        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-ink-700/10 text-xs text-ink-700/60">
              <th class="text-left py-2">{{ t('lab_batches.row_name') }}</th>
              <th class="text-left py-2" v-if="g.key === 'metals'">{{ t('lab_batches.row_symbol') }}</th>
              <th class="text-left py-2">{{ t('lab_batches.row_value') }}</th>
              <th class="text-left py-2">{{ t('lab_batches.row_uncertainty') }}</th>
              <th class="text-left py-2">{{ t('lab_batches.row_unit') }}</th>
              <th class="text-left py-2">{{ t('lab_batches.row_limit') }}</th>
              <th class="text-left py-2">LOQ</th>
              <th class="text-left py-2">{{ t('lab_batches.row_status') }}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, i) in form.tests[g.key]" :key="i" class="border-b border-ink-700/5">
              <td class="py-1.5"><input v-model="row.name" class="field-input" /></td>
              <td v-if="g.key === 'metals'" class="py-1.5 w-20"><input v-model="row.symbol" class="field-input font-mono text-center" /></td>
              <td class="py-1.5 w-32"><input v-model="row.value" class="field-input" /></td>
              <td class="py-1.5 w-24"><input v-model.number="row.uncertainty" type="number" step="0.0001" class="field-input" /></td>
              <td class="py-1.5 w-24"><input v-model="row.unit" class="field-input" /></td>
              <td class="py-1.5 w-24"><input v-model="row.limit" class="field-input" /></td>
              <td class="py-1.5 w-16 text-center"><input v-model="row.below_loq" type="checkbox" /></td>
              <td class="py-1.5 w-24">
                <select v-model="row.status" class="field-input">
                  <option value="V">V — PASS</option>
                  <option value="Vn">Vn — PASS±</option>
                  <option value="N">N — FAIL</option>
                  <option value="X">X — INFO</option>
                </select>
              </td>
              <td class="py-1.5 w-10">
                <button class="text-terracotta-700 hover:text-terracotta-900" @click="removeRow(g.key, i)"><Trash2 :size="14" /></button>
              </td>
            </tr>
            <tr v-if="!form.tests[g.key].length">
              <td :colspan="g.key === 'metals' ? 9 : 8" class="py-3 text-center text-xs text-ink-700/40">—</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PDF protocols -->
      <div v-if="!isNew" class="card lg:col-span-3 space-y-3">
        <h2 class="font-display text-lg">{{ t('lab_batches.protocols') }}</h2>
        <p class="text-xs text-ink-700/60">{{ t('lab_batches.protocols_hint') }}</p>

        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-ink-700/10 text-xs text-ink-700/60">
              <th class="text-left py-2">{{ t('lab_batches.file_no') }}</th>
              <th class="text-left py-2">{{ t('lab_batches.file_label') }}</th>
              <th class="text-left py-2">{{ t('lab_batches.file_date') }}</th>
              <th class="text-left py-2">{{ t('lab_batches.file') }}</th>
              <th></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="f in files" :key="f.id" class="border-b border-ink-700/5">
              <td class="py-1.5 w-32"><input v-model="f.file_no" class="field-input font-mono" @blur="patchFile(f)" /></td>
              <td class="py-1.5"><input v-model="f.label" class="field-input" @blur="patchFile(f)" /></td>
              <td class="py-1.5 w-36"><input v-model="f.tested_at" type="date" class="field-input" @blur="patchFile(f)" /></td>
              <td class="py-1.5">
                <a :href="fileUrl(f)" target="_blank" class="text-moss-700 hover:underline inline-flex items-center gap-1 text-xs">
                  <FileText :size="14" /> {{ f.original_name || f.path }}
                </a>
              </td>
              <td class="py-1.5 text-xs text-ink-700/50">{{ f.size ? `${Math.round(f.size / 1024)} КБ` : '' }}</td>
              <td class="py-1.5 w-10">
                <button class="text-terracotta-700 hover:text-terracotta-900" @click="deleteFile(f.id)"><Trash2 :size="14" /></button>
              </td>
            </tr>
            <tr v-if="!files.length">
              <td colspan="6" class="py-3 text-center text-xs text-ink-700/40">—</td>
            </tr>
          </tbody>
        </table>

        <div class="grid grid-cols-4 gap-2 pt-4 border-t border-ink-700/10">
          <div>
            <label class="field-label">{{ t('lab_batches.file_no') }}</label>
            <input v-model="newFileMeta.file_no" class="field-input font-mono" placeholder="ML 63/26" />
          </div>
          <div class="col-span-2">
            <label class="field-label">{{ t('lab_batches.file_label') }}</label>
            <input v-model="newFileMeta.label" class="field-input" placeholder="Aktivní látky + těžké kovy" />
          </div>
          <div>
            <label class="field-label">{{ t('lab_batches.file_date') }}</label>
            <input v-model="newFileMeta.tested_at" type="date" class="field-input" />
          </div>
          <div class="col-span-4">
            <label class="btn-primary cursor-pointer inline-flex">
              <Upload :size="16" />
              {{ uploading ? t('lab_batches.uploading') : t('lab_batches.upload_pdf') }}
              <input type="file" accept="application/pdf" multiple class="hidden" :disabled="uploading" @change="uploadFiles" />
            </label>
          </div>
        </div>
      </div>

      <div v-else class="card lg:col-span-3 text-sm text-ink-700/60">
        {{ t('lab_batches.save_first_for_files') }}
      </div>
    </div>
  </div>
</template>
