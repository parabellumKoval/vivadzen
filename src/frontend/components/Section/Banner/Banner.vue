<script setup></script>

<style src="./banner.scss" lang="scss" scoped></style>

<template>
  <section class="wrapper">
    <div class="item">
      <nuxt-img
        src="/images/banner.jpg"
        width="1440"
        height="350"
        sizes = "mobile:320px tablet:768px desktop:1440px"
        format = "avif"
        fit="inside"
        quality = "60"
        class = "image"
      />
    </div>
  </section>
</template>