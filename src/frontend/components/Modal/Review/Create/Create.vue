<script setup>
import { useReviewStore } from '~~/store/review';
import { useAuthStore } from '~~/store/auth';

const { t } = useI18n()

const review = ref({
  provider: 'data',
  owner: {
    name: null,
    email: null,
    photo: null
  },
  rating: 5,
  flaws: null,
  advantages: null,
  text: null,
  reviewable_id: null,
  reviewable_type: null
})

const errors = ref(null)

// COMPUTEDS
const product = computed(() => {
  return useModal().active.data
})

// HANDLER
const resetReview = () => {
  review.value.text = null
  review.value.advantages = null
  review.value.flaws = null
  review.value.reviewable_id = null
  review.value.reviewable_type = null
}

const sendHandler = async () => {
  let data = {...review.value}
  await useReviewStore().create(data).then(({data, error}) => {

    if(data) {
      resetReview()

      useNoty().setNoty({
        title: t('noty.review.success_title'),
        content: t('noty.review.success'),
        type: 'success'
      }, 5000)

      useModal().close()
    }
    
    if(error) 
      throw error

  }).catch((e) => {
    useNoty().setNoty({
      title: t('noty.review.fail_title'),
      content: t('noty.review.fail'),
      type: 'error'
    }, 7000)

    if(e.options) {
      errors.value = e.options
    }
  })
}

// METHODS
const setProductData = () => {
  review.value.reviewable_id = product.value?.id || null
  review.value.reviewable_type = product.value?.id? String.raw`Backpack\Store\app\Models\Product`: null
}

const setUserData = () => {
  review.value.owner.name = useAuthStore().name
  review.value.owner.email = useAuthStore().user.email || null
  review.value.owner.photo = useAuthStore().user.photo || null
}

setProductData()
setUserData()
</script>

<style src="./create.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('new_review')">
    <div class="modal-wrapper">
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
          <form-textarea
            v-model="review.text"
            :error = "errors?.text"
            @input = "() => errors.text = null"
            :placeholder="t('your_review')"
          ></form-textarea>
        </div>

        <div>
          <div class="form-label">{{ t('w_advantages') }}</div>
          <form-textarea
            v-model="review.advantages"
            :error = "errors?.advantages"
            @input = "() => errors.advantages = null"
            :placeholder="t('advantages')"
          ></form-textarea>
        </div>

        <div>
          <div class="form-label">{{ t('w_flaws') }}</div>
          <form-textarea
            v-model="review.flaws"
            :error = "errors?.flaws"
            @input = "() => errors.flaws = null"
            :placeholder="t('flaws')"
          ></form-textarea>
        </div>

        <button @click="sendHandler" class="button primary send-btn">{{ t('button.send') }}</button>
      </div>
    </div>
  </modal-wrapper>
</template>