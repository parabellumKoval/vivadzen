<script setup lang="ts">
import { useFeedbackStore } from '~~/store/feedback'

const { t } = useI18n()
const runtimeConfig = useRuntimeConfig()

const email = ref('')
const isSubmitting = ref(false)
const isSuccess = ref(false)
const successMessage = ref('')

const feedbackType = computed(() =>
  runtimeConfig.public?.landingPromoSubscribe?.feedbackType || 'landing_kratom_local_sale'
)

const handleSubmit = async () => {
  if (!email.value || isSubmitting.value) return

  isSubmitting.value = true

  try {
    const payload = {
      type: feedbackType.value,
      email: email.value
    }

    const { data, error } = await useFeedbackStore().create(payload)

    if (error?.value) {
      throw error.value
    }

    if (data?.value) {
      const promoStatus = data.value?.extras?.promo_subscribe?.status
      successMessage.value = promoStatus === 'existing_customer'
        ? t('success_existing_customer')
        : t('success')

      isSuccess.value = true
      email.value = ''
    }
  } catch (error) {
    useNoty().setNoty({
      title: t('noty.subscription.fail'),
      content: t('error.check_fields'),
      type: 'error'
    }, 5000)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style src="./promo-subscribe.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section class="promo-subscribe">
    <div class="container">
      <div class="promo-subscribe__content">
        <div class="promo-subscribe__badge">
          {{ t('badge') }}
        </div>
        <div class="promo-subscribe__image-wrapper">
          <nuxt-img
            src="/images/landing/box2.png"
            :alt="t('image_alt')"
            loading="lazy"
            class="promo-subscribe__img"
          />
        </div>
        <div class="promo-subscribe__text-wrapper">
          <div class="promo-subscribe__text">
            <h3 class="promo-subscribe__title">
              {{ t('title') }}
            </h3>
            <p class="promo-subscribe__description">
              {{ t('description') }}
              <simple-info-hint :text="t('discount_hint')" />
            </p>
          </div>

          <form 
            v-if="!isSuccess"
            class="promo-subscribe__form" 
            @submit.prevent="handleSubmit"
          >
            <input
              v-model="email"
              type="email"
              class="promo-subscribe__input"
              :placeholder="t('placeholder')"
              required
            />
            <button 
              type="submit" 
              class="promo-subscribe__button"
              :disabled="isSubmitting"
            >
              <span v-if="!isSubmitting">{{ t('button') }}</span>
              <span v-else class="promo-subscribe__loader"></span>
            </button>
          </form>
          <div v-else class="promo-subscribe__success">
            <IconCSS name="ph:check-circle-fill" />
            <span>{{ successMessage || t('success') }}</span>
          </div>
        </div>

      </div>
    </div>
  </section>
</template>
