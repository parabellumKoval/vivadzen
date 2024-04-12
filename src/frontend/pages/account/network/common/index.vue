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
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div>
    <div class="header">
      <div class="header-item">
        <div class="label">Текущий балланс</div>
        <div class="value">
          <div class="value-price">{{ $n(456, 'currency') }}</div>
        </div>
      </div>
      <div class="header-item">
        <div class="label">Ваш партнерский код</div>
        <div class="value">
          <promocode-code :value="referralCode" class="value-code"></promocode-code>
        </div>
      </div>
      <div class="header-item header-item-link">
        <div class="label">Ваша партнерская ссылка</div>
        <div class="value">
          <form-link v-model="referralLink" placeholder="https://"></form-link>
        </div>
      </div>
    </div>
    <ul class="conds">
      <li class="conds-item">Бонусный баланс можно использовать для оплаты заказов</li>
      <li class="conds-item">Вы можете заказать <button class="text-link"><span>вывод средств</span></button> при балансе от 500 ГРН</li>
    </ul>
    <div class="percent">
      <div class="percent-item">
        <div class="percent-value">5<span class="symbol">%</span></div>
        <div class="percent-text">Доход от рефералов <span class="select">первого</span> 🤝 уровня</div>
      </div>
      <div class="percent-item">
        <div class="percent-value">3<span class="symbol">%</span></div>
        <div class="percent-text">Доход от рефералов <span class="select">второго</span> 🤝 🤝 уровня</div>
      </div>
    </div>
  </div>
</template>