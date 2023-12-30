<script setup>
const { t } = useI18n()

const openContactsHandler = (event) => {
  useModal().open(resolveComponent('ModalContacts'), null, null)
}
</script>

<style src="./contacts.scss" lang="scss" scoped />

<template>
  <button @click="openContactsHandler" class="contacts-btn btn" type="button" clickable>
    <IconCSS name="iconoir:phone" size="20px" class="icon"></IconCSS>
    <span class="text">{{ t('phone') }}</span>
  </button>
</template>

<i18n lang="yaml">
uk:
  phone: "+38 (099) 777-33-45"
ru:
  phone: "+38 (099) 777-33-45"
</i18n>