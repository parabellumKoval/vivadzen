<script setup>
import { useModalStore } from '~~/store/modal';

const {t} = useI18n()
</script>

<style src="./message.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('your_message')">
    <message-form></message-form>
  </modal-wrapper>
</template>