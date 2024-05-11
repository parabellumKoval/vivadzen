<script setup>
import {useLikesStore} from '~~/store/likes';

const {t} = useI18n()
const props = defineProps({
  item: {
    type: Object
  }
})

const likes = ref(0)

// COMPUTED
const isMyLike = computed(() => {
  const likes = useLikesStore().getLikes || []

  if(likes.includes(props.item.id)){
    return true
  }else {
    return false
  }
})


// HANDLERS
const toggleLikeHandler = () => {
  if(isMyLike.value) {
    likes.value -= 1
  }else {
    likes.value += 1
  }

  useLikesStore().toggleLike(props.item.id)
}

// METHODS
const setLikes = () => {
  likes.value = props.item.likes
}

setLikes()
</script>

<style src="./full.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div class="full">
    <div class="header">
      <div class="name">{{ item.author.name }}</div>
      <simple-stars :amount="item.rating" mobile="medium" class="stars"></simple-stars>
      <div class="date">{{ $d(item.created_at, 'long') }}</div>
    </div>
    <div v-if="item.extras?.verified_purchase === '1'" class="approved">
      <IconCSS name="iconoir:user-cart" class="approved-icon"></IconCSS>
      <span class="approved-text">{{ t('verified_purchase') }}</span>
    </div>
    <div class="content">
      {{ item.text }}
    </div>
    <div v-if="item.extras?.advantages" class="review-adv">
      <div class="review-label">{{ t('dignity') }}</div>
      <div>{{ item.extras?.advantages }}</div>
    </div>
    <div v-if="item.extras?.flaws" class="review-adv">
      <div class="review-label">{{ t('flaws') }}</div>
      <div>{{ item.extras?.flaws }}</div>
    </div>
    <div class="buttons">

      <button
        @click="toggleLikeHandler"
        :class="{violet: likes > 0, secondary: likes <= 0, active: isMyLike}"
        class="button mini"
      >
        <IconCSS name="iconoir:thumbs-up" class="inline-icon"></IconCSS>
        <span v-if="likes">{{ likes }}</span>
      </button>

      <button class="button mini secondary lowcase">
        <IconCSS name="iconoir:reply" class="inline-icon"></IconCSS>
        <span>{{ t('button.reply') }}</span>
      </button>

    </div>
  </div>
</template>