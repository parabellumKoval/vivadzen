<script setup>
const {t} = useI18n()
import {useInstaStore} from '~/store/insta'

const posts = ref([])

// METHODS
await useLazyAsyncData('insta-posts', () => useInstaStore().posts({per_page: 6})).then(({data, error}) => {
  if(data.value){
    posts.value = data.value
  }
})
</script>

<style src="./insta.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section class="main-section insta-section">
    <div class="box">
      <div class="box-title">{{ t('subto') }} instagram djini.shop</div>
      <a href="/" class="button primary box-btn">
        <IconCSS name="basil:instagram-outline" size="20" class="icon"></IconCSS>
        <span class="name">{{ t('goto') }} instagram</span>
      </a>
    </div>
    <div class="feed">
      <a
        v-for="post in posts"
        :href="post.permalink"
        :key="post.permalink"
        target="_blank"
        class="feed-item"
      >
        <nuxt-img
          :src = "post.media_url"
          width="220"
          height="220"
          sizes = "mobile:100vw tablet:220px desktop:220px"
          format = "webp"
          quality = "60"
          loading = "lazy"
          placeholder="./images/noimage.png"
          fit="outside"
          class="image"
        />
      </a>
    </div>
  </section>
</template>