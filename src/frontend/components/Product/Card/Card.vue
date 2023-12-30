<script setup lang="ts">
import {useCartStore} from '~/store/cart'
const { t } = useI18n()

const props = defineProps({
  item: {
    type: Object as PropType<Product>,
    required: true
  }
})

const photo = computed(() => {
  if(props.item.image?.src) {
    return props.item.image.src
    // return '/server/' + props.item.image.src
  } else {
    return null
  }
})

// METHODS
const toCartHandler = () => {
  const product = {
    ...props.item,
    amount: 1
  }
  
  useCartStore().add(product).then(() => {
    useNoty().setNoty(t('noty.product_to_cart', {product: props.item.name}), 1000)
  })
}

</script>

<style src="./card.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div class="card">
    <NuxtLink :to="localePath('/' + item.slug)" :aria-label="item.name" clickable class="image-wrapper">
        <nuxt-img
          v-if="photo"
          :src = "photo"
          :alt = "item.image.alt || item.name"
          :title = "item.image.title || item.name"
          :class="item.image.size"
          width="290"
          height="260"
          sizes = "mobile:100vw tablet:230px desktop:300px"
          format = "webp"
          quality = "60"
          loading = "lazy"
          fit="outside"
          class="image"
        >
        </nuxt-img> 
    </NuxtLink>
    
    <div class="reviews">
      <simple-stars :amount="item.rating.rating" class="rating"></simple-stars>
      <button v-if="item.rating.reviews_count" class="reviews-btn">
        {{ item.rating.reviews_count }} отзывов
      </button>
      <button class="reviews-btn" v-else>
        <IconCSS name="iconoir:message" size="16"></IconCSS>
        Оставить отзыв
      </button>
    </div>

    <NuxtLink
      :to="localePath(`/${item.slug}`)"
      class="name"
      clickable
    >
      {{ item.name }}
    </NuxtLink>

    <hr class="line">

    <div class="amount">
      <div v-if="item.inStock > 2" class="available">{{ t('label.available') }}</div>
      <div v-else-if="item.inStock > 0 && item.inStock <= 2" class="running_out">{{ t('label.running_out') }}</div>
      <div v-else class="not_available">{{ t('label.not_available') }}</div>
    </div>

    <div class="sale">
      <button @click="toCartHandler" type="button" class="button primary small buy-btn">
        <span class="buy-btn-name">{{ t('buy') }}</span>
        <IconCSS name="iconoir:shopping-bag" class="buy-btn-icon"></IconCSS>
      </button>
      <div class="price">
        <simple-price v-if="+item.oldPrice" :value="+item.oldPrice" :currency="false" class="old-pr"></simple-price>
        <simple-price v-if="+item.price" :value="+item.price" class="pr"></simple-price>
      </div>
    </div>

  </div>
</template>