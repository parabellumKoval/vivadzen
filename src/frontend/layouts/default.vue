<script setup>
import { useCategoryStore } from '~/store/category'
import { useAuthStore } from '~~/store/auth';
import { useAppPersistStore } from '~/store/appPersist';
import { useFavoritesStore } from '~/store/favorites';

const {t, locale} = useI18n()
const route = useRoute()

const head = useLocaleHead({
  addDirAttribute: true,
  identifierAttribute: 'id',
  addSeoAttributes: true
})

const title = "Djini";

// COMPUTEDS
const background = computed(() => {
  return route?.meta?.bg || '#fff'
})

// AUTH
const { auth } = useSupabaseClient()

auth.onAuthStateChange((event, session) => {
  // console.log(event, session);
  if(event === 'SIGNED_OUT') {
    useNoty().setNoty({
      content: t('noty.auth.logout')
    })
  }else if(event === 'INITIAL_SESSION'){
    if(session) {
      useAuthStore().setUserFromSession(session.user)
      // useFavoritesStore().getIds({user_id: user?.value?.id})
      
      if(useAppPersistStore().from === 'login') {
        useNoty().setNoty({
          content: t('noty.auth.login.success')
        })
        useAppPersistStore().setFrom(null)
      }
    }else {
      useAuthStore().resetUser()
    }
  }else if(event === 'SIGNED_IN') {
    useAuthStore().setUserFromSession(session.user)
    // useFavoritesStore().getIds({user_id: user?.value?.id})
    // getFavoriteIds()
  }else if(event === 'PASSWORD_RECOVERY') {
  }
})

const user = computed(() => {
  return useAuthStore().user
})

// HANDLERS
// const closeMenuMobileHandler = () => {
//   useModalStore().close('menuMobile')
// }

// METHODS

// const getFavoriteIds = async () => {
//   if(!user || !user.value?.id)
//     return

//   await useAsyncData('favorites', () => useFavoritesStore().getIds({user_id: user.value.id}))
// }

// WATCHERS
watch(locale, (v) => {
  refreshCategories()
})

// HOOKS
const {refresh: refreshCategories} = useAsyncData('all-categories', async () =>  await useCategoryStore().index())

useSchemaOrg([
  defineWebSite({
    url: 'https://djini.com.ua',
    name: 'djini.com.ua',
  }),
  defineWebPage(),
])

// await useAsyncData('favorites', () => useFavoritesStore().getIds({user_id: user?.value?.id}))
// await getFavoriteIds()
</script>

<style src="~/assets/scss/layout-default.scss" lang="scss" scoped />


<template>
  <div>
    <Html :lang="head.htmlAttrs.lang" :dir="head.htmlAttrs.dir">
      <Head>
        <Title>{{ title }}</Title>
        <template v-for="link in head.link" :key="link.id">
          <Link :id="link.id" :rel="link.rel" :href="link.href" :hreflang="link.hreflang" />
        </template>
        <template v-for="meta in head.meta" :key="meta.id">
          <Meta :id="meta.id" :property="meta.property" :content="meta.content" />
        </template>
        <Meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no" />
      </Head>
      <Body>
        
        <the-supheader></the-supheader>

        <the-header></the-header>
        
        <main class="main" :style="{background: background}">
          <slot />
        </main>

        <lazy-modal-noty></lazy-modal-noty>
      
        <lazy-the-footer></lazy-the-footer>

        <comparison-btn></comparison-btn>

        <modal-transition :is-show="useModal().show" mode="out-in">
          <component :is="useModal().active.component"></component>
        </modal-transition>
        

        <simple-clicker></simple-clicker>
      </Body>
    </Html>
  </div>
</template>