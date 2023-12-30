<script setup>
const {t} = useI18n()

const accounts = ref([
  {
    id: 1,
    image: 'https://styles.redditmedia.com/t5_2fz35a/styles/profileIcon_pcyt47jx05891.png?width=256&height=256&frame=1&auto=webp&crop=256:256,smart&s=1e013b140ac0d6d90fb92f46759d2cf2a21cde50',
    name: 'Shopyoubest.com.ua',
    status: 'active'
  },{
    id: 2,
    image: 'https://www.babystore.lv/img/sm/categories/1043/ryp2zwkiby.jpg',
    name: 'Barbiqu.com',
    status: 'active'
  }
])

const selectHandler = (v) => {

}
</script>

<style src="./app.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('select_app')">
    <ul class="mobile-list">
      <li v-for="account in accounts" :key="account.id">
        <button @click="selectHandler(account.id)" class="select-btn">
          <span class="image-wrapper">
            <nuxt-img
              :src="account.image"
              width="20"
              height="20"
              sizes = "mobile:30px tablet:30px desktop:30px"
              format = "avif"
              fit = "cover"
              quality = "100"
              class = "image"
            />
          </span>
          <span class="name">{{ account.name }}</span>
          <span class="footer">
            <span :class="account.status" class="status">{{ account.status }}</span>
            <span class="selected">{{ t('selected') }}</span>
          </span>
        </button>
      </li>
    </ul>
    <button class="create-btn">
      <IconCSS name="ph:plus" size="20" class="icon"></IconCSS>
      <span class="name">{{ t('create_new') }}</span>
    </button>
  </modal-wrapper>
</template>