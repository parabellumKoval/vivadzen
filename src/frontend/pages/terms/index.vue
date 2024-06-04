<script setup>
const {t, locale} = useI18n()

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.terms'),
    item: '/terms'
  }
]
// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='~/assets/scss/_rich-text.scss' lang='scss' scoped></style>
<style src='./terms.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">{{ t('title.terms') }}</div>

      <div class="rich-text"> 

        <ContentQuery path="terms" :locale="locale" find="one">
          <template #default="{ data }">
            <div v-html="data.title"></div>

            <ol class="ol-list">
              <li>
                <h2 v-html="data.title1"></h2>
                <ol v-html="data.li1"></ol>
              </li>

              <li>
                <h2 v-html="data.title2"></h2>
                <ol v-html="data.li2"></ol>
              </li>
              
              <li>
                <h2 v-html="data.title3"></h2>
                <ol v-html="data.li3"></ol>
              </li>

              
              <li>
                <h2 v-html="data.title4"></h2>
                <ol v-html="data.li4"></ol>
              </li>

              <li>
                <h2 v-html="data.title5"></h2>
                <ol v-html="data.li5"></ol>
              </li>

              <li>
                <h2 v-html="data.title6"></h2>
                <ol v-html="data.li6"></ol>
              </li>

              <li>
                <h2 v-html="data.title7"></h2>
                <ol v-html="data.li7"></ol>
              </li>
            </ol>
          </template>
        </ContentQuery>

        </div>

    </div>
  </div>
</template>