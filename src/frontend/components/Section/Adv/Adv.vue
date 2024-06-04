<script setup>
const {t} = useI18n()

const advantages = computed(() => {
  return [
    {
      id: 1,
      icon: 'solar:ticket-sale-bold-duotone',
      title: t('adv1.title'),
      desc: '<a href="/">' + t('sub') + '</a> ' + t('adv1.desc')
    },
    {
      id: 2,
      icon: 'solar:hand-money-bold-duotone',
      title: t('adv2.title'),
      desc: '<a href="/">' + t('reg') + '</a> ' + t('adv2.desc')
    },
    {
      id: 3,
      icon: 'solar:medal-ribbons-star-bold-duotone',
      title: t('adv3.title'),
      desc: t('adv3.desc')
    },
    {
      id: 4,
      icon: 'solar:chat-square-like-bold-duotone',
      title: t('adv4.title'),
      desc: t('adv4.desc') + ' - <a href="/">' + t('button.leave_review') + '</a>'
    }
  ]
})
</script>

<style src="./adv.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section class="main-section adv-section">
    <div class="section-title">{{ t('title') }}</div>
    <div class="desc">{{ t('desc') }} <span class="selected">djini.com.ua</span> 😍</div>

    <div class="adv-wrapper">
      <div v-for="adv in advantages" :key="adv.id" class="adv-item">
        <IconCSS :name="adv.icon" size="50" class="adv-icon"></IconCSS>
        <div class="adv-title">{{ adv.title }}</div>
        <div class="adv-desc" v-html="adv.desc"></div>
      </div>
    </div>

    <NuxtLink :to="localePath('/vitaminy_i_mineraly')" class="button primary adv-btn">{{ t('button') }}</NuxtLink>

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