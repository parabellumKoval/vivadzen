import type { TgOrder } from '~/types/tg'
import { useTgUiStore } from '~/stores/ui'

const normalizeOrders = (payload: any): TgOrder[] => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.orders)) return payload.orders
  if (Array.isArray(payload?.orders?.data)) return payload.orders.data
  return []
}

export const useTgOrders = () => {
  const { $api } = useNuxtApp()
  const ui = useTgUiStore()
  const { t } = useTgI18n()
  const { isTelegram } = useTelegram()

  const orders = ref<TgOrder[]>([])
  const loading = ref(false)

  const loadOrders = async () => {
    loading.value = true
    try {
      const response = isTelegram.value
        ? await $api('/tg/orders', { method: 'GET' })
        : await $api('/order/get', {
            method: 'POST',
            body: {}
          })
      orders.value = normalizeOrders(response)
    } catch {
      orders.value = []
      ui.showToast(t('loading_error'), 'error')
    } finally {
      loading.value = false
    }
  }

  return {
    orders,
    loading,
    loadOrders
  }
}
