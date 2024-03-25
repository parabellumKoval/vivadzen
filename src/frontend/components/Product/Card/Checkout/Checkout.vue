<script setup>
import {useCartStore} from '~/store/cart'
const { t } = useI18n()

const props = defineProps({
  item: {
    type: Object
  }
})

// const amount = ref(1)

const photo = computed(() => {
  if(props.item.image?.src) {
    return props.item.image.src
    // return '/server/' + props.item.image.src
  } else {
    return null
  }
})

const deleteHandler = () => {
  useCartStore().remove(props.item.id)
  useNoty().setNoty({
    content: t('noty.product_delete_cart', {product: props.item.name})
  }, 1000)
}
</script>

<style src="./checkout.scss" lang="scss" scoped></style>

<template>
<div class="wrapper">
  <NuxtLink :to="localePath('/' + item.slug)" :aria-label="item.name" clickable class="image-wrapper">
    <nuxt-img
      v-if="photo"
      :src = "photo"
      :alt = "item.image.alt || item.name"
      :title = "item.image.title || item.name"
      :class="item.image.size"
      width="85"
      height="110"
      sizes = "mobile:50px tablet:165px desktop:165px"
      format = "webp"
      quality = "60"
      loading = "lazy"
      fit="outside"
      class="image"
    >
    </nuxt-img> 
  </NuxtLink>
  <div class="body">
    <span class="code label">{{ item.code }}</span>
    <NuxtLink
      :to="localePath('/' + item.slug)"
      :aria-label="item.name"
      clickable
      class="name"
    >{{ item.name }}</NuxtLink>
    <button @click="deleteHandler" class="remove-btn">
      <IconCSS name="iconoir:trash" class="remove-btn-icon"></IconCSS>
      <span class="remove-btn-text">{{ t('label.delete') }}</span>
    </button>
  </div>
  <div class="footer">
    <form-amount v-model="item.amount"></form-amount>
    <div class="price">
      <simple-price v-if="+item.oldPrice" :value="+item.oldPrice" class="price-old"></simple-price>
      <simple-price v-if="+item.price" :value="+item.price" class="price-base"></simple-price>
    </div>
  </div>
</div>
</template>