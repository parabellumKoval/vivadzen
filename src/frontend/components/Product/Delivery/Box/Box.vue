<script setup>
const {t} = useI18n()

const methods = computed(() => {
  return [
    {
      id: 1,
      name: t('delivery_one.title'),
      desc: t('delivery_one.desc'),
      image: '/images/logo/np-mini.png'
    },{
      id: 2,
      name: t('delivery_two.title'),
      desc: t('delivery_two.desc'),
      image: '/images/logo/np-mini.png'
    },{
      id: 3,
      name: t('delivery_three.title'),
      desc: t('delivery_three.desc'),
      image: '/images/logo/np-mini.png'
    }
  ]
})
</script>

<style src="./../../box.scss" lang="scss" scoped></style>
<style src="./box.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div class="product-box">
    <div class="product-box-label">{{ t('delivery') }}:</div>
    <div class="method">
      <div v-for="method in methods" :key="method.id" class="method-item">
        <nuxt-img
          v-if="method.image"
          :src = "method.image"
          width="20"
          height="20"
          sizes = "mobile:30px tablet:30px desktop:30px"
          format = "webp"
          quality = "60"
          loading = "lazy"
          fit="outside"
          class="method-image"
        />

        <div class="method-name">{{ method.name }}</div>
        <div class="method-desc">{{ method.desc }}</div>
      </div>
    </div>
    <div class="free-delivery">
      <IconCSS name="solar:gift-bold-duotone" class="free-delivery-icon"></IconCSS>
      <span class="free-delivery-name">
        {{ t('free') }}
        <span class="free-delivery-desc">{{ t('order') }} <span class="bold">{{ t('grn') }}</span></span>
      </span>
    </div>
  </div>
</template>