<script setup>
const {locale} = useI18n()

const text = await queryContent('guarantees').locale(locale.value).findOne()

const guarantees = [
  {
    id: 1,
    desc: text.item1,
  },{
    id: 2,
    desc: text.item2,
  },{
    id: 3,
    desc: text.item3,
  },{
    id: 4,
    desc: text.item4,
  }
]

</script>

<style src="./info.scss" lang="scss" scoped />

<template>
  <div class="guarantees">
    <ul class="list">
      <li v-for="item in guarantees" :key="item.id" class="list-item">
        {{ item.desc }}
      </li>
    </ul>
  </div>
</template>