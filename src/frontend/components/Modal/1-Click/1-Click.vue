<script setup>
import { useFeedbackStore } from '~~/store/feedback';
const { t } = useI18n()

const form = ref({
  type: '1_click_buy',
  phone: null,
  text: null
})

const errors = ref({
  phone: null,
  text: null
})

// COMPUTEDS
const product = computed(() => {
  return useModal().active.data
})

// METHODS
const resetForm = () => {
  form.value.phone = null
  form.value.text = null
}

const resetErrors = () => {
  errors.value.phone = null
  errors.value.text = null
}

const getFormData = () => {
  let formData = {...form.value}
  formData.text = 'Товар: ' + product.value.name + '. Комментарий: ' + form.value.text

  return formData
}

// HANDLERS
const buyHandler = () => {
  let formData = getFormData()

  useFeedbackStore().create(formData).then(({data, error}) => {

    if(data) {
      useNoty().setNoty({
        title: t('noty.1_click.title'),
        content: t('noty.1_click.sent'),
        type: 'success'
      }, 5000)

      resetForm()
      resetErrors()
      useModal().close()
    }

    if(error) {
      throw error
    }

  }).catch((e) => {

    useNoty().setNoty({
      title: t('noty.1_click.fail'),
      content: t('error.check_fields'),
      type: 'error'
    }, 5000)

    errors.value = e
  })

}
</script>

<style src="./1-click.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('button.1_click_buy')" class="modal-wrapper-auth">
    <div>
      <product-card-small :item="product"></product-card-small>

      <div class="form-wrapper">
        <div>
          {{ t('w') }}
        </div>
        
        <div>
          <div class="form-label">{{ t('form.phone') }}</div>
          <form-text
            v-model="form.phone"
            :error="errors?.phone"
            @input="() => errors.phone = null"
            :placeholder="t('form.enter.phone')"
          ></form-text>
        </div>

        <div>
          <div class="form-label">{{ t('form.comment') }}</div>
          <form-textarea
            v-model="form.text"
            :error="errors?.text"
            @input="() => errors.text = null"
            :placeholder="t('form.enter.comment')"
          ></form-textarea>
        </div>

        <button @click="buyHandler" class="button primary send-btn">{{ t('button.send') }}</button>
      </div>
    </div>
  </modal-wrapper>
</template>