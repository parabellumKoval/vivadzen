<script setup lang="ts">
import { useTgUiStore } from '~/stores/ui'
import { useTgUserStore, type TgSavedAddress } from '~/stores/user'

const { t } = useTgI18n()
const { pathFor, locale } = useTgRouting()
const { user, webApp, haptic } = useTelegram()
const userStore = useTgUserStore()
const ui = useTgUiStore()
const { deliveryMethods, loadSettings } = useTgCheckoutOptions()
const {
  loadProfile,
  saveProfile,
  saveAddress,
  updateAddress,
  deleteAddress,
  loading: profileLoading
} = useTgProfileApi()

const profileForm = reactive({
  first_name: '',
  last_name: '',
  phone: '',
  email: '',
  avatar: ''
})

const addressForm = reactive({
  id: '',
  deliveryMethod: '',
  settlement: '',
  street: '',
  house: '',
  room: '',
  zip: '',
  warehouse: '',
  title: ''
})

const savingProfile = ref(false)
const savingAddress = ref(false)
const editingAddressId = ref('')

const savedAddressesCount = computed(() => userStore.addresses.length)
const addressNeedsWarehouse = computed(() => String(addressForm.deliveryMethod || '').includes('warehouse'))
const addressNeedsAddress = computed(() => String(addressForm.deliveryMethod || '').includes('address'))

const syncProfileForm = () => {
  profileForm.first_name = userStore.checkoutProfile.first_name || ''
  profileForm.last_name = userStore.checkoutProfile.last_name || ''
  profileForm.phone = userStore.checkoutProfile.phone || ''
  profileForm.email = userStore.checkoutProfile.email || ''
  profileForm.avatar = userStore.checkoutProfile.avatar || userStore.user?.photo_url || ''
}

const resetAddressForm = () => {
  editingAddressId.value = ''
  Object.assign(addressForm, {
    id: '',
    deliveryMethod: '',
    settlement: '',
    street: '',
    house: '',
    room: '',
    zip: '',
    warehouse: '',
    title: ''
  })
}

const editAddress = (address: TgSavedAddress) => {
  editingAddressId.value = address.id
  Object.assign(addressForm, {
    id: address.id,
    deliveryMethod: address.deliveryMethod || '',
    settlement: address.settlement || '',
    street: address.street || '',
    house: address.house || '',
    room: address.room || '',
    zip: address.zip || '',
    warehouse: address.warehouse || '',
    title: address.title || ''
  })
}

const addressLine = (address: TgSavedAddress) => {
  const streetLine = [address.street, address.house, address.room].filter(Boolean).join(', ')
  return [address.settlement, address.warehouse || streetLine || address.zip].filter(Boolean).join(', ') || address.title
}

const deliveryMethodTitle = (key: string | null) => {
  return deliveryMethods.value.find((method) => method.key === key)?.title || key || t('delivery')
}

const saveProfileForm = async () => {
  if (savingProfile.value) return

  savingProfile.value = true

  const profile = {
    first_name: profileForm.first_name,
    last_name: profileForm.last_name,
    phone: profileForm.phone,
    email: profileForm.email,
    avatar: profileForm.avatar || userStore.avatarUrl || null
  }

  try {
    await saveProfile(profile)
    syncProfileForm()
    ui.showToast(t('profile_saved'))
    haptic('success')
  } finally {
    savingProfile.value = false
  }
}

const saveAddressForm = async () => {
  if (savingAddress.value || !addressForm.deliveryMethod) return

  savingAddress.value = true

  const address: TgSavedAddress = {
    id: editingAddressId.value || addressForm.id || `addr-${Date.now()}`,
    deliveryMethod: addressForm.deliveryMethod || null,
    settlement: addressForm.settlement || null,
    street: addressForm.street || null,
    house: addressForm.house || null,
    room: addressForm.room || null,
    zip: addressForm.zip || null,
    warehouse: addressForm.warehouse || null,
    price: null,
    priceCurrency: null,
    title: addressForm.title || [addressForm.settlement, addressForm.warehouse || addressForm.street || addressForm.zip].filter(Boolean).join(', ') || t('fallback_address_title'),
    updatedAt: new Date().toISOString()
  }

  try {
    if (editingAddressId.value) {
      await updateAddress(address)
    } else {
      await saveAddress(address)
    }

    resetAddressForm()
    ui.showToast(t('address_saved'))
    haptic('success')
  } finally {
    savingAddress.value = false
  }
}

