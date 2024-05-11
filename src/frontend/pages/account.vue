<script setup>
import { useAuthStore } from '~~/store/auth';
const {t} = useI18n()
const route = useRoute()

definePageMeta({
  bg: '#eee'
});

const props = defineProps({})

const isMenuOpen = ref(false)

const breadcrumbs = computed(() => {
  let list = [
    {
      name: t('title.home'),
      item: '/'
    },{
      name: t('title.account.account'),
      item: '/account'
    }
  ]

  if(route?.meta?.crumb) {
    let crumb = {...route?.meta?.crumb}
    crumb.name = t(crumb.name)
    list.push(crumb)
  }

  return  list
})

// COMPUTEDS
const activeMenuItem = computed(() => {
  return route?.meta?.tab
})

const menus = computed(() => {
  return [
    [
      {
        id: 1,
        title: t('title.account.orders'),
        slug: 'orders',
        icon: 'iconoir:shopping-bag',
        link: '/account/orders'
      },{
        id: 2,
        title: t('title.account.promocodes'),
        slug: 'promocodes',
        icon: 'iconoir:percentage',
        link: '/account/promocodes'
      },{
        id: 3,
        title: t('title.account.favorite'),
        slug: 'favorite',
        icon: 'iconoir:heart',
        link: '/account/favorite'
      }, {
        id: 4,
        title: t('title.account.referrals'),
        slug: 'network',
        icon: 'iconoir:user-crown',
        link: '/account/network/common'
      },{
        id: 5,
        title: t('title.account.settings'),
        slug: 'settings',
        icon: 'iconoir:settings',
        link: '/account/settings'
      },{
        id: 6,
        title: t('button.logout'),
        icon: 'iconoir:log-out',
        callback: logoutConfirmHandler
      }
    ],[
      {
        id: 7,
        title: 'Поддержка',
        slug: 'support',
        icon: 'iconoir:headset-help',
        link: '/account/support'
      }
    ]
  ]
})

// METHODS
// HANDLERS
const clickMenuHandler = (item) => {
  if(item.link){
    navigateTo(useLocalePath()(item.link))
  }else if(item.callback) {
    item.callback()
  }
}

const logoutHandler = () => {
  useAuthStore().logout().then(() => {
    navigateTo('/')
  })
}

const logoutConfirmHandler = () => {
  useModal().open(resolveComponent('ModalConfirm'), {
    title: 'Выход из аккаунта',
    desc: 'Вы точно хотите выйти из аккаунта?',
    yes: {
      title: 'Выйти',
      callback: logoutHandler
    },
    no: {
      title: 'Отмена',
      callback: null
    },
    type: 'default'
  }, null, {
    width: {
      max: 420
    }
  })
}

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

        <div class="profile-wrapper">
          <account-card></account-card>
          <button v-if="$device.isMobile" @click="openMenuHandler" class="more-btn">
            <IconCSS name="iconoir:more-vert" class="more-btn-icon"></IconCSS>
          </button>
        </div>

        <div v-if="!$device.isMobile || isMenuOpen" class="menu-wrapper">
          <ul v-for="(menu, index) in menus" :key="index" class="menu-ul">
            <li v-for="item in menu" :key="item.id" :class="{active: item.slug === activeMenuItem}" class="menu-li">
              <button @click="clickMenuHandler(item)" class="menu-link">
                <IconCSS :name="item.icon" class="menu-icon"></IconCSS>
                <span class="menu-title">{{ item.title }}</span>
              </button>
            </li>
          </ul>
        </div>
      </aside>
      
      <div class="content">
        <NuxtPage />
      </div>

    </div>
  </div>
  
</template>