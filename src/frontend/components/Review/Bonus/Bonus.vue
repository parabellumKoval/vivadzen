<script setup>
const {t} = useI18n()
// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./bonus.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div>
    <div class="info-title">🎁 {{ t('messages.review_gift') }}</div>
    <ol class="info-list">
      <li><button class="text-link"><span>{{ t('cond1.one') }}</span></button> {{ t('cond1.two') }} Djini.com.ua</li>
      <li>{{ t('cond2') }}</li>
      <li>{{ t('cond3') }}</li>
      <li>{{ t('cond4.one') }} <span class="violet-text">{{ t('cond4.two') }}</span> {{ t('cond4.three') }}</li>
    </ol>
  </div>
</template>