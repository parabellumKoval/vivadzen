<script setup lang="ts">
import { Plus, Trash2, GripVertical } from 'lucide-vue-next'
import draggable from 'vuedraggable'

const api = useApi()
const { contentLocales, primaryContentLocale, emptyLocalizedValue, normalizeLocalizedValue } = useAdminI18n()
const activeLocale = ref(primaryContentLocale.value)

type PaymentMethod = {
  id?: number
  code: string
  type: 'cod' | 'qr' | 'bank' | 'online'
  name: Record<string, string>
  description: Record<string, string>
  fee: number
  is_active: boolean
  position: number
  delivery_method_codes?: string[] | null
  settings?: Record<string, any> | null
}

const { data, refresh, pending } = await useAsyncData('payment-methods', () => api<any>('/payment-methods'))
const items = ref<PaymentMethod[]>([])
watch(data, (value) => { items.value = value?.data ?? [] }, { immediate: true })
const reordering = ref(false)

async function persistOrder() {
  const ids = items.value.map((m) => m.id).filter((id): id is number => !!id)
  if (!ids.length) return
  reordering.value = true
  try {
    const res = await api<any>('/payment-methods/reorder', {
      method: 'PUT',
      body: { order: ids },
    })
    items.value = res?.data ?? items.value
  } catch (e: any) {
    error.value = e?.data?.message || 'Не удалось сохранить порядок'
    await refresh()
  } finally {
    reordering.value = false
  }
}

const { data: deliveryRaw } = await useAsyncData('delivery-methods-for-payment', () => api<any>('/delivery-methods'))
const deliveryOptions = computed<Array<{ code: string; name: string }>>(() =>
  (deliveryRaw.value?.data ?? []).map((d: any) => ({
    code: d.code,
    name: d.name?.cs || d.code,
  })),
)

const editing = ref<PaymentMethod | null>(null)
const saving = ref(false)
const error = ref<string | null>(null)

function emptyMethod(type: PaymentMethod['type'] = 'online'): PaymentMethod {
  return {
    code: '',
    type,
    name: emptyLocalizedValue(),
    description: emptyLocalizedValue(),
    fee: 0,
    is_active: true,
    position: items.value.length + 1,
    delivery_method_codes: [],
    settings: {},
  }
}

function startEdit(item: PaymentMethod) {
  editing.value = {
    ...item,
    name: normalizeLocalizedValue(item.name),
    description: normalizeLocalizedValue(item.description),
    delivery_method_codes: item.delivery_method_codes ?? [],
  }
}

function startNew(type: PaymentMethod['type']) {
  editing.value = emptyMethod(type)
}

function toggleDeliveryCode(code: string) {
  if (!editing.value) return
  const list = editing.value.delivery_method_codes ?? []
  if (list.includes(code)) {
    editing.value.delivery_method_codes = list.filter((c) => c !== code)
  } else {
    editing.value.delivery_method_codes = [...list, code]
  }
}

async function save() {
  if (!editing.value) return
  saving.value = true
  error.value = null
  try {
    const payload = { ...editing.value }
    if (payload.id) {
      await api(`/payment-methods/${payload.id}`, { method: 'PUT', body: payload })
    } else {
      await api('/payment-methods', { method: 'POST', body: payload })
    }
    editing.value = null
    await refresh()
  } catch (e: any) {
    error.value = e?.data?.message || 'Ошибка сохранения'
  } finally {
    saving.value = false
  }
}

async function remove(item: PaymentMethod) {
  if (!item.id) return
  if (!confirm(`Удалить «${item.name?.cs || item.code}»?`)) return
  await api(`/payment-methods/${item.id}`, { method: 'DELETE' })
  await refresh()
}

async function toggleActive(item: PaymentMethod) {
  await api(`/payment-methods/${item.id}`, {
    method: 'PUT',
    body: { ...item, is_active: !item.is_active },
  })
  await refresh()
}

