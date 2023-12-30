<script setup>
import {useCategoryStore} from '~/store/category'
const {t, locales} = useI18n()

const emit = defineEmits([
  'close'
])

// COMPUTED
const categories = computed(() => {
  return useCategoryStore().all
})

const menu = computed(() => {
  return useMenu().all.value
})

// HANDLERS
const closeHandler = () => {
  emit('close')
}

</script>

<style src="./menu-mobile.scss" lang="scss" scoped />

<template>
  <div class="menu-wrapper">

    <button @click="closeHandler" class="close-btn" type="button">
      <IconCSS name="fluent:dismiss-20-regular" size="30" class="icon"></IconCSS>
    </button>

    <div class="nav-container" scrollable>
      <ul class="categories nav">
        <li v-for="cat in categories" :key="cat.id"  class="nav-item">
          <NuxtLink
            :to="localePath(`/shop/${cat.slug}`)"
            @click="closeHandler"
            clickable
            class="nav-link"
          >
            {{ cat.name }}
          </NuxtLink>
        </li>
      </ul>

      <ul class="menu nav">
        <li v-for="li in menu" :key="li.id"  class="nav-item">
          <NuxtLink
            :to="localePath(li.link)"
            @click="closeHandler"
            clickable
            class="nav-link"
          >
            {{ li.title }}
          </NuxtLink>
        </li>
      </ul>
    </div>

    <ul class="langs">
      <li
        v-for="locale in locales"
        :key="locale.code"
        @click="selectHandler"
        clickable
        class="language"
      >
        <nuxt-link
          :to="switchLocalePath(locale.code)"
          :class="{selected: $i18n.locale === locale.code}"
          class="link language-link"
          :aria-label="locale.name"
          @click="closeHandler"
        >
          <span class="language-name">
            {{ locale.name }}
          </span>
          
          <IconCSS v-if="$i18n.locale === locale.code" name="fluent:checkmark-12-filled" size="14px" class="icon" ></IconCSS>
          </nuxt-link>
      </li>
    </ul>

  </div>
</template>