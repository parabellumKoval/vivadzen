<script setup lang="ts">
const { t, locale } = useI18n()
definePageMeta({
  pageBackground: '#071d19',
  pageTheme: 'dark',
})

const text = await queryContent('about').locale(locale.value).findOne()

const heroImage = '/images/landing/bonus/bg2.png'

const blockImages = [
  {
    src: '/images/landing/laboratory.png',
    width: 3264,
    height: 1312,
    fit: 'cover',
  },
  {
    src: '/images/landing/shop/3.jpg',
    width: 2000,
    height: 1333,
    fit: 'cover',
  },
  {
    src: '/images/landing/box2.png',
    width: 2142,
    height: 1336,
    fit: 'cover',
  },
  {
    src: '/images/landing/logo.png',
    width: 500,
    height: 500,
    fit: 'contain',
  },
  {
    src: '/images/landing/progress/thing-4.png',
    width: 833,
    height: 833,
    fit: 'contain',
  },
] as const

const aboutCards = computed(() =>
  (text?.mediaBlocks || []).map((block, index) => ({
    ...block,
    image: blockImages[index]?.src || blockImages[0].src,
    imageWidth: blockImages[index]?.width || blockImages[0].width,
    imageHeight: blockImages[index]?.height || blockImages[0].height,
    imageFit: blockImages[index]?.fit || blockImages[0].fit,
    alt: block.title,
  })),
)

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.about'), item: '/about' },
])

useSeo().setPageSeo('about', {
  fallbackTitle: () => t('title.about'),
})
</script>

<template>
  <div class="page-base kratom-about-page">
    <section v-if="text?.hero" class="kratom-about-hero">
      <nuxt-img :src="heroImage" :alt="text.hero.title || t('title.about')" width="1920" height="1120" class="kratom-about-hero__bg" />
      <div class="container kratom-page-container kratom-about-hero__shell">
        <the-breadcrumbs :crumbs="breadcrumbs" />
        <div class="kratom-about-hero__content">
          <p class="kratom-about-hero__eyebrow">{{ text.hero.eyebrow }}</p>
          <h1 class="kratom-about-hero__title">{{ text.hero.title }}</h1>
          <div class="kratom-about-hero__lead">
            <p v-for="(paragraph, index) in text.hero.paragraphs" :key="index">{{ paragraph }}</p>
          </div>
        </div>
      </div>
    </section>

    <div class="container kratom-page-container kratom-about-page__content">
      <section v-if="text?.quote" class="kratom-about-quote">
        <p class="kratom-about-quote__eyebrow">{{ text.quote.title }}</p>
        <blockquote>{{ text.quote.text }}</blockquote>
      </section>

      <section v-if="aboutCards.length" class="kratom-about-grid">
        <article v-for="(block, index) in aboutCards" :key="block.title" class="kratom-about-card">
          <div class="kratom-about-card__media" :style="{ aspectRatio: `${block.imageWidth} / ${block.imageHeight}` }">
            <nuxt-img
              :src="block.image"
              :alt="block.alt"
              :width="block.imageWidth"
              :height="block.imageHeight"
              :style="{ objectFit: block.imageFit }"
            />
          </div>
          <div class="kratom-about-card__body">
            <p class="kratom-about-card__index">{{ String(index + 1).padStart(2, '0') }}</p>
            <h2 class="kratom-about-card__title">{{ block.title }}</h2>
            <div class="kratom-about-card__text">
              <p v-for="(paragraph, paragraphIndex) in block.body" :key="paragraphIndex">{{ paragraph }}</p>
            </div>
          </div>
        </article>
      </section>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-about-page {
  color: var(--kratom-page-text);
}

.kratom-about-page__content {
  padding-top: 32px;
  padding-bottom: 48px;
}

.kratom-about-hero {
  position: relative;
  overflow: clip;
  min-height: 620px;
  background: #071d19;

  &::after {
    content: '';
    position: absolute;
    inset: 0;
    background:
      linear-gradient(to bottom, rgba(7, 29, 25, 0) 0%, rgba(7, 29, 25, 0.28) 38%, #071d19 100%);
    pointer-events: none;
    z-index: 1;
  }
}

.kratom-about-hero__bg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center top;
}

