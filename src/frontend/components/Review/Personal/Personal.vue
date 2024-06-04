<script setup>
const props = defineProps({
  item: {
    type: Object
  }
})

const {t} = useI18n()
const isOpen = ref(false)

// Refs
const contentRef = ref(null)

// COMPUTEDS
const photo = computed(() => {
  return props.item?.author?.photo || '/images/account.png'
})

const link = computed(() => {
  return props.item?.extras?.link || null
})

const isOverlay = computed(() => {

  if(contentRef.value) {

    const offset = 80 - contentRef.value.offsetHeight

    if(offset < 0) {
      return true
    }else{
      return false
    }

  }else {
    return true
  }
})

// METHODS
const toggleHandler = () => {
  isOpen.value = !isOpen.value
}
</script>

<style src="./personal.scss" lang="scss" scoped></style>

<template>
  <div class="personal-wrapper">
    <nuxt-img
      :src = "photo"
      :alt = "item?.author?.name"
      :title = "item?.author?.name"
      width = "130"
      height = "130"
      sizes = "mobile:100vw tablet:130px desktop:130px"
      format = "avif"
      quality = "60"
      loading = "lazy"
      fit = "outside"
      class = "image"
      placeholder = "/images/account.png"
    />

    <review-social v-if="link" :source="link" :name="item?.author?.name" class="nm"></review-social>
    <span v-else class="name nm">{{ item.author.name }}</span>

    <div class="content">

      <simple-stars
        :amount="item.rating"
        :size="20"
        mobile="large"
        class="rating"
      ></simple-stars>

      <div :class="[{active: isOpen}]" class="text-wrapper">
        <div ref="contentRef" class="text">{{ item.text }}</div>
      </div>

      <button v-if="isOverlay" @click="toggleHandler" class="text-link more-btn">
        <transition name="fade-in">
          <span v-if="isOpen">{{ t('button.collapse') }}</span>
          <span v-else>{{ t('button.expand') }}</span>
        </transition>
        <transition name="fade-in">
          <IconCSS v-if="isOpen" name="iconoir:nav-arrow-left"></IconCSS>
          <IconCSS v-else name="iconoir:nav-arrow-right"></IconCSS>
        </transition>
      </button>

      <div class="date">{{ $d(item.created_at, 'short') }}</div>
    </div>
  </div>
</template>