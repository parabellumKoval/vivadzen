<script setup>
const localePath = useLocalePath()
const {t} = useI18n()

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.catalog'),
    item: '/catalog'
  }
]

const tab = ref(0)

const tabs = computed(() => {
  return [
    {
      id: 1,
      name: 'Отзывы о магазине <span class="budge green">34</span>'
    },{
      id: 2,
      name: 'Отзывы о товарах <span class="budge green">1532</span>'
    }
  ]
})

watch(tab, (index) => {
  if(index === 0) {
    navigateTo(localePath('/reviews/shop'))
  }else if(index === 1) {
    navigateTo(localePath('/reviews/products'))
  }
})

watch(() => useRoute().meta.tab, (v) => {
  tab.value = v
}, {
  immediate: true
})
// HOOK
// if(useRoute()?.meta?.tab !== undefined) {
//   tab.value = useRoute().meta.tab
// }
</script>

<i18n src="./reviews/lang.yaml" lang="yaml"></i18n>
<style src="./reviews/reviews.scss" lang="scss" scoped></style>

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>
    </div>

    <simple-tabs v-model="tab" :values="tabs" value="name" class="tab-wrapper"></simple-tabs>
      
    <div class="container">
      <div class="grid">
        <div>
          <NuxtPage />
        </div>
        <div>
          <div class="review-form">
            <div class="review-form-title">
              {{ t('messages.leave_review') }}<br> 
              {{ tab === 0? t('review_shop'): t('review_product') }}
            </div>
            <button class="button violet wide large-icon inline-icon">
              <IconCSS name="iconoir:message-text" class="icon"></IconCSS>
              <span>{{ t('messages.leave_review') }}</span>
            </button>
          </div>
          <div class="info-wrapper">
            <div class="info-title">🎁 Купон на -15% за верифицированный отзыв о магазине</div>
            <ol class="info-list">
              <li><button class="text-link"><span>Напишите отзыв</span></button> о магазине Djini.com.ua</li>
              <li>Прикрепите к отзыву ссылку на ваш профиль в соц. сети, чтобы другие пользователи могли убедиться, что ваш отзыв настоящий</li>
              <li>После прохождения модерации ваш отзыв будет опубликован</li>
              <li>Получите вознаграждение в виде <span class="violet-text">купона -5%</span> на любую продукцию</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>