.kratom-about-hero__shell {
  position: relative;
  z-index: 2;
  display: grid;
  align-content: space-between;
  min-height: 620px;
  padding-top: 24px;
  padding-bottom: 88px;
}

.kratom-about-hero__content {
  max-width: 880px;
  margin: auto;
  text-align: center;
}

.kratom-about-hero__eyebrow,
.kratom-about-quote__eyebrow,
.kratom-about-card__index {
  margin: 0 0 14px;
  color: #d8c098;
  text-transform: uppercase;
  letter-spacing: 0.16em;
  font-size: 12px;
  font-weight: 700;
}

.kratom-about-hero__title {
  margin: 0 0 24px;
  font-size: clamp(44px, 8vw, 92px);
  line-height: 0.96;
  color: #fff7ec;
}

.kratom-about-hero__lead {
  display: grid;
  gap: 16px;

  p {
    margin: 0;
    color: rgba(255, 247, 236, 0.84);
    font-size: 17px;
    line-height: 1.8;

    @include desktop {
      font-size: 19px;
    }
  }
}

.kratom-about-quote {
  margin: 0 auto 32px;
  max-width: 960px;
  padding: 32px;
  border: 1px solid rgba(255, 247, 236, 0.1);
  border-radius: 32px;
  background:
    radial-gradient(circle at top left, rgba(216, 192, 152, 0.16), transparent 30%),
    linear-gradient(180deg, rgba(20, 42, 36, 0.96), rgba(12, 27, 22, 0.98));
  box-shadow: 0 28px 70px rgba(0, 0, 0, 0.24);

  blockquote {
    margin: 0;
    color: #fff7ec;
    font-size: clamp(24px, 3vw, 34px);
    line-height: 1.45;
    font-family: var(--font-display);
    font-style: italic;
  }
}

.kratom-about-grid {
  display: grid;
  gap: 24px;
  grid-template-columns: 1fr;

  @include tablet {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  @include desktop {
    grid-template-columns: repeat(6, minmax(0, 1fr));
    align-items: start;
  }
}

.kratom-about-card {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border-radius: 30px;
  background: linear-gradient(180deg, rgba(19, 39, 33, 0.98), rgba(12, 26, 22, 0.98));
  border: 1px solid rgba(255, 247, 236, 0.08);
  box-shadow: 0 26px 60px rgba(0, 0, 0, 0.24);

  @include desktop {
    &:nth-child(5n + 1),
    &:nth-child(5n + 2),
    &:nth-child(5n + 3) {
      grid-column: span 2;
    }

    &:nth-child(5n + 4),
    &:nth-child(5n + 5) {
      grid-column: span 3;
    }
  }
}

.kratom-about-card__media {
  position: relative;
  overflow: hidden;
  min-height: 260px;

  &::after {
    content: '';
    position: absolute;
    inset: auto 0 0;
    height: 45%;
    background: linear-gradient(to top, rgba(7, 29, 25, 0.55), rgba(7, 29, 25, 0));
    pointer-events: none;
  }

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
}

.kratom-about-card__body {
  display: flex;
  flex: 1;
  flex-direction: column;
  padding: 24px 24px 28px;
}

.kratom-about-card__title {
  margin: 0 0 16px;
  color: #fff7ec;
  font-size: clamp(26px, 3vw, 34px);
  line-height: 1.08;
}

.kratom-about-card__text {
  display: grid;
  gap: 12px;

  p {
    margin: 0;
    color: rgba(255, 247, 236, 0.76);
    line-height: 1.75;
    font-size: 16px;
  }
}

:deep(.breadcrumbs) {
  padding-bottom: 40px;
}

:deep(.breadcrumbs .item),
:deep(.breadcrumbs .breadcrumbs__link) {
  color: rgba(255, 247, 236, 0.82);
}

:deep(.breadcrumbs .item:last-child) {
  color: rgba(216, 192, 152, 0.82);
}

@include mobile {
  .kratom-about-page__content {
    padding-top: 24px;
    padding-bottom: 32px;
  }

  .kratom-about-hero,
  .kratom-about-hero__shell {
    min-height: 560px;
  }

  .kratom-about-hero__shell {
    padding-bottom: 64px;
  }

  .kratom-about-quote,
  .kratom-about-card__body {
    padding-left: 20px;
    padding-right: 20px;
  }
}
</style>
