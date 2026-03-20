<script setup lang="ts">
import { useFeedbackStore } from '~~/store/feedback'

const { t } = useI18n()
const modal = useModal()

const form = ref({
  email: null as string | null,
  phone: null as string | null
})

const requestType = 'landing_sample_set'
const isSubmitting = ref(false)

const requestNote = computed(() => t('modal.request_note'))

const initialErrorsState = () => ({
  email: null as string | string[] | null,
  phone: null as string | string[] | null
})

const errors = ref(initialErrorsState())

const normalizeFieldErrors = (value: unknown) => {
  if (Array.isArray(value)) {
    return value.length ? value : null
  }

  if (typeof value === 'string' && value.length) {
    return value
  }

  return null
}

const extractValidationBag = (error: unknown) => {
  if (!error || typeof error !== 'object') return null
  if ('errors' in error && typeof (error as any).errors === 'object') return (error as any).errors
  if ('options' in error && typeof (error as any).options === 'object') return (error as any).options
  return error
}

const applyErrors = (error: unknown) => {
  const bag = extractValidationBag(error)
  const next = initialErrorsState()

  if (bag) {
    next.email = normalizeFieldErrors((bag as any).email)
    next.phone = normalizeFieldErrors((bag as any).phone)
  }

  errors.value = next
}

const resetForm = () => {
  form.value.email = null
  form.value.phone = null
}

const resetErrors = () => {
  errors.value = initialErrorsState()
}

const sanitize = (value: string | null) => {
  const trimmed = (value ?? '').trim()
  return trimmed.length ? trimmed : null
}

const validate = () => {
  form.value.email = sanitize(form.value.email)
  form.value.phone = sanitize(form.value.phone)

  if (form.value.email || form.value.phone) {
    return true
  }

  const message = t('error.required')
  errors.value.email = message
  errors.value.phone = message

  return false
}

const buildPayload = () => {
  const payload = {
    type: requestType,
    text: requestNote.value
  } as Record<string, string | null>

  if (form.value.email) {
    payload.email = form.value.email
  }

  if (form.value.phone) {
    payload.phone = form.value.phone
  }

  return payload
}

const submitHandler = () => {
  if (isSubmitting.value) return
  resetErrors()
  if (!validate()) return

  isSubmitting.value = true

  useFeedbackStore().create(buildPayload()).then(({ data, error }) => {
    if (data?.value) {
      resetForm()
      resetErrors()
      modal.close()

      useInfoModal().open({
        title: t('modal.success_title'),
        message: t('modal.success_message'),
        type: 'success',
        buttonText: t('modal.success_button')
      })
    }

    if (error?.value) {
      throw error.value
    }
  }).catch((e) => {
    useNoty().setNoty({
      title: t('noty.feedback.fail'),
      content: t('error.check_fields'),
      type: 'error'
    }, 5000)

    applyErrors((e as any)?.data || e)
  }).finally(() => {
    isSubmitting.value = false
  })
}
</script>

<style src="./sample-set-modal.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('modal.title')" class="sample-set-modal">
    <div class="sample-set-modal__content">
      <p class="sample-set-modal__desc">{{ t('modal.desc') }}</p>

      <div class="sample-set-modal__form">
        <div>
          <div class="form-label">{{ t('modal.form.email') }}</div>
          <form-text
            v-model="form.email"
            :error="errors?.email"
            @input="() => errors.email = null"
            :placeholder="t('modal.form.email_placeholder')"
          ></form-text>
        </div>

        <div>
          <div class="form-label">{{ t('modal.form.phone') }}</div>
          <form-text
            v-model="form.phone"
            :error="errors?.phone"
            @input="() => errors.phone = null"
            :placeholder="t('modal.form.phone_placeholder')"
          ></form-text>
        </div>
      </div>

      <div class="sample-set-modal__actions">
        <button
          class="button primary sample-set-modal__submit"
          type="button"
          :disabled="isSubmitting"
          @click="submitHandler"
        >
          {{ t('modal.form.submit') }}
        </button>
      </div>
    </div>
  </modal-wrapper>
</template>
