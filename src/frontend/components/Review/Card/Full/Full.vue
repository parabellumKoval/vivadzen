<script setup>
import {useAuthStore} from '~~/store/auth';
import {useLikesStore} from '~~/store/likes';

const {t} = useI18n()
const props = defineProps({
  item: {
    type: Object
  },
  parentName: {
    type: String
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
const replyHandler = () => {
  if(useAuthStore().auth) {
    useModal().open(resolveComponent('ModalReviewReply'), props.item, null, {width: {min: 420, max: 420}})
  }else{
    useNoty().setNoty({
      content: t('noty.review.need_login'),
      type: 'warning'
    }, 7000)
    
    useModal().open(resolveComponent('ModalAuthSocial'), null, null, {width: {min: 420, max: 420}})
  }
}

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
  <div>
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
        <span v-if="parentName" class="parent">
          <IconCSS name="iconoir:reply" size="14" class="parent-icon"></IconCSS>
          <span class="parent-name">{{ parentName }}</span>
        </span> 
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

        <button @click="replyHandler" class="button mini secondary lowcase">
          <IconCSS name="iconoir:reply" class="inline-icon"></IconCSS>
          <span>{{ t('button.reply') }}</span>
        </button>

      </div>

      <div class="sub-items-wrapper" v-if="item.children?.length">
        <review-card-full
          v-for="ch in item.children"
          :key="ch.id"
          :item="ch"
          :parent-name="item.author.name"
          class="deep-child"
        ></review-card-full>
      </div>
    </div>
  </div>
</template>