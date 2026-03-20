<script setup lang="ts">
const { t, locale } = useI18n()
const text = await queryContent('about').locale(locale.value).findOne()

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.about'), item: '/about' },
])

useSeo().setPageSeo(t('title.about'))
</script>

<template>
  <div class="page-base kratom-content-page">
    <div class="container kratom-content-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />
      <h1 class="title-common">{{ t('title.about') }}</h1>
      <div class="rich-text kratom-content-card">
        <section v-if="text?.hero" class="kratom-about-hero">
          <div>
            <p class="kratom-content-shell__eyebrow">{{ text.hero.eyebrow }}</p>
            <h2>{{ text.hero.title }}</h2>
            <p v-for="(paragraph, index) in text.hero.paragraphs" :key="index">{{ paragraph }}</p>
          </div>
          <nuxt-img v-if="text.hero.image?.src" :src="text.hero.image.src" :alt="text.hero.image.alt" width="1024" height="640" />
        </section>
        <section v-if="text?.quote" class="kratom-about-quote">
          <p class="kratom-content-shell__eyebrow">{{ text.quote.title }}</p>
          <blockquote>{{ text.quote.text }}</blockquote>
        </section>
        <section v-for="block in text?.mediaBlocks || []" :key="block.title" class="kratom-about-block">
          <div>
            <h2>{{ block.title }}</h2>
            <p v-for="(paragraph, index) in block.body" :key="index">{{ paragraph }}</p>
          </div>
          <nuxt-img v-if="block.image?.src" :src="block.image.src" :alt="block.image.alt" width="920" height="620" />
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-content-shell { max-width: 1080px; padding-top: 32px; }
.kratom-content-shell__eyebrow { margin-bottom: 12px; color: #8a5a2b; text-transform: uppercase; letter-spacing: .12em; font-size: 12px; font-weight: 700; }
.kratom-content-card { display: grid; gap: 28px; padding: 28px; border-radius: 32px; background: rgba(255,250,244,.92); border: 1px solid rgba(74,91,68,.1); }
.kratom-about-hero, .kratom-about-block { display: grid; gap: 22px; align-items: start; @include tablet { grid-template-columns: minmax(0, 1.1fr) minmax(0, .9fr); } }
.kratom-about-hero img, .kratom-about-block img { width: 100%; border-radius: 24px; }
.kratom-about-quote { padding: 24px; border-radius: 24px; background: linear-gradient(135deg, rgba(53,82,74,.08), rgba(138,90,43,.08)); }
.kratom-about-quote blockquote { margin: 0; font-size: 24px; line-height: 1.55; font-family: var(--font-display); }
</style>
