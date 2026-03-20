<script setup>
const { t } = useI18n()

const contactsModal = defineAsyncComponent(() =>
  import('~/components/Modal/Contacts/Contacts.client.vue')
)

const openContactsModal = () => {
  useModal().open(contactsModal, null, null, {
    width: { min: 320, max: 900 }
  })
}
</script>

<template>
  <div class="support">
    <nuxt-img
      src="/images/landing/support-man.png"
      alt="Vivadzen Kratom Pack"
      width="1024"
      height="1024"
      quality="90"
      fit="cover"
      sizes="mobile: 100vw tablet: 550px desktop: 550px"
      class="support__img"
    />
    <div class="support__content">
      <h3 class="support__title">{{ t('title') }}</h3>
      <p class="support__desc">
        {{ t('description') }}
      </p>
      <button type="button" class="button orange support__link" @click="openContactsModal">
        {{ t('button') }}
      </button>
    </div>
  </div>
</template>

<style src="./support.scss" lang="scss" scoped></style>

<i18n src="./lang.yaml" lang="yaml"></i18n>