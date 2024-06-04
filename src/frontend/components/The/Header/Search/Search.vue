<script setup>
import {useSearchStore} from '~/store/search'

const { t } = useI18n()
const searchInput = ref(null)
const isActive = ref(false)

const categories = ref([])
const products = ref([])
const brands = ref([])

const isLoading = ref(false)
const timeout = ref(null)

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
const history = computed(() => {
  return useSearchStore().getHistory
})

// METHODS
const getPhoto = (item, segment) => {
  if(item.image?.src) {
    return '/server/images/' + segment + '/' + item.image.src
  }else {
    return '/images/noimage.png'
  }
}


const goToSearchPage = async () => {
  closeHandler()

  await navigateTo({
    path: '/search',
    query: {
      q: searchInput.value
    }
  })
}

const setInput = (search) => {
  searchInput.value = search
}

const search = async (search) => {

  timeout.value = null

  const params = {
    search: search
  }

  if(!params.search?.length) {
    categories.value = null
    products.value = null
    return
  }

  isLoading.value = true

  await useAsyncData('livesearch', () => useSearchStore().livesearch(params)).then(({data, error}) => {
    if(data?.value) {
      categories.value = data.value.categories
      products.value = data.value.products
      brands.value = data.value.brands
    }
  }).finally(() => {
    isLoading.value = false
  })
}

// WATCH
watch(searchInput, (v) => {
  clearTimeout(timeout.value)

  timeout.value = setTimeout(() => {
    search(v)
  }, 1000)
}, {
  deep: true,
})
</script>

<style src="./search.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div :class="{active: isActive && $device.isMobile}" class="search-wrapper">
    <simple-search
      v-model="searchInput"
      @input:focus="focusHandler"
      @input:blur="blurHandler"
      @closo="closeHandler"
      @btn:click="goToSearchPage"
      class="simple-search"
    ></simple-search>

    <transition name="fade-in">
      <div v-if="isActive" class="livesearch-wrapper">
        <div class="livesearch">

          <transition name="fade-in">
            <div
              v-if="isLoading"
              class="livesearch-box message-box"
            >
              {{ t('searching') }}...
            </div>
          </transition>

          <div v-if="timeout" class="typing">
            <div class="dot">•</div>
            <div class="dot">•</div>
            <div class="dot">•</div>
          </div>

          <transition name="fade-in">
            <div
              v-if="!timeout && !isLoading && searchInput?.length && !categories?.length && !products?.length"
              class="livesearch-box message-box"
            >
              {{ t('no_res') }}
            </div>
          </transition>

          <div v-if="categories?.length" class="livesearch-box">
            <div class="livesearch-label">{{ t('title.categories') }}</div>
            <ul class="livesearch-list">
              <li v-for="item in categories" :key="item.id" class="livesearch-item">
                <NuxtLink :to="localePath('/' + item.slug)" class="livesearch-link">
                  <span class="value">{{ item.name }}</span>
                </NuxtLink>
              </li>
            </ul>
          </div>

          <div v-if="brands?.length" class="livesearch-box">
            <div class="livesearch-label">{{ t('title.brands') }}</div>
            <ul class="livesearch-list">
              <li v-for="item in brands" :key="item.id" class="livesearch-item">
                <NuxtLink :to="localePath('/brands/' + item.slug)" class="livesearch-link brand-item">
                  <nuxt-img
                    :src = "getPhoto(item, 'brands')"
                    width="50"
                    height="60"
                    sizes = "mobile:50px tablet:50px desktop:50px"
                    format = "webp"
                    quality = "60"
                    loading = "lazy"
                    fit="outside"
                    class="item-image"
                    placeholder="/images/noimage.png"
                  >
                  </nuxt-img>
                  <span class="value">{{ item.name }}</span>
                </NuxtLink>
              </li>
            </ul>
          </div>

          <div v-if="products?.length" class="livesearch-box">
            <div class="livesearch-label">{{ t('title.products') }}</div>
            <ul class="livesearch-list">
              <li v-for="item in products" :key="item.id" class="livesearch-item">
                <NuxtLink :to="localePath('/' + item.slug)" class="livesearch-link product-card">
                  <nuxt-img
                    :src = "getPhoto(item, 'products')"
                    width="50"
                    height="60"
                    sizes = "mobile:50px tablet:50px desktop:50px"
                    format = "webp"
                    quality = "60"
                    loading = "lazy"
                    fit="outside"
                    class="item-image"
                    placeholder="/images/noimage.png"
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

          <div v-if="history?.length" class="livesearch-box">
            <div class="livesearch-label">{{ t('search_history') }}</div>
            <ul class="livesearch-inline">
              <li v-for="item in history" :key="item" class="livesearch-item">
                <button @click="setInput(item)" class="livesearch-link">
                  <span class="value">{{ item }}</span>
                </button>
              </li>
            </ul>
          </div>


          <div v-if="products?.length" class="livesearch-box">
            <button @click="goToSearchPage" class="all-results-btn">
              <span class="text">{{ t('all') }}</span>
              <IconCSS name="iconoir:arrow-right" class="icon"></IconCSS>
            </button>
          </div>


          <span
            v-if="categories?.length || products?.length || brands?.length"
            class="powered-by"
          >
            <span>Powered by </span>

            <nuxt-img
              src = "/images/algolia.png"
              width="30"
              height="30"
              sizes = "mobile:50px tablet:50px desktop:50px"
              format = "webp"
              quality = "100"
              loading = "lazy"
              fit="outside"
              class="algolia"
            />
          </span>
        </div>
      </div>
    </transition>
  </div>
</template>