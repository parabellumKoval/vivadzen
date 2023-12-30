<script setup>

const openIndex = ref(null)

const faq = computed(() =>{
  return [
    {
      id: 1,
      q: 'Какие самые популярные товары в категории сывороточный протеин в 2023 году?',
      a: 'Какие самые популярные товары в категории сывороточный протеин в 2023 году?'
    },{
      id: 2,
      q: 'Как происходит доставка и оплата товаров в категории сывороточный протеин?',
      a: "<p>Условия доставки и оплаты товаров в категории сывороточный протеин:</p><ul><li>Доставка осуществляется по Украине, транспортной компанией 'Новая Почта' в отделение или до дверей заказчика.</li><li>Отправка заказов происходит в течении 2-х рабочих дней после согласования оплаты.</li><li>Оплата доставки производится покупателем при получении заказа.</li></ul>"
    },{
      id: 3,
      q: 'Какие товары в категории сывороточный протеин самые дешевые?',
      a: 'Какие товары в категории сывороточный протеин самые дешевые?'
    }
  ]
})

const openHandler = (index) => {
  if(openIndex.value === index) {
    openIndex.value = null
  }else {
    openIndex.value = index
  }
}
</script>

<style src="./faq.scss" lang="scss" scoped></style>

<template>
  <ul class="faq-list">
    <li v-for="(item, index) in faq" :key="item.id" :class="{active: openIndex === index}" class="faq-item">
      <div class="faq-header">
        <span class="faq-count">{{ index + 1 }}</span>
        <button @click="openHandler(index)" class="faq-title">{{ item.q }}</button>
        <span class="faq-arrow">
          <IconCSS name="iconoir:nav-arrow-down" class="faq-icon"></IconCSS>
        </span>
      </div>
      <div class="faq-content" v-html="item.a"></div>
    </li>
  </ul>
</template>