<script setup>
import {computed} from 'vue'
import {useCartStore} from '~/store/cart'
import {useCard} from '~/composables/product/useCard.ts'

const { t } = useI18n()
const regionPath = useToLocalePath()
const props = defineProps({
  item: {
    type: Object
  }
})

const isExternal = computed(() => Boolean(props.item?.external))

// .updateAmount(props.item.id)
const {photo, photoAlt, photoTitle, photoSize} = useCard(props.item)

const deleteHandler = () => {
  useCartStore().remove(props.item.id)
  useNoty().setNoty({
    content: t('noty.product_delete_cart', {product: props.item.name})
  }, 1000)


  useGoogleEvent().setEvent('RemoveFromCart', props.item)
}

const setAmountHandler = (newAmount) => {
  useCartStore().updateAmount(props.item.id, newAmount)
}

// watch(() => props.item.amount, (newAmount) => {
// })


// watch(() => props.item, (v) => {
//   console.log('item changed', v)
// }, {deep: true})
</script>

<style src="./checkout.scss" lang="scss" scoped></style>

<template>
  <div class="checkout-card">
    <div :class="['wrapper', { 'wrapper--external': isExternal }]">
      <NuxtLink :to="regionPath('/' + item.slug)" :aria-label="item.name" clickable class="image-wrapper">
        <nuxt-img
          :src = "photo"
          :alt = "photoAlt"
          :title = "photoTitle"
          :class = "photoSize"
          width="85"
          height="110"
          sizes = "mobile:85px tablet:85px desktop:120px"
          format = "webp"
          provider = "bunny"
          quality = "80"
          loading = "lazy"
          fit="outside"
          placeholder="/images/noimage.png"
          class="image"
        >
        </nuxt-img> 
      </NuxtLink>
      <div class="body">
        <span v-if="item.code" class="code label">{{ item.code }}</span>
        <NuxtLink
          :to="regionPath('/' + item.slug)"
          :aria-label="item.name"
          clickable
          class="name"
        >{{ item.name }}</NuxtLink>

        <div v-if="item.shortName" class="product-modification">{{ item.shortName }}</div>
      </div>
      <div class="footer">
        <form-amount :modelValue="item.amount" @update:modelValue="setAmountHandler"></form-amount>
        <div>
          <button @click="deleteHandler" class="remove-btn">{{ t('button.delete') }}</button>
        </div>
        <product-price :price="item.totalPrice" :old-price="item.totalOldPrice"></product-price>
      </div>
    </div>
    <p v-if="isExternal" class="unavailable-note">{{ t('messages.cart_item_unavailable') }}</p>
  </div>
</template>
