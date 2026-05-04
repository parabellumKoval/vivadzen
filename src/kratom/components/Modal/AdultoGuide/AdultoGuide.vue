<script setup lang="ts">
const { t, tm, rt } = useI18n()

const translateText = (value: unknown) => {
  if (typeof value === 'string') {
    return value
  }

  return value ? rt(value as Parameters<typeof rt>[0]) : ''
}

const translateList = (value: unknown) => {
  return Array.isArray(value) ? value.map((item) => translateText(item)) : []
}

const sectionImagesByNumber: Record<string, Array<{ src: string, width: number, height: number }>> = {
  '1': [
    { src: '/images/adulto/1.jpg', width: 1280, height: 745 },
  ],
  '2': [
    { src: '/images/adulto/2.jpg', width: 1166, height: 1264 },
  ],
  '3': [
    { src: '/images/adulto/3.jpg', width: 1280, height: 727 },
    { src: '/images/adulto/4.jpg', width: 1280, height: 1231 },
  ],
  '4': [
    { src: '/images/adulto/5.jpg', width: 588, height: 881 },
    { src: '/images/adulto/6.jpg', width: 515, height: 1000 },
  ],
}

const intro = computed(() => {
  const value = tm('intro') as Record<string, unknown>

  return {
    paragraphs: translateList(value.paragraphs),
    alert: translateText(value.alert),
  }
})

const sections = computed(() => {
  const value = tm('sections')

  return Array.isArray(value)
    ? value.map((section) => {
      const sectionValue = section as Record<string, unknown>
      const sectionNumber = translateText(sectionValue.number)

      return {
        number: sectionNumber,
        title: translateText(sectionValue.title),
        lead: translateText(sectionValue.lead),
        steps: translateList(sectionValue.steps),
        note: translateText(sectionValue.note),
        tips: translateList(sectionValue.tips),
        images: sectionImagesByNumber[sectionNumber] || [],
      }
    })
    : []
})

const important = computed(() => {
  const value = tm('important') as Record<string, unknown>

  return {
    title: translateText(value.title),
    paragraphs: translateList(value.paragraphs),
    steps: translateList(value.steps),
    footer: translateText(value.footer),
  }
})

const alternative = computed(() => {
  const value = tm('alternative') as Record<string, unknown>

  return {
    title: translateText(value.title),
    text: translateText(value.text),
    bullets: translateList(value.bullets),
  }
})
</script>

<style src="./adulto-guide.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('kratom.product.adulto_modal_title')" class="adulto-guide-modal">
    <div class="adulto-guide">
      <header class="adulto-guide__intro">
        <p v-for="(paragraph, index) in intro.paragraphs || []" :key="`intro-${index}`">
          {{ paragraph }}
        </p>
        <div class="adulto-guide__alert">{{ intro.alert }}</div>
      </header>

      <section
        v-for="section in sections"
        :key="section.number"
        class="adulto-guide__section"
      >
        <div class="adulto-guide__section-head">
          <span>{{ section.number }}</span>
          <h2>{{ section.title }}</h2>
        </div>
        <p v-if="section.lead" class="adulto-guide__lead">{{ section.lead }}</p>
        <ol class="adulto-guide__steps">
          <li v-for="(step, index) in section.steps || []" :key="`${section.number}-${index}`" v-html="step"></li>
        </ol>
        <div
          v-if="section.images.length"
          class="adulto-guide__images"
          :class="{ 'adulto-guide__images--stacked': section.number === '3' }"
        >
          <nuxt-img
            v-for="(image, index) in section.images"
            :key="image.src"
            :src="image.src"
            :alt="`${section.title} ${index + 1}`"
            :title="section.title"
            :width="image.width"
            :height="image.height"
            sizes="mobile:88vw tablet:360px desktop:800px"
            format="webp"
            quality="85"
            class="adulto-guide__image"
          />
        </div>
        <p v-if="section.note" class="adulto-guide__note">{{ section.note }}</p>
        <div v-if="section.tips?.length" class="adulto-guide__tip-list">
          <p v-for="(tip, index) in section.tips" :key="`${section.number}-tip-${index}`">{{ tip }}</p>
        </div>
      </section>

      <section class="adulto-guide__section adulto-guide__section--accent">
        <h2>{{ important.title }}</h2>
        <p v-for="(paragraph, index) in important.paragraphs || []" :key="`important-${index}`">
          {{ paragraph }}
        </p>
        <ol class="adulto-guide__steps adulto-guide__steps--compact">
          <li v-for="(step, index) in important.steps || []" :key="`important-step-${index}`">{{ step }}</li>
        </ol>
        <p>{{ important.footer }}</p>
      </section>

      <section class="adulto-guide__section">
        <h2>{{ alternative.title }}</h2>
        <p>{{ alternative.text }}</p>
        <ul class="adulto-guide__bullets">
          <li v-for="(bullet, index) in alternative.bullets || []" :key="`alternative-${index}`">
            {{ bullet }}
          </li>
        </ul>
      </section>
    </div>
  </modal-wrapper>
</template>
