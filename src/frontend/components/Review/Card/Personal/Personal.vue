<script setup>
const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})
</script>

<style src="./personal.scss" lang="scss" scoped></style>

<template>
  <div class="card">
    <div class="card-content">
      <simple-stars
        :amount="item.rating" 
        mobile="large"
      ></simple-stars>
      <div class="card-text">{{ item.text }}</div>
    </div>
    <div class="author">
      <nuxt-img
          v-if="item.author.photo"
          :src = "item.author.photo"
          :alt = "item.author.name"
          :title = "item.author.name"
          width="50"
          height="50"
          sizes = "mobile:60px tablet:60px desktop:60px"
          format = "webp"
          quality = "60"
          loading = "lazy"
          fit="outside"
          class="author-image"
        >
      </nuxt-img>
      <div>
        <div class="author-name">{{ item.author.name }}</div>
        <div class="author-source">
          <a v-if="item.author.link?.type === 'instagram'" :href="item.author.link.href" target="_blank" class="social-link instagram-link">
            <IconCSS name="basil:instagram-outline" class="social-link-icon"></IconCSS>
            <span class="social-link-text">Instagram автора</span>
          </a>
          <a v-else-if="item.author.link?.type === 'facebook'" :href="item.author.link.href" target="_blank" class="social-link facebook-link">
            <IconCSS name="basil:facebook-outline" class="social-link-icon"></IconCSS>
            <span class="social-link-text">Facebook автора</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>