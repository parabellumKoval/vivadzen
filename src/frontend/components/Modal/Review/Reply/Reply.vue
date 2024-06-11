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
  link: null,
  rating: null,
  flaws: null,
  advantages: null,
  text: null,
  reviewable_id: null,
  reviewable_type: null,
  parent_id: null
})

const errors = ref(null)

// COMPUTEDS
const parent = computed(() => {
  return useModal().active.data
})

// HANDLER
const resetReview = () => {
  review.value.text = null
  review.value.parent_id = null
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
const clearError = (key) => {
  if(errors.value && errors.value[key]) {
    errors.value[key]= null
  }
}

const setParent = () => {
  review.value.parent_id = useModal().active?.data?.id || null
}

const setUserData = () => {
  review.value.owner.name = useAuthStore().name
  review.value.owner.email = useAuthStore().user.email || null
  review.value.owner.photo = useAuthStore().user.photo || null
}

// WATCH
setParent()
setUserData()
</script>

<style src="./reply.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('new_reply')">
    <div class="modal-wrapper">

      <div class="parent">
        <div class="parent-name">{{ parent.author.name }}</div>
        <div class="parent-text">{{ parent.text }}</div>
      </div>

      <div class="form-wrapper">

        <div>
          <div class="form-label">{{ t('form.reply') }}</div>
          <form-textarea
            v-model="review.text"
            :error = "errors?.text"
            @input = "clearError('text')"
            :placeholder="t('form.enter.reply')"
          ></form-textarea>
        </div>

        <button @click="sendHandler" class="button primary send-btn">{{ t('button.reply') }}</button>
      </div>

    </div>
  </modal-wrapper>
</template>