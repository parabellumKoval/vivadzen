<script setup>
const {t} = useI18n()

definePageMeta({
  subTab: 0
});

const referralLink = ref('https://djini.com.ua?ref=GTRE43')
const referralCode = ref('GTRE43')
const props = defineProps({})
// COMPUTEDS

// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./common.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div>
    <div class="header">
      <div class="header-item">
        <div class="label">{{ t('current') }}</div>
        <div class="value">
          <simple-price :value="0" class="value-price"></simple-price>
        </div>
      </div>
      <div class="header-item">
        <div class="label">{{ t('your_code') }}</div>
        <div class="value">
          <promocode-code :value="referralCode" class="value-code"></promocode-code>
        </div>
      </div>
      <div class="header-item header-item-link">
        <div class="label">{{ t('your_link') }}</div>
        <div class="value">
          <form-link v-model="referralLink" placeholder="https://"></form-link>
        </div>
      </div>
    </div>
    <ul class="conds">
      <li class="conds-item">{{ t('desc_1') }}</li>
      <li class="conds-item">
        {{ t('desc_2_1') }} 
        <button class="text-link">
          <span>{{ t('desc_2_2') }}</span>
        </button>
        {{ t('desc_2_3') }}
      </li>
    </ul>
    <div class="percent">
      <div class="percent-item">
        <div class="percent-value">5<span class="symbol">%</span></div>
        <div class="percent-text">
          {{ t('income') }} <span class="select">{{ t('first') }}</span> 🤝 {{ t('level') }}
        </div>
      </div>
      <div class="percent-item">
        <div class="percent-value">3<span class="symbol">%</span></div>
        <div class="percent-text">
          {{ t('income') }} <span class="select">{{ t('second') }}</span> 🤝 🤝 {{ t('level') }}
        </div>
      </div>
    </div>
  </div>
</template>