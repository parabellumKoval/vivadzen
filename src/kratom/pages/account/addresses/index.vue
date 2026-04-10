<script setup lang="ts">
import type { SavedDeliveryAddress } from '~/composables/useSavedDeliveryAddresses'

const { t } = useI18n()
const { user: authUser, updateProfile, init, me } = useAuth()
const noty = useNoty()
const { region } = useRegion()
const {
  createEmptyAddress,
  normalizeAddress,
  needsWarehouse,
  needsAddress,
  requiresHouse,
  requiresZip,
  buildAddressSummary,
  sanitizeAddresses,
} = useSavedDeliveryAddresses()
const { methods } = useDelivery()

await init()
await me(true).catch(() => {})

definePageMeta({
  crumb: {
    name: 'title.account.addresses',
    item: '/account/addresses',
  },
  tab: 'addresses',
})

const savedAddresses = ref<SavedDeliveryAddress[]>([])
const isSavingAddresses = ref(false)

const extractSavedAddresses = (value: Record<string, any> | null): SavedDeliveryAddress[] => {
  const rawValue =
    value?.saved_delivery_addresses
    ?? value?.meta?.saved_delivery_addresses
    ?? value?.meta?.delivery_addresses
    ?? []

  if (Array.isArray(rawValue)) {
    return rawValue.map((item: SavedDeliveryAddress) => normalizeAddress(item))
  }

  if (typeof rawValue === 'string') {
    try {
      const parsed = JSON.parse(rawValue)

      if (Array.isArray(parsed)) {
        return parsed.map((item: SavedDeliveryAddress) => normalizeAddress(item))
      }
    } catch {
      return []
    }
  }

  return []
}

const hydrateFromAuth = (value: Record<string, any> | null) => {
  savedAddresses.value = extractSavedAddresses(value)
}

watch(
  authUser,
  (value) => {
    hydrateFromAuth(value)
  },
  { immediate: true, deep: true },
)

const addAddress = () => {
  savedAddresses.value.unshift({
    ...createEmptyAddress(),
    country: String(region.value || '').toUpperCase(),
  })
}

const removeAddress = (index: number) => {
  savedAddresses.value.splice(index, 1)
}

const saveAddresses = async () => {
  isSavingAddresses.value = true

  try {
    await updateProfile({
      saved_delivery_addresses: sanitizeAddresses(savedAddresses.value, String(region.value || '').toUpperCase()),
    })

    noty.setNoty({ content: t('noty.update.success'), type: 'success' })
  } catch (error) {
    noty.setNoty({ content: t('noty.update.fail'), type: 'error' }, 7000)
  } finally {
    isSavingAddresses.value = false
  }
}
</script>

<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div class="account-addresses">
    <section class="account-addresses__section">
      <div class="account-addresses__section-header">
        <div>
          <div class="account-addresses__eyebrow">{{ t('saved_addresses') }}</div>
          <h2 class="account-addresses__title">{{ t('title.account.addresses') }}</h2>
          <p class="account-addresses__text">{{ t('delivery_data') }}</p>
        </div>

        <button type="button" class="button secondary" @click="addAddress">{{ t('add_address') }}</button>
      </div>

      <div v-if="savedAddresses.length" class="account-addresses__list">
        <article v-for="(address, index) in savedAddresses" :key="address.id || `address-${index}`" class="address-card">
          <header class="address-card__header">
            <div>
              <div class="address-card__title">{{ address.title || buildAddressSummary(address) }}</div>
              <div class="address-card__summary">{{ buildAddressSummary(address) }}</div>
            </div>
            <button type="button" class="button color-dark" @click="removeAddress(index)">{{ t('button.delete') }}</button>
          </header>

          <div class="account-addresses__grid">
            <label class="account-addresses__select-wrapper">
              <span>{{ t('delivery_method') }}</span>
              <select v-model="address.method" class="account-addresses__select">
                <option v-for="method in methods" :key="method.key" :value="method.key">
                  {{ method.title }}
                </option>
              </select>
            </label>

            <form-text v-model="address.title" :placeholder="t('address_title')" />
            <form-text v-model="address.settlement" :placeholder="t('address_fields.city')" />
            <form-text v-model="address.country" :placeholder="t('address_fields.country')" />

            <template v-if="needsWarehouse(address.method)">
              <form-text v-model="address.warehouse" :placeholder="t('warehouse')" />
            </template>

            <template v-if="needsAddress(address.method)">
              <form-text v-model="address.street" :placeholder="t('address_fields.address_1')" />
              <form-text v-if="requiresHouse(address.method)" v-model="address.house" :placeholder="t('house')" />
              <form-text v-model="address.room" :placeholder="t('flat')" />
              <form-text v-if="requiresZip(address.method)" v-model="address.zip" :placeholder="t('address_fields.postcode')" />
            </template>
          </div>
        </article>
      </div>

      <div v-else class="account-addresses__empty">{{ t('no_saved_addresses') }}</div>

      <button type="button" class="button primary" :class="{ loading: isSavingAddresses }" @click="saveAddresses">
        {{ t('button.save') }}
      </button>
    </section>
  </div>
</template>

<style scoped lang="scss">
.account-addresses {
  display: grid;
  gap: 18px;
}

.account-addresses__section {
  display: grid;
  gap: 18px;
}

.account-addresses__section-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.account-addresses__eyebrow {
  margin-bottom: 8px;
  color: #8a5a2b;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.account-addresses__title {
  color: #1f2b1d;
  font-size: 28px;
  line-height: 1.05;
}

.account-addresses__text {
  margin-top: 8px;
  max-width: 640px;
  color: #667160;
  line-height: 1.6;
}

.account-addresses__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 14px;

  @include desktop {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.account-addresses__list {
  display: grid;
  gap: 16px;
}

.address-card {
  display: grid;
  gap: 16px;
  padding: 20px;
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.68);
  border: 1px solid rgba(74, 91, 68, 0.12);
}

.address-card__header {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  width: 100%;

  @include desktop {
    justify-content: space-between;
  }
}

.address-card__title {
  color: #1f2b1d;
  font-size: 18px;
  font-weight: 700;
}

.address-card__summary {
  margin-top: 4px;
  color: #667160;
  font-size: 14px;
  line-height: 1.5;
}

.account-addresses__empty {
  padding: 28px;
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.68);
  color: #667160;
  text-align: center;
}

.account-addresses__select-wrapper {
  display: grid;
  gap: 8px;
  color: #667160;
  font-size: 14px;
}

.account-addresses__select {
  min-height: 56px;
  padding: 0 16px;
  border-radius: 18px;
  border: 1px solid rgba(74, 91, 68, 0.16);
  background: #fffdf9;
  color: #1f2b1d;
}

@media (max-width: 767px) {
  .account-addresses__section-header,
  .address-card__header {
    display: grid;
  }
}
</style>
