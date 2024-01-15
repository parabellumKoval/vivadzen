<script setup>
const { t } = useI18n()
const searchInput = ref(null)
const isActive = ref(false)

const focusHandler = () => {
  isActive.value = true
}

const blurHandler = () => {
  if(!useDevice().isMobile) {
    isActive.value = false
  }
}

const closeHandler = () => {
    isActive.value = false
}

// COMPUTEDS
const categories = computed(() => {
  return [
    {
      id: 1,
      name: 'Биологические добавки'
    },{
      id: 2,
      name: 'Бады'
    },{
      id: 3,
      name: 'Биоэнергетические напитки'
    }
  ]
})

const history = computed(() => {
  return [
    {
      id: 1,
      name: 'Спортивное питание Украина'
    },{
      id: 2,
      name: 'Купить витамины'
    },{
      id: 3,
      name: 'Биоэнергетические напитки'
    }
  ]
})

const products = computed(() => {
  return [
    {
      id: 1,
      name: 'Морской коллаген с витамином С, En`vie Lab, 5250 мг, 30 порций/COMPLEX 2',
      price: 656,
      oldPrice: 893,
      image: '/images/products/1.png'
    },{
      id: 2,
      name: 'Морской коллаген с витамином С',
      price: 656,
      oldPrice: 893,
      image: '/images/products/2.png'
    }
  ]
})
</script>

<style src="./search.scss" lang="scss" scoped />

<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div :class="{active: isActive && $device.isMobile}" class="search-wrapper">
    <div class="search">
      <div class="search-inner" type="button" clickable>
        <IconCSS v-if="!$device.isMobile" name="iconoir:search" size="20px" class="search-input-icon"></IconCSS>
        <input
          @focus="focusHandler"
          @blur="blurHandler"
          v-model="searchInput"
          :placeholder="t('search')"
          class="search-input"
        />
        <button @click="closeHandler" class="search-close">
          <IconCSS v-if="$device.isMobile" name="iconoir:cancel"  class="search-close-icon"></IconCSS>
        </button>
        <button class="search-action">
          <IconCSS v-if="$device.isMobile" name="iconoir:search" class="search-action-icon"></IconCSS>
          <span v-else-if="!$device.isMobile" class="search-action-text">{{ t('find') }}</span>
        </button>
      </div>
    </div>

    <transition name="fade-in">
      <div v-if="isActive" class="livesearch-wrapper">
        <div class="livesearch">
          <NuxtLink :to="localePath('/search')" class="all-results-btn">
            <span class="text">Все результаты поиска</span>
            <IconCSS name="iconoir:arrow-right" class="icon"></IconCSS>
          </NuxtLink>

          <div class="livesearch-box">
            <div class="livesearch-label">Категории</div>
            <ul class="livesearch-list">
              <li v-for="item in categories" :key="item.id" class="livesearch-item">
                <NuxtLink :to="localePath('/')" class="livesearch-link">
                  <span class="value">{{ item.name }}</span>
                </NuxtLink>
              </li>
            </ul>
          </div>
          <div class="livesearch-box">
            <div class="livesearch-label">Товары</div>
            <ul class="livesearch-list">
              <li v-for="item in products" :key="item.id" class="livesearch-item">
                <NuxtLink :to="localePath('/')" class="livesearch-link product-card">
                  <nuxt-img
                    v-if="item.image"
                    :src = "item.image"
                    width="50"
                    height="60"
                    sizes = "mobile:50px tablet:50px desktop:50px"
                    format = "webp"
                    quality = "60"
                    loading = "lazy"
                    fit="outside"
                    class="product-image"
                  >
                  </nuxt-img>
                  <div class="product-content">
                    <div class="product-name">{{ item.name }}</div>
                    <div class="product-price">
                      <simple-price v-if="item.oldPrice" :value="item.oldPrice" class="old-price"></simple-price>
                      <simple-price v-if="item.price" :value="item.price" class="base-price"></simple-price>
                    </div>
                  </div>
                </NuxtLink>
              </li>
            </ul>
          </div>
          <div class="livesearch-box">
            <div class="livesearch-label">История поиска</div>
            <ul class="livesearch-list">
              <li v-for="item in history" :key="item.id" class="livesearch-item">
                <NuxtLink :to="localePath('/')" class="livesearch-link">
                  <span class="value">{{ item.name }}</span>
                </NuxtLink>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>