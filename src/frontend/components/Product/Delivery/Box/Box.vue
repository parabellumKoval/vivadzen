<script setup>
const {t} = useI18n()

const methods = computed(() => {
  return [
    {
      id: 1,
      name: 'У відділення / поштомат "Нова пошта"',
      desc: 'За тарифами перевізника',
      image: '/images/logo/np-mini.png'
    },{
      id: 2,
      name: "Кур'єр НП за вашою адресою",
      desc: 'За тарифами перевізника',
      image: '/images/logo/np-mini.png'
    },{
      id: 3,
      name: 'Самовывоз',
      desc: 'по адресу',
      image: '/images/logo/np-mini.png'
    }
  ]
})
</script>

<style src="./../../box.scss" lang="scss" scoped></style>
<style src="./box.scss" lang="scss" scoped></style>

<template>
  <div class="product-box">
    <div class="product-box-label">Доставка в Харьков:</div>
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
        Бесплатная доставка
        <span class="free-delivery-desc">при заказе <span class="bold">от 1000 грн.</span></span>
      </span>
    </div>
  </div>
</template>