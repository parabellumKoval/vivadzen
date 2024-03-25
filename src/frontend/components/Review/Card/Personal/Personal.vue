<script setup>
const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})

const link = computed(() => {
  let url = null
  let link = {
    type: null,
    href: props.item?.extras?.link || null
  }

  if(!link.href) {
    return link;
  }

  // type = link.match('^(?:https?://)?(?:www.)?(\w*)\/')

  try {
    url = new URL(link.href)
  }catch(e) {
    console.log(e)
  }

  if(url.host.match('(?:www.)?facebook.com')){ 
    link.type = 'facebook'
  }else if(url.host.match('(?:www.)?instagram.com')) {
    link.type = 'instagram'
  }else {
    link.type = null
  }
  
  return link
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
          <a :href="link.href" :class="link.type + '-link'" target="_blank" rel="nofollow" class="social-link" >
            <IconCSS :name="'basil:' + link.type +'-outline'" class="social-link-icon"></IconCSS>
            <span class="social-link-text">{{ link.type }} автора</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>