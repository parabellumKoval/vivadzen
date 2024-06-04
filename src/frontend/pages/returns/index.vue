<script setup>
const {t, locale} = useI18n()

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.returns'),
    item: '/returns'
  }
]
// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='~/assets/scss/_rich-text.scss' lang='scss' scoped></style>
<style src='./returns.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">{{ t('title.returns') }}</div>

      <div class="rich-text"> 

        <ContentQuery path="returns" :locale="locale" find="one">
          <template #default="{ data }">
            <h2>{{ data.title1 }}</h2>
            <p>{{ data.desc1 }}</p>
            <p>{{ data.desc2 }}</p>
            <ul v-html="data.list1"></ul>
            <p>{{ data.desc3 }}</p>
            <p>{{ data.desc4 }}</p>

            <h2>{{ data.title2 }}</h2>
            <p>{{ data.desc5 }}</p>
            <p>{{ data.desc6 }}</p>
            <p>{{ data.desc7 }}</p>
            <ul v-html="data.list2"></ul>

            <p>{{ data.desc8 }}</p>
            <ol v-html="data.list3"></ol>
            <p>{{ data.desc9 }}</p>
            <p>{{ data.desc10 }}</p>
            <p>{{ data.desc11 }}</p>
            <p>{{ data.desc12 }}</p>
          </template>
        </ContentQuery>

        </div>

    </div>
  </div>
</template>