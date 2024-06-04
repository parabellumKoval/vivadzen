<script setup>
import { useAuthStore } from '~~/store/auth';
const {t} = useI18n()

const props = defineProps({
  reviews: {
    type: Array
  },
  meta: {
    type: Object
  },
  product: {
    type: Object
  }
})

const emit = defineEmits(['update:current'])

const updateCurrentHandler = (v) => {
  emit('update:current', v)
}

const createReviewHandler = () => {
  if(useAuthStore().auth) {
    useModal().open(resolveComponent('ModalReviewCreate'), props.product, null, {width: {min: 420, max: 420}})
  }else{
    useNoty().setNoty({
      content: t('noty.review.need_login'),
      type: 'warning'
    }, 7000)
    
    useModal().open(resolveComponent('ModalAuthSocial'), null, null, {width: {min: 420, max: 420}})
  }
}
</script>

<style src="./reviews.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div class="reviews">
    <div class="review-form">
      <div class="review-form-title">{{ t('messages.leave_review') }}<br>{{ t('about_product') }}</div>
      <button @click="createReviewHandler" class="button violet wide large-icon inline-icon">
        <IconCSS name="iconoir:message-text" class="icon"></IconCSS>
        <span>{{ t('messages.leave_review') }}</span>
      </button>
    </div>
    
    <div class="review-grid">
      <review-card-full v-for="review in reviews" :key="review.id" :item="review" class="review-card"></review-card-full>
    </div>

    <simple-pagination :current="meta.current_page" :total="meta.last_page" @update:current="updateCurrentHandler"></simple-pagination>
  </div>
</template>