<script setup>
import {useCategoryFaker} from '~/composables/fakers/useCategoryFaker.ts'
const {t} = useI18n()

const selectedIndex = ref(null)

const categories = computed(() => {
  return useCategoryFaker()(16)
})

const selectedCategory = computed(() => {
  if(selectedIndex.value === null)
    return null
  else
    return categories.value[selectedIndex.value]
})

const sub = computed(() => {
  if(selectedIndex.value === null)
    return []
  else 
    return categories.value[selectedIndex.value].children
})


const getPhotoSrc = (image) => {
  if(image?.src) {
    return image.src
    // return '/server/' + image.src
  } else {
    return null
  }
}

const backHandler = () => {
  selectedIndex.value = null
}

const selectHandler = (index) => {
  selectedIndex.value = index
}

if(useDevice().isDesktop) {
  selectedIndex.value = 0
}
</script>

<style src="./catalog.scss" lang="scss" scoped />

<template>
  <modal-wrapper>
    <div class="catalog">
      <div :class="{'mobile-active': !sub || !sub.length}" class="category-wrapper">
        <button
          v-for="(category, index) in categories"
          :key="category.id"
          @click="selectHandler(index)"
          :aria-label="category.name"
          :class="{active: selectedIndex === index}"
          class="category-item link"
          clickable
        >
          <nuxt-img
            v-if="category.image.src"
            :src = "getPhotoSrc(category.image)"
            width="40"
            height="40"
            sizes = "mobile:60px tablet:60px desktop:60px"
            format = "webp"
            quality = "60"
            loading = "lazy"
            fit="outside"
            class="category-image"
          >
          </nuxt-img>
          <div class="category-name">{{ category.name }}</div>
          <IconCSS v-if=" selectedIndex === index" name="iconoir:nav-arrow-right" class="category-icon"></IconCSS>
        </button>
      </div>
      <div :class="{'mobile-active': sub && sub.length}" class="sub-wrapper">
        <div v-if="selectedCategory" class="sub-header">
          <button @click="backHandler" class="sub-btn">
            <IconCSS name="iconoir:nav-arrow-left" size="24"></IconCSS>
          </button>
          <nuxt-img
            v-if="selectedCategory.image.src"
            :src = "getPhotoSrc(selectedCategory.image)"
            width="40"
            height="40"
            sizes = "mobile:60px tablet:60px desktop:60px"
            format = "webp"
            quality = "60"
            loading = "lazy"
            fit="outside"
            class="category-image"
          >
          </nuxt-img>
          <div class="sub-title">{{ selectedCategory.name }}</div>
        </div>
        <template v-for="category in sub" :key="category.id">
          <NuxtLink
            :to="localePath('/' + category.slug)"
            :aria-label="category.name"
            clickable
            class="sub-item link"
          >
            <div class="sub-name">{{ category.name }}</div>
          </NuxtLink>
          <ul v-if="category.children" class="last">
            <li v-for="child in category.children" :key="child.id" class="last-item">
              <NuxtLink :to="localePath('/' + child.slug)" class="last-link link">
                {{ child.name }}
              </NuxtLink>
            </li>
          </ul>
        </template>
      </div>
    </div>
  </modal-wrapper>
</template>