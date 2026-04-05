import type { Ref } from 'vue'
import type { SettingsDataset } from '../utils/settings-helpers'

const STOREFRONT_KEY_OVERRIDES: Record<string, string> = {
  'shipping.add_to_order_enabled': 'shipping.kratom.add_to_order_enabled',
  'shipping.free_enabled': 'shipping.kratom.free_enabled',
  'shipping.free_min_price': 'shipping.kratom.free_min_price',
  'shipping.methods': 'shipping.kratom.methods',
  'payment.methods': 'payment.kratom.methods',
}

const STOREFRONT_STRICT_KEYS = new Set([
  'shipping.methods',
  'payment.methods',
])

const getByPath = (root: any, key: string) => {
  if (!root || typeof root !== 'object') {
    return undefined
  }

  if (Object.prototype.hasOwnProperty.call(root, key)) {
    return root[key]
  }

  const parts = key.split('.')
  let current: any = root
  for (const part of parts) {
    if (current && Object.prototype.hasOwnProperty.call(current, part)) {
      current = current[part]
      continue
    }

    return undefined
  }

  return current
}

export function useSettings() {
  const { $getSetting, $settings, $settingsAll } = useNuxtApp() as any

  const get = (key: string, def?: any) => {
    const overrideKey = STOREFRONT_KEY_OVERRIDES[key]

    if (overrideKey) {
      const overrideValue = getByPath($settings.value, overrideKey)
      if (overrideValue !== undefined && overrideValue !== null) {
        return overrideValue
      }

      if (STOREFRONT_STRICT_KEYS.has(key)) {
        return def
      }
    }

    return $getSetting(key, def)
  }

  return {
    all: $settings as Ref<Record<string, any>>,
    variants: $settingsAll as Ref<SettingsDataset>,
    get,
  }
}
