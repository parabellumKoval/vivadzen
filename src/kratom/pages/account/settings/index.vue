<script setup lang="ts">
import type { SavedDeliveryAddress } from '~/composables/useSavedDeliveryAddresses'

const { t } = useI18n()
const { user: authUser, updateProfile, requestEmailChange, changePassword, init } = useAuth()
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
} = useSavedDeliveryAddresses()
const { methods } = useDelivery()

await init()

definePageMeta({
  crumb: {
    name: 'title.account.settings',
    item: '/account/settings',
  },
  tab: 'settings',
})

const userForm = ref({
  first_name: '',
  last_name: '',
  phone: '',
  email: '',
})

const savedAddresses = ref<SavedDeliveryAddress[]>([])

const emailForm = ref({
  email: '',
  password: '',
})

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const isSavingProfile = ref(false)
const isSavingAddresses = ref(false)
const isChangingEmail = ref(false)
const isChangingPassword = ref(false)

const hydrateFromAuth = (value: Record<string, any> | null) => {
  userForm.value = {
    first_name: value?.first_name || '',
    last_name: value?.last_name || '',
    phone: value?.phone || '',
    email: value?.email || '',
  }

  savedAddresses.value = Array.isArray(value?.saved_delivery_addresses)
    ? value.saved_delivery_addresses.map((item: SavedDeliveryAddress) => normalizeAddress(item))
    : []
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

const sanitizeAddressesForSave = () => {
  return savedAddresses.value
    .map((item) => normalizeAddress(item))
    .filter((item) => item.method && (item.warehouse || item.street || item.settlement || item.zip))
    .map((item) => ({
      ...item,
      country: item.country || String(region.value || '').toUpperCase(),
    }))
}

const saveProfile = async () => {
  isSavingProfile.value = true
  try {
    await updateProfile({
      first_name: userForm.value.first_name,
      last_name: userForm.value.last_name,
      phone: userForm.value.phone,
    })

    noty.setNoty({ content: t('noty.update.success'), type: 'success' })
  } catch (error) {
    noty.setNoty({ content: t('noty.update.fail'), type: 'error' }, 7000)
  } finally {
    isSavingProfile.value = false
  }
}

const saveAddresses = async () => {
  isSavingAddresses.value = true
  try {
    await updateProfile({
      saved_delivery_addresses: sanitizeAddressesForSave(),
    })

    noty.setNoty({ content: t('noty.update.success'), type: 'success' })
  } catch (error) {
    noty.setNoty({ content: t('noty.update.fail'), type: 'error' }, 7000)
  } finally {
    isSavingAddresses.value = false
  }
}

const submitEmailChange = async () => {
  isChangingEmail.value = true
  try {
    await requestEmailChange({
      email: emailForm.value.email,
      password: emailForm.value.password,
    })

    emailForm.value.password = ''
    noty.setNoty({
      content: t('noty.auth.email.sent', { email: emailForm.value.email }),
      type: 'success',
    }, 7000)
  } catch (error: any) {
    noty.setNoty({
      content: String(error?.data?.message || t('noty.update.fail')),
      type: 'error',
    }, 7000)
  } finally {
    isChangingEmail.value = false
  }
}

const submitPasswordChange = async () => {
  isChangingPassword.value = true
  try {
    await changePassword({
      current_password: passwordForm.value.current_password,
      password: passwordForm.value.password,
      password_confirmation: passwordForm.value.password_confirmation,
    })

    passwordForm.value = {
      current_password: '',
      password: '',
      password_confirmation: '',
    }

    noty.setNoty({
      content: t('noty.auth.password.changed.success'),
      type: 'success',
    }, 7000)
  } catch (error: any) {
    noty.setNoty({
      content: String(error?.data?.message || t('noty.update.fail')),
      type: 'error',
    }, 7000)
  } finally {
    isChangingPassword.value = false
  }
}
</script>

<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div class="account-settings">
    <section class="account-settings__section">
      <div class="account-settings__section-header">
        <div>
          <div class="account-settings__eyebrow">{{ t('personal_data') }}</div>
          <h2 class="account-settings__title">{{ t('title.account.settings') }}</h2>
        </div>
      </div>

      <div class="account-settings__grid">
        <form-text v-model="userForm.first_name" :placeholder="t('form.firstname')" />
        <form-text v-model="userForm.last_name" :placeholder="t('form.lastname')" />
        <form-phone-region v-model="userForm.phone" :placeholder="t('form.phone')" />
        <form-text v-model="userForm.email" :placeholder="t('form.email')" readonly />
      </div>

      <button type="button" class="button primary" :class="{ loading: isSavingProfile }" @click="saveProfile">
        {{ t('button.save') }}
      </button>
    </section>

    <section class="account-settings__section">
      <div class="account-settings__section-header">
        <div>
          <div class="account-settings__eyebrow">{{ t('saved_addresses') }}</div>
          <h3 class="account-settings__subtitle">{{ t('delivery_data') }}</h3>
        </div>

        <button type="button" class="button secondary" @click="addAddress">{{ t('add_address') }}</button>
      </div>

      <div v-if="savedAddresses.length" class="account-settings__addresses">
        <article v-for="(address, index) in savedAddresses" :key="address.id || `address-${index}`" class="address-card">
          <header class="address-card__header">
            <div>
              <div class="address-card__title">{{ address.title || buildAddressSummary(address) }}</div>
              <div class="address-card__summary">{{ buildAddressSummary(address) }}</div>
            </div>
            <button type="button" class="button text-link" @click="removeAddress(index)">{{ t('button.delete') }}</button>
          </header>

          <div class="account-settings__grid">
            <label class="account-settings__select-wrapper">
              <span>{{ t('delivery_method') }}</span>
              <select v-model="address.method" class="account-settings__select">
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

      <div v-else class="account-settings__empty">{{ t('no_saved_addresses') }}</div>

      <button type="button" class="button primary" :class="{ loading: isSavingAddresses }" @click="saveAddresses">
        {{ t('button.save') }}
      </button>
    </section>

    <section class="account-settings__section">
      <div class="account-settings__section-header">
        <div>
          <div class="account-settings__eyebrow">{{ t('security') }}</div>
          <h3 class="account-settings__subtitle">{{ t('security') }}</h3>
        </div>
      </div>

      <div class="account-settings__security">
        <div class="account-settings__security-card">
          <div class="account-settings__security-title">{{ t('change_email') }}</div>
          <div class="account-settings__grid">
            <form-text v-model="emailForm.email" :placeholder="t('form.email')" />
            <form-text v-model="emailForm.password" :placeholder="t('form.password')" type="password" />
          </div>
          <button type="button" class="button secondary" :class="{ loading: isChangingEmail }" @click="submitEmailChange">
            {{ t('button.send') }}
          </button>
        </div>

        <div class="account-settings__security-card">
          <div class="account-settings__security-title">{{ t('change_pass') }}</div>
          <div class="account-settings__grid">
            <form-text v-model="passwordForm.current_password" :placeholder="t('current_password')" type="password" />
            <form-text v-model="passwordForm.password" :placeholder="t('form.password')" type="password" />
            <form-text v-model="passwordForm.password_confirmation" :placeholder="t('form.password_confirmation')" type="password" />
          </div>
          <button type="button" class="button secondary" :class="{ loading: isChangingPassword }" @click="submitPasswordChange">
            {{ t('button.save') }}
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped lang="scss">
.account-settings {
  display: grid;
  gap: 18px;
}

.account-settings__section {
  display: grid;
  gap: 18px;
}

.account-settings__section-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.account-settings__eyebrow {
  margin-bottom: 8px;
  color: #8a5a2b;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.account-settings__title,
.account-settings__subtitle {
  color: #1f2b1d;
  font-size: 28px;
  line-height: 1.05;
}

.account-settings__subtitle {
  font-size: 24px;
}

.account-settings__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 14px;

  @include desktop {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.account-settings__addresses,
.account-settings__security {
  display: grid;
  gap: 16px;
}

.account-settings__security-card,
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
  justify-content: space-between;
  gap: 16px;
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

.account-settings__empty {
  padding: 28px;
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.68);
  color: #667160;
  text-align: center;
}

.account-settings__security-title {
  color: #1f2b1d;
  font-size: 18px;
  font-weight: 700;
}

.account-settings__select-wrapper {
  display: grid;
  gap: 8px;
  color: #667160;
  font-size: 14px;
}

.account-settings__select {
  min-height: 56px;
  padding: 0 16px;
  border-radius: 18px;
  border: 1px solid rgba(74, 91, 68, 0.16);
  background: #fffdf9;
  color: #1f2b1d;
}
</style>
