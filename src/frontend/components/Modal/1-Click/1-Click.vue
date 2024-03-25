<script setup>
const { t } = useI18n()

const form = ref({
  phone: null,
  text: null
})

// COMPUTEDS
const product = computed(() => {
  return useModal().active.data
})
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
          <div class="form-label">{{ t('w_phone') }}</div>
          <form-text v-model="form.phone" :placeholder="t('phone')"></form-text>
        </div>

        <div>
          <div class="form-label">{{ t('w_comment') }}</div>
          <form-textarea v-model="form.text" :placeholder="t('comment')"></form-textarea>
        </div>

        <button class="button primary send-btn">{{ t('button.send') }}</button>
      </div>
    </div>
  </modal-wrapper>
</template>