const TYPE_LABEL: Record<string, string> = {
  cod: 'Наложенный платёж',
  qr: 'QR-код',
  bank: 'Банк. перевод',
  online: 'Онлайн (карта)',
}
</script>

<template>
  <div class="space-y-6">
    <header class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-display">Способы оплаты</h1>
        <p class="text-sm text-ink-700/60">Размер комиссии, активность, совместимость со способами доставки.</p>
      </div>
      <div class="flex gap-2">
        <button class="btn btn-primary flex items-center gap-2" @click="startNew('online')">
          <Plus :size="16" /> Новый способ
        </button>
      </div>
    </header>

    <div v-if="pending" class="text-ink-700/60">Загрузка…</div>

    <table v-else class="w-full bg-white border border-ink-700/10 rounded-lg overflow-hidden">
      <thead class="bg-cream-50 text-left text-sm text-ink-700/70">
        <tr>
          <th class="p-3 w-10"></th>
          <th class="p-3">Название (CS)</th>
          <th class="p-3">Код</th>
          <th class="p-3">Тип</th>
          <th class="p-3 text-right">Комиссия</th>
          <th class="p-3">Совместимо с</th>
          <th class="p-3 text-center">Активна</th>
          <th class="p-3 w-32"></th>
        </tr>
      </thead>
      <draggable
        v-model="items"
        tag="tbody"
        item-key="id"
        handle=".drag-handle"
        :animation="150"
        ghost-class="row-ghost"
        @end="persistOrder"
      >
        <template #item="{ element: item }">
          <tr class="border-t border-ink-700/5" :class="{ 'opacity-60': reordering }">
            <td class="p-3">
              <button
                type="button"
                class="drag-handle text-ink-700/30 hover:text-ink-700 cursor-grab active:cursor-grabbing"
                title="Перетащите, чтобы изменить порядок"
              >
                <GripVertical :size="16" />
              </button>
            </td>
            <td class="p-3 font-medium">{{ item.name?.cs || '—' }}</td>
            <td class="p-3 font-mono text-xs text-ink-700/70">{{ item.code }}</td>
            <td class="p-3 text-sm">{{ TYPE_LABEL[item.type] }}</td>
            <td class="p-3 text-right">{{ item.fee }} Kč</td>
            <td class="p-3 text-sm text-ink-700/70">
              <span v-if="!item.delivery_method_codes?.length">Все способы</span>
              <span v-else>{{ item.delivery_method_codes.join(', ') }}</span>
            </td>
            <td class="p-3 text-center">
              <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" :checked="item.is_active" @change="toggleActive(item)" class="sr-only peer" />
                <div class="w-9 h-5 bg-ink-700/20 peer-checked:bg-moss-700 rounded-full relative transition-colors">
                  <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-4" />
                </div>
              </label>
            </td>
            <td class="p-3 text-right">
              <button class="text-sm text-moss-700 hover:underline mr-2" @click="startEdit(item)">Изменить</button>
              <button class="text-sm text-terracotta-700 hover:underline" @click="remove(item)"><Trash2 :size="14" class="inline" /></button>
            </td>
          </tr>
        </template>
      </draggable>
    </table>

    <div v-if="editing" class="fixed inset-0 bg-ink-700/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-auto">
        <header class="p-4 border-b border-ink-700/10 flex items-center justify-between">
          <h2 class="font-display text-lg">{{ editing.id ? 'Изменить' : 'Новый способ оплаты' }}</h2>
          <button class="text-ink-700/60 hover:text-ink-700" @click="editing = null">✕</button>
        </header>

        <div class="p-4 space-y-4">
          <p v-if="error" class="text-sm text-terracotta-700 bg-terracotta-700/10 p-2 rounded">{{ error }}</p>

          <div class="grid grid-cols-2 gap-3">
            <label class="block">
              <span class="text-xs text-ink-700/70 uppercase">Код</span>
              <input v-model="editing.code" class="input mt-1" placeholder="niftipay" :disabled="!!editing.id" />
            </label>
            <label class="block">
              <span class="text-xs text-ink-700/70 uppercase">Тип</span>
              <select v-model="editing.type" class="input mt-1">
                <option value="cod">Наложенный платёж</option>
                <option value="qr">QR-код</option>
                <option value="bank">Банковский перевод</option>
                <option value="online">Онлайн / карта</option>
              </select>
            </label>
          </div>

          <div class="border border-ink-700/10 rounded-lg">
            <div class="flex border-b border-ink-700/10 px-2">
              <button
                v-for="loc in contentLocales"
                :key="loc.code"
                class="px-3 py-2 text-xs uppercase"
                :class="activeLocale === loc.code ? 'border-b-2 border-moss-700 text-moss-700' : 'text-ink-700/50'"
                @click="activeLocale = loc.code"
              >
                {{ loc.native }}
              </button>
            </div>
            <div class="p-3 space-y-3">
              <label class="block">
                <span class="text-xs text-ink-700/70 uppercase">Название</span>
                <input v-model="editing.name[activeLocale]" class="input mt-1" />
              </label>
              <label class="block">
                <span class="text-xs text-ink-700/70 uppercase">Описание</span>
                <textarea v-model="editing.description[activeLocale]" class="input mt-1" rows="2"></textarea>
              </label>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <label class="block">
              <span class="text-xs text-ink-700/70 uppercase">Комиссия, Kč</span>
              <input v-model.number="editing.fee" type="number" min="0" class="input mt-1" />
            </label>
            <label class="block">
              <span class="text-xs text-ink-700/70 uppercase">Позиция</span>
              <input v-model.number="editing.position" type="number" min="0" class="input mt-1" />
            </label>
          </div>

          <div class="border border-ink-700/10 rounded-lg p-3">
            <p class="text-xs text-ink-700/70 uppercase mb-2">Совместимо со способами доставки</p>
            <p class="text-xs text-ink-700/50 mb-2">Если ничего не выбрано — доступно для всех способов доставки.</p>
            <div class="space-y-1">
              <label v-for="opt in deliveryOptions" :key="opt.code" class="flex items-center gap-2 text-sm">
                <input
                  type="checkbox"
                  :checked="editing.delivery_method_codes?.includes(opt.code)"
                  @change="toggleDeliveryCode(opt.code)"
                />
                <span>{{ opt.name }} <span class="text-xs text-ink-700/50 font-mono">{{ opt.code }}</span></span>
              </label>
            </div>
          </div>

          <label class="inline-flex items-center gap-2">
            <input type="checkbox" v-model="editing.is_active" />
            <span class="text-sm">Активна</span>
          </label>
        </div>

        <footer class="p-4 border-t border-ink-700/10 flex justify-end gap-2">
          <button class="btn btn-secondary" @click="editing = null">Отмена</button>
          <button class="btn btn-primary" :disabled="saving" @click="save">{{ saving ? 'Сохранение…' : 'Сохранить' }}</button>
        </footer>
      </div>
    </div>
  </div>
</template>

<style scoped>
.btn { padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 500; font-size: 0.875rem; }
.btn-primary { background: rgb(34, 65, 51); color: white; }
.btn-primary:hover { background: rgb(20, 45, 30); }
.btn-secondary { background: rgb(245, 240, 230); color: rgb(40, 40, 40); border: 1px solid rgba(0,0,0,0.1); }
.btn-secondary:hover { background: rgb(235, 228, 215); }
.input { display: block; width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(0,0,0,0.1); border-radius: 0.375rem; background: white; font-size: 0.875rem; }
.input:focus { outline: 2px solid rgb(34, 65, 51); outline-offset: -1px; }
.row-ghost { background: rgba(58, 87, 40, 0.08); }
</style>
