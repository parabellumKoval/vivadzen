<script setup>
const { t } = useI18n()

const props = defineProps({
  item: {
    type: Object
  }
})

const amount = ref(1)

const photo = computed(() => {
  if(props.item.image?.src) {
    return props.item.image.src
    // return '/server/' + props.item.image.src
  } else {
    return null
  }
})

const deleteHandler = () => {}
</script>

<style src="./small.scss" lang="scss" scoped></style>

<template>
<div class="product">
  <div class="product-inner">
    <nuxt-img
      v-if="photo"
      :src = "photo"
      :alt = "item.image.alt || item.name"
      :title = "item.image.title || item.name"
      :class="item.image.size"
      width="100"
      height="100"
      sizes = "mobile:100px tablet:100px desktop:100px"
      format = "webp"
      quality = "60"
      loading = "lazy"
      fit="outside"
      class="image"
    >
    </nuxt-img>

    <div class="body">
      <span v-if="item.code" class="code label">
        код товара: 
        <span class="value">{{ item.code }}</span>
      </span>
      <span class="name">{{ item.name }}</span>
    </div>
  </div>

  <div class="price-block">
    <simple-price :value="item.oldPrice" class="price-old"></simple-price>
    <simple-price :value="item.price" class="price-base"></simple-price>
  </div>
</div>
</template>