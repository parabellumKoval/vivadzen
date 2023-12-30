<script setup>
const insta = computed(() => {
  return [
    {
      id: 1,
      image: '/images/insta/img-1.png'
    },{
      id: 2,
      image: '/images/insta/img-2.png'
    },{
      id: 3,
      image: '/images/insta/img-3.png'
    },{
      id: 4,
      image: '/images/insta/img-4.png'
    },{
      id: 5,
      image: '/images/insta/img-5.png'
    }
  ]
})
</script>

<style src="./insta.scss" lang="scss" scoped></style>

<template>
  <section class="main-section insta-section">
    <div class="box">
      <div class="box-title">Подпишитесь на instagram djini.shop</div>
      <a href="/" class="button primary box-btn">
        <IconCSS name="basil:instagram-outline" size="20" class="icon"></IconCSS>
        <span class="name">Перейти в instagram</span>
      </a>
    </div>
    <div class="feed">
      <a v-for="item in insta" :key="item.id" href="/" target="_blank" class="feed-item">
        <nuxt-img
          :src = "item.image"
          width="220"
          height="220"
          sizes = "mobile:100vw tablet:220px desktop:220px"
          format = "webp"
          quality = "60"
          loading = "lazy"
          fit="outside"
          class="image"
        />
      </a>
    </div>
  </section>
</template>