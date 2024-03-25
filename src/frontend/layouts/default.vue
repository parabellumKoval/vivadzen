<script setup>
import { useCategoryStore } from '~/store/category'
import { useModalStore } from '~~/store/modal';
import { useAuthStore } from '~~/store/auth';
import { useAppPersistStore } from '~/store/appPersist';

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
const { auth } = useSupabaseAuthClient()

auth.onAuthStateChange((event, session) => {
  console.log(event, session);
  if(event === 'SIGNED_OUT') {
    useNoty().setNoty(t('noty.logout'))
  }else if(event === 'INITIAL_SESSION'){
    if(session) {
      useAuthStore().setUserFromSession(session.user)
      
      if(useAppPersistStore().from === 'login') {
        useNoty().setNoty(t('noty.login_success'))
        useAppPersistStore().setFrom(null)
      }
    }else {
      useAuthStore().resetUser()
    }
  }else if(event === 'SIGNED_IN') {
    useAuthStore().setUserFromSession(session.user)
  }else if(event === 'PASSWORD_RECOVERY') {
  }
})

const user = computed(() => {
  return useAuthStore().user
})

// HANDLERS
const closeMenuMobileHandler = () => {
  useModalStore().close('menuMobile')
}

// WATCHERS
watch(locale, (v) => {
  refreshCategories()
})

// HOOKS
const {refresh: refreshCategories} = useAsyncData('all-categories', async () =>  await useCategoryStore().index())

useSchemaOrg([
  defineWebSite({
    url: 'https://abu.com.ua',
    name: 'abu.com.ua',
  }),
  defineWebPage(),
])
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


        <modal-transition :is-show="useModal().show" mode="out-in">
            <component :is="useModal().active.component"></component>
        </modal-transition>
        

        <simple-clicker></simple-clicker>
      </Body>
    </Html>
  </div>
</template>