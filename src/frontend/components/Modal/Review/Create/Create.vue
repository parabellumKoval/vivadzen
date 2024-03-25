<script setup>
const { t } = useI18n()

const review = ref({
  rating: 5,
  flaws: null,
  advantages: null,
  text: null
})

// COMPUTEDS
const product = computed(() => {
  console.log('MODAL PRODUCT', useModal().active.data)
  return useModal().active.data
})
// const regHandler = () => {
//   useModal().open(resolveComponent('ModalAuthRegister'), null, null, {width: {min: 420, max: 420}})
// }
</script>

<style src="./create.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('new_review')" class="modal-wrapper-auth">
    <div>
      <product-card-small :item="product"></product-card-small>

      <div class="form-wrapper">
        <div class="rate-wrapper">
          <div class="form-label">{{ t('set_rating') }}</div>
          <div class="rate-forms">
            <form-amount v-model="review.rating"></form-amount>
            <simple-stars :amount="review.rating" :size="20" mobile="medium"></simple-stars>
          </div>
        </div>

        <div>
          <div class="form-label">{{ t('w_review') }}</div>
          <form-textarea v-model="review.text" :placeholder="t('your_review')"></form-textarea>
        </div>

        <div>
          <div class="form-label">{{ t('w_advantages') }}</div>
          <form-textarea v-model="review.advantages" :placeholder="t('advantages')"></form-textarea>
        </div>

        <div>
          <div class="form-label">{{ t('w_flaws') }}</div>
          <form-textarea v-model="review.flaws" :placeholder="t('flaws')"></form-textarea>
        </div>

        <button class="button primary send-btn">{{ t('button.send') }}</button>
      </div>
    </div>
  </modal-wrapper>
</template>