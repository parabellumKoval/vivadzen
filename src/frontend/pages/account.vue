<script setup>
const {t} = useI18n()

definePageMeta({
  bg: '#eee'
});

const props = defineProps({})

const isMenuOpen = ref(false)

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.catalog'),
    item: '/catalog'
  }
]

// COMPUTEDS
const photo = computed(() => {
  return '/images/avatars/1.jpg'
})

const user = computed(() => {
  return {
    name: 'Алина Штайнер',
    email: 'gopashola2@gmail.com'
  }
})

const menus = computed(() => {
  return [
    [
      {
        id: 1,
        title: 'История заказов',
        icon: 'iconoir:shopping-bag',
        link: '/account/orders'
      },{
        id: 2,
        title: 'Мои промокоды',
        icon: 'iconoir:percentage',
        link: '/account/promocodes'
      },{
        id: 3,
        title: 'Партнерская программа',
        icon: 'iconoir:user-crown',
        link: '/account/network/common'
      },{
        id: 4,
        title: 'Настройки',
        icon: 'iconoir:settings',
        link: '/account/settings'
      },{
        id: 5,
        title: 'Выйти',
        icon: 'iconoir:log-out',
        link: '/'
      }
    ],[
      {
        id: 6,
        title: 'Поддержка',
        icon: 'iconoir:headset-help',
        link: '/account/support'
      }
    ]
  ]
})
// METHODS
// HANDLERS
const openMenuHandler = () => {
  isMenuOpen.value = !isMenuOpen.value
}
// WATCHERS
</script>

<style src='./account/account.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="page-base container account-container">
    <div class="account-wrapper">
      <the-breadcrumbs :crumbs="breadcrumbs" class="account-breadcrumbs"></the-breadcrumbs>

      <aside class="aside">
        <div class="profile">
          <nuxt-img
            v-if="photo"
            :src = "photo"
            width="70"
            height="70"
            sizes = "mobile:100vw tablet:70px desktop:70px"
            format = "webp"
            quality = "60"
            loading = "lazy"
            fit="outside"
            class="profile-image"
          >
          </nuxt-img>
          <div class="profile-data">
            <div class="profile-name">{{ user.name }}</div>
            <div class="profile-email">{{ user.email }}</div>
          </div>
          <button v-if="$device.isMobile" @click="openMenuHandler" class="more-btn">
            <IconCSS name="iconoir:more-vert" class="more-btn-icon"></IconCSS>
          </button>
        </div>
        <div v-if="!$device.isMobile || isMenuOpen" class="menu-wrapper">
          <ul v-for="(menu, index) in menus" :key="index" class="menu-ul">
            <li v-for="item in menu" :key="item.id" class="menu-li">
              <NuxtLink :to="localePath(item.link)" class="menu-link">
                <IconCSS :name="item.icon" class="menu-icon"></IconCSS>
                <span class="menu-title">{{ item.title }}</span>
              </NuxtLink>
            </li>
          </ul>
        </div>
      </aside>
      <div class="content">
        <NuxtPage />
      </div>

      <!-- <div class="account-wrapper">
      </div> -->
    </div>
  </div>
  
</template>