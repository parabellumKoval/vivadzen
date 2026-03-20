import type { Ref } from 'vue'
import type { SettingsDataset } from '../utils/settings-helpers'

export function useSettings() {
  const { $getSetting, $settings, $settingsAll } = useNuxtApp() as any
  return {
    all: $settings as Ref<Record<string, any>>,
    variants: $settingsAll as Ref<SettingsDataset>,
    get: $getSetting as (key: string, def?: any) => any,
  }
}