const removeAddress = async (addressId: string) => {
  await deleteAddress(addressId)
  if (editingAddressId.value === addressId) {
    resetAddressForm()
  }
  ui.showToast(t('address_deleted'))
  haptic('selection')
}

onMounted(async () => {
  userStore.sync(user.value, webApp.value?.initData || '')
  await Promise.all([
    loadSettings(),
    loadProfile()
  ])
  syncProfileForm()
})
</script>

<template>
  <TgLayout :title="t('account')" :show-lang="true">
    <section class="tg-page account-page">
      <div class="account-card">
        <img
          v-if="userStore.avatarUrl"
          :src="userStore.avatarUrl"
          :alt="userStore.fullName"
          class="account-card__avatar"
        >
        <div v-else class="account-card__avatar account-card__avatar--fallback">
          {{ (userStore.fullName || 'V').slice(0, 1).toUpperCase() }}
        </div>
        <div>
          <h1>{{ userStore.fullName || t('telegram_user') }}</h1>
          <p>{{ userStore.usernameLabel || t('mini_app') }}</p>
        </div>
      </div>

      <form class="account-section" @submit.prevent="saveProfileForm">
        <div class="account-section__header">
          <h2>{{ t('recipient') }}</h2>
          <small v-if="profileLoading">{{ t('loading') }}</small>
        </div>

        <div class="account-form">
          <label>
            <span>{{ t('first_name') }}</span>
            <input v-model="profileForm.first_name" class="tg-field">
          </label>
          <label>
            <span>{{ t('last_name') }}</span>
            <input v-model="profileForm.last_name" class="tg-field">
          </label>
          <label>
            <span>{{ t('phone') }}</span>
            <input v-model="profileForm.phone" class="tg-field" inputmode="tel">
          </label>
          <label>
            <span>{{ t('email') }}</span>
            <input v-model="profileForm.email" class="tg-field" type="email" inputmode="email">
          </label>
        </div>

        <button type="submit" class="tg-btn" :disabled="savingProfile">
          {{ savingProfile ? t('loading') : t('save') }}
        </button>
      </form>

      <section class="account-section">
        <div class="account-section__header">
          <h2>{{ t('saved_addresses') }}</h2>
          <small>{{ savedAddressesCount }}</small>
        </div>

        <div v-if="userStore.addresses.length" class="address-list">
          <article v-for="address in userStore.addresses" :key="address.id" class="address-card">
            <div>
              <strong>{{ address.title || addressLine(address) }}</strong>
              <span>{{ deliveryMethodTitle(address.deliveryMethod) }}</span>
              <small>{{ addressLine(address) }}</small>
            </div>
            <div class="address-card__actions">
              <button type="button" class="account-link" @click="editAddress(address)">{{ t('edit') }}</button>
              <button type="button" class="account-link account-link--danger" @click="removeAddress(address.id)">{{ t('delete') }}</button>
            </div>
          </article>
        </div>
        <p v-else class="account-muted">{{ t('no_saved_addresses') }}</p>

        <form class="account-form account-form--address" @submit.prevent="saveAddressForm">
          <h3>{{ editingAddressId ? t('edit_address') : t('add_address') }}</h3>

          <label>
            <span>{{ t('delivery') }}</span>
            <select v-model="addressForm.deliveryMethod" class="tg-field">
              <option value="">{{ t('select_delivery') }}</option>
              <option v-for="method in deliveryMethods" :key="method.key" :value="method.key">
                {{ method.title }} · {{ method.label }}
              </option>
            </select>
          </label>

          <label>
            <span>{{ t('city') }}</span>
            <input v-model="addressForm.settlement" class="tg-field">
          </label>

          <label v-if="addressNeedsWarehouse">
            <span>{{ t('warehouse') }}</span>
            <input v-model="addressForm.warehouse" class="tg-field">
          </label>

          <template v-if="addressNeedsAddress">
            <label>
              <span>{{ t('address') }}</span>
              <input v-model="addressForm.street" class="tg-field">
            </label>
            <label>
              <span>{{ t('house') }}</span>
              <input v-model="addressForm.house" class="tg-field">
            </label>
            <label>
              <span>{{ t('flat') }}</span>
              <input v-model="addressForm.room" class="tg-field">
            </label>
            <label>
              <span>{{ t('zip') }}</span>
              <input v-model="addressForm.zip" class="tg-field">
            </label>
          </template>

          <label>
            <span>{{ t('details') }}</span>
            <input v-model="addressForm.title" class="tg-field">
          </label>

          <div class="account-actions">
            <button type="submit" class="tg-btn" :disabled="savingAddress || !addressForm.deliveryMethod">
              {{ savingAddress ? t('loading') : t('save') }}
            </button>
            <button v-if="editingAddressId" type="button" class="tg-btn tg-btn--ghost" @click="resetAddressForm">
              {{ t('cancel') }}
            </button>
          </div>
        </form>
      </section>

      <div class="settings-list">
        <div class="settings-list__row">
          <span class="settings-list__label">
            <span class="settings-list__icon"><TgIcon name="globe" :size="18" :stroke="2.2" /></span>
            {{ t('language') }}
          </span>
          <strong>{{ locale.toUpperCase() }}</strong>
        </div>
        <NuxtLink :to="pathFor('orders')" class="settings-list__row">
          <span class="settings-list__label">
            <span class="settings-list__icon"><TgIcon name="package" :size="18" :stroke="2.2" /></span>
            {{ t('order_history') }}
          </span>
          <TgIcon name="chevron-right" :size="18" :stroke="2.4" />
        </NuxtLink>
        <NuxtLink :to="pathFor('about')" class="settings-list__row">
          <span class="settings-list__label">
            <span class="settings-list__icon"><TgIcon name="info" :size="18" :stroke="2.2" /></span>
            {{ t('about') }}
          </span>
          <TgIcon name="chevron-right" :size="18" :stroke="2.4" />
        </NuxtLink>
      </div>
    </section>
  </TgLayout>
