<script setup>
const {t} = useI18n()
const props = defineProps({})
// COMPUTEDS
// METHODS
// HANDLERS
const contactsHandler = () => {
  useModal().open(resolveComponent('ModalContacts'), null)
}
// WATCHERS
</script>

<style src='./contacts.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div class="contacts-box">
    <div class="title-secondary">{{ t('have_q') }}</div>
    <div class="contacts-desc">{{ t('we_help') }} 👩‍💻</div>
    <div class="contacts">
      <button class="button contacts-button">
        <IconCSS name="iconoir:phone" class="inline-icon"></IconCSS>
        <span>{{ useContacts().phone }}</span>
      </button>
      <button @click="contactsHandler" class="button lowcase contacts-button-secondary">
        <span>{{ t('more') }}</span>
      </button>
    </div>
  </div>
</template>