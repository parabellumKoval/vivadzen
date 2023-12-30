<script setup>
const advantages = computed(() => {
  return [
    {
      id: 1,
      icon: 'solar:ticket-sale-bold-duotone',
      title: 'Купоны и промокоды',
      desc: '<a href="/">Подпишитесь</a> на обновления и получайте еженедельные скидки на разные категории товаров'
    },
    {
      id: 2,
      icon: 'solar:hand-money-bold-duotone',
      title: 'Накопительная система',
      desc: '<a href="/">Зарегистрируйте</a> аккаунт в нашем магазине и накапливайте бонусы и скидки '
    },
    {
      id: 3,
      icon: 'solar:medal-ribbons-star-bold-duotone',
      title: 'Оригинальная продукция',
      desc: 'Продукция сертифицирована и только оригинальные брендовые товары'
    },
    {
      id: 4,
      icon: 'solar:chat-square-like-bold-duotone',
      title: '500+ реальных отзывов',
      desc: 'В нашем магазине только настоящие отзывы. Убидитесь в этом - <a href="/">оставить отзыв</a>'
    }
  ]
})
</script>

<style src="./adv.scss" lang="scss" scoped></style>

<template>
  <section class="main-section adv-section">
    <div class="section-title">Здоровье на первом месте</div>
    <div class="desc">Получите бесценные преимущества от заботы за своим здоровьем вместе с магазином <span class="selected">djini.com.ua</span> 😍</div>

    <div class="adv-wrapper">
      <div v-for="adv in advantages" :key="adv.id" class="adv-item">
        <IconCSS :name="adv.icon" size="50" class="adv-icon"></IconCSS>
        <div class="adv-title">{{ adv.title }}</div>
        <div class="adv-desc" v-html="adv.desc"></div>
      </div>
    </div>

    <NuxtLink :to="useLocalePath('/')" class="button primary adv-btn">Товары для здоровья</NuxtLink>

    <nuxt-img
      src = "/images/apple.png"
      width="155"
      height="155"
      sizes = "mobile:100vw tablet:200px desktop:200px"
      format = "webp"
      quality = "80"
      loading = "lazy"
      fit="outside"
      class="image-adv image-apple"
    />

    <nuxt-img
      src = "/images/pineapple.png"
      width="319"
      height="204"
      sizes = "mobile:100vw tablet:340px desktop:340px"
      format = "webp"
      quality = "80"
      loading = "lazy"
      fit="outside"
      class="image-adv image-pineapple"
    />

    <nuxt-img
      src = "/images/banana.png"
      width="381"
      height="342"
      sizes = "mobile:100vw tablet:400px desktop:400px"
      format = "webp"
      quality = "80"
      loading = "lazy"
      fit="outside"
      class="image-adv image-banana"
    />

    <nuxt-img
      src = "/images/carrot.png"
      width="262"
      height="222"
      sizes = "mobile:100vw tablet:300px desktop:300px"
      format = "webp"
      quality = "80"
      loading = "lazy"
      fit="outside"
      class="image-adv image-carrot"
    />
  </section>
</template>