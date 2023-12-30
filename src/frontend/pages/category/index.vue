<script setup>
import {useProductFaker} from '~/composables/fakers/useProductFaker.ts'
import {useReviewFaker} from '~/composables/fakers/useReviewFaker.ts'

const {t} = useI18n()

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.catalog'),
    item: '/catalog'
  }
]

const reviews = computed(() => {
  return useReviewFaker()(4)
})

const products = computed(() => {
  return useProductFaker()(8)
})

const category = computed(() => {
  return {
    text: "<h2>Виды сывороточного протеина</h2><p class='p1'>Спортивное питание, где концентрация белка 50-75%, свидетельствует о том, что это недорогой протеин. К такому можно отнести как соевый, так и сывороточный протеин. Без них не составляются программы физических нагрузок для спортсменов, а наоборот они служат основой рациона.</p><p>Сывороточный протеин – глобулярный концентрированный белок, который получают при процеживании и скисании молока. Спортивная добавка имеет высокую биологическую ценность, которая заключается в содержании аминокислот в необходимых для организма человека пропорциях. Расщепление происходит пептидными связями.</p><p>В состав сывороточного протеина входят аминокислотные комплексы, которые незаменимы для всех, кто ведет активный образ жизни, занимаясь любым видом спорта. Интернет-магазин «Джини» предлагает купить сывороточный протеин по лучшим ценам в Киеве и по всей Украине.</p><h2>Как выбрать сывороточный протеин</h2><p>Для достижения высоких спортивных результатов, необходимо в свой рацион добавить быстроусвояемые белки. Их принято называть качественным спортивным питанием. Представлены разнообразными источниками белков, учитывающими образ жизни спортсмена (веганы, кошерное питание, сыроеды).</p><p>Сегодня спортивные биологически активные добавки выпускают многие компании производители. Все продукты отличаются не только составом, но и стоимостью. Каждый препарат имеет характерные свойства, которые обязательно нужно учитывать при покупке.</p>"
  }
})

</script>

<style src="./category.scss" lang="scss" scoped></style>

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">Сывороточный протеин</div>
    </div>

    <div class="header">
      <div class="container">
        <div class="header-inner">
          <filter-selected></filter-selected>
          <button class="button mini light sorting-btn">
            <IconCSS name="iconoir:sort-down" class="inline-icon"></IconCSS>
            <span>От дешевых к дорогим</span>
          </button>
        </div>
      </div>
    </div>

    <div class="content">
      <filter-list class="filters"></filter-list>
      <div class="content-grid">
        <product-card v-for="product in products" :key="product.id" :item="product" class="content-grid-item"></product-card>
      </div>
    </div>

    <div class="container">
      <div class="content">
        <div></div>
        <div>
          <simple-pagination :total="8" class="pagination"></simple-pagination>

          <div class="review">
            <div class="title-secondary review-title">Отзывы о Сыворочном протеине</div>
            <div class="review-header">
              <simple-stars :amount="4.4"></simple-stars>
              <div class="review-count">60 оценок и 78 отзывов</div>
            </div>
            <review-product v-for="review in reviews" :key="review.id" :item="review" type="mini" class="review-item"></review-product>
          </div>

          <div class="seo-text rich-text" v-html="category.text"></div>

          <section-faq></section-faq>
        </div>
      </div>
    </div>


  </div>
</template>