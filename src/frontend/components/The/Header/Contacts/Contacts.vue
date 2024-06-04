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
    <span class="text">{{ useContacts().phone }}</span>
  </button>
</template>