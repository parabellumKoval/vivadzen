<script setup lang="ts">
definePageMeta({ layout: 'auth' })

const email = ref('admin@vivadzen.cz')
const password = ref('admin12345')
const loading = ref(false)
const error = ref('')
const api = useApi()
const auth = useAuthStore()
const { t } = useAdminI18n()

async function submit() {
  error.value = ''
  loading.value = true
  try {
    const res = await api<{ token: string; user: any }>('/login', {
      method: 'POST',
      body: { email: email.value, password: password.value },
    })
    auth.setSession(res.token, res.user)
    navigateTo('/')
  } catch (e: any) {
    error.value = e?.data?.message || t('auth.login_error')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <div class="font-display text-4xl text-cream-50">Vivadzen</div>
      <div class="text-xs uppercase tracking-widest text-cream-50/70 mt-1">{{ t('auth.title') }}</div>
    </div>
    <form class="bg-white rounded-2xl shadow-2xl p-8 space-y-5" @submit.prevent="submit">
      <div>
        <label class="field-label">{{ t('auth.email') }}</label>
        <input v-model="email" type="email" required class="field-input" autocomplete="email" />
      </div>
      <div>
        <label class="field-label">{{ t('auth.password') }}</label>
        <input v-model="password" type="password" required class="field-input" autocomplete="current-password" />
      </div>
      <button type="submit" class="btn-primary w-full justify-center" :disabled="loading">
        {{ loading ? t('auth.logging_in') : t('auth.login') }}
      </button>
      <p v-if="error" class="text-sm text-terracotta-700 text-center">{{ error }}</p>
    </form>
  </div>
</template>
