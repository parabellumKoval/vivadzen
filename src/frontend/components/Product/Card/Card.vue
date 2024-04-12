<script setup lang="ts">
import {useCartStore} from '~/store/cart'
const { t } = useI18n()

const props = defineProps({
  item: {
    type: Object as PropType<Product>,
    required: true
  }
})

// COMPUTEDS
const stock = computed(() => {
  return props.item.inStock > 0? 'in-stock': 'not-in-stock'
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
    useNoty().setNoty({
      content: t('noty.product_to_cart', {product: props.item.name})
    }, 2000)
  })
}

</script>

<style src="./card.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div v-if="item" :class="stock" class="card">
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
      <simple-stars :amount="item?.rating || 0" class="rating"></simple-stars>
      <button v-if="item?.reviews_rating_detailes?.reviews_count || 0" class="reviews-btn">
        {{ item?.reviews_rating_detailes?.reviews_count || 0 }} отзывов
      </button>
      <button class="reviews-btn" v-else>
        <IconCSS name="iconoir:message" size="16"></IconCSS>
        Оставить отзыв
      </button>
    </div>

    <div class="name-wrapper">
      <NuxtLink
        :to="localePath(`/${item.slug}`)"
        class="name"
        clickable
      >
        {{ item.name }}
      </NuxtLink>
    </div>

    <hr class="line">

    <!-- <div class="amount">
    </div> -->
    <product-available :in-stock="item.inStock" class="amount"></product-available>

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