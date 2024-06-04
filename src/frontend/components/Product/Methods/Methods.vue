<script setup>
const props = defineProps({
  items: {
    type: Array
  }
})
</script>

<style src="./methods.scss" lang="scss" scoped />

<template>
  <div>
    <div class="items">
      <div
        v-for="item in items"
        :key="item.id"
        class="item"
      >
        <div class="header">
          <IconCSS :name="item.icon" size="24" class="icon"></IconCSS>
          <span class="title">{{ item.name }}</span>
          <span class="line"></span>
          <span class="logo">
            <nuxt-img
              :src = "item.logo"
              :alt = "item.name"
              :title = "item.name"
              sizes = "mobile:155px tablet:155px desktop:155px"
              width = "auto"
              height = "60"
              format = "webp"
              fit = "inside"
              quality = "100"
              loading = "lazy"
              class="image"
            />
          </span>
        </div>

        <div class="content" v-html="item.desc"></div>
      </div>
    </div>
  </div>
</template>