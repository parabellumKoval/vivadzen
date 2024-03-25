<script setup>
const props = defineProps({
  filter: {
    type: Object
  },
  depth: {
    default: 1
  }
})
</script>

<style src="./list.scss" lang="scss" scoped></style>

<template>
  <ul :class="'depth-' + depth" class="list">
    <li
      v-for="value in filter.values"
      :key="value.id"
      :class="{'has-children': value.children?.length}"
      class="item"
    >
      <NuxtLink :to="localePath('/brands/' + value.slug)" class="item-btn">
        <nuxt-img
          v-if="value.image?.src"
          :src = "value.image.src"
          width="40"
          height="40"
          sizes = "mobile:40px tablet:40px desktop:40px"
          format = "webp"
          quality = "80"
          loading = "lazy"
          fit="outside"
          class="item-image"
        >
        </nuxt-img>
        <span class="item-content">
          <span class="item-name">{{ value.name }}</span>
          <span v-if="value.children?.length" class="item-count">({{ value.children?.length }})</span>
        </span>
      </NuxtLink>
    </li>
  </ul>
</template>