</template>

<style scoped>
.account-page,
.account-section,
.account-form,
.address-list {
  display: grid;
  gap: 14px;
}

.account-card,
.account-section {
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-lg);
  background: var(--color-white);
  padding: 16px;
  box-shadow: var(--shadow-card);
}

.account-card {
  display: grid;
  grid-template-columns: 64px minmax(0, 1fr);
  gap: 14px;
  align-items: center;
}

.account-card__avatar {
  width: 64px;
  height: 64px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  object-fit: cover;
}

.account-card__avatar--fallback {
  display: grid;
  background: var(--color-lime);
  color: var(--color-ink);
  place-items: center;
  font-family: var(--font-display);
  font-size: 24px;
}

.account-card h1,
.account-card p,
.account-section h2,
.account-section h3,
.account-muted {
  margin: 0;
}

.account-card h1 {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 18px;
}

.account-card p,
.account-muted,
.account-section__header small {
  color: var(--color-text-muted);
  font-size: 13px;
}

.account-section__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.account-section h2 {
  font-size: 16px;
}

.account-section h3 {
  font-size: 14px;
}

.account-form label {
  display: grid;
  gap: 6px;
  color: var(--color-ink);
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.account-form--address {
  border-top: 2px solid var(--color-ink);
  padding-top: 14px;
}

.address-card {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 12px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-md);
  background: var(--color-bg-input);
  padding: 12px;
  align-items: center;
  box-shadow: var(--shadow-card-sm);
}

.address-card strong,
.address-card span,
.address-card small {
  display: block;
}

.address-card strong {
  font-family: var(--font-display);
  font-size: 13px;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.address-card span,
.address-card small {
  margin-top: 3px;
  color: var(--color-text-muted);
  font-size: 12px;
  font-weight: 600;
}

.address-card__actions,
.account-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.address-card__actions {
  flex-direction: column;
  align-items: flex-end;
}

.account-link {
  border: 0;
  background: transparent;
  color: var(--color-primary-dark);
  padding: 0;
  font-family: var(--font-display);
  font-size: 11px;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  text-decoration: underline;
  text-decoration-thickness: 2px;
  text-underline-offset: 3px;
}

.account-link--danger {
  color: var(--color-danger);
}

.settings-list {
  overflow: hidden;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-lg);
  background: var(--color-white);
  box-shadow: var(--shadow-card-sm);
}

.settings-list__row {
  display: flex;
  min-height: 56px;
  border-bottom: 2px solid var(--color-ink);
  padding: 0 16px;
  align-items: center;
  justify-content: space-between;
  font-family: var(--font-display);
  font-size: 13px;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.settings-list__row:last-child {
  border-bottom: 0;
}

.settings-list__label {
  display: inline-flex;
  align-items: center;
  gap: 12px;
}

.settings-list__icon {
  display: grid;
  width: 32px;
  height: 32px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-lime);
  color: var(--color-ink);
  place-items: center;
}

.settings-list__row strong {
  font-family: var(--font-display);
  color: var(--color-accent-dark);
}
</style>
