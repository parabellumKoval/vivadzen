<script setup lang="ts">
import { PARTNER_STORES_HASH } from '../../composables/usePartnerStores'

const { t } = useI18n()
const route = useRoute()

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.contacts'), item: '/contacts' },
])

useSeo().setPageSeo('contacts', {
  fallbackTitle: () => t('title.contacts'),
})

const scrollToPartners = async () => {
  if (process.server || route.hash !== `#${PARTNER_STORES_HASH}`) return

  await nextTick()

  const target = document.getElementById(PARTNER_STORES_HASH)
  if (!target) return

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  target.scrollIntoView({
    behavior: prefersReducedMotion ? 'auto' : 'smooth',
    block: 'start',
  })
}

onMounted(() => {
  scrollToPartners()
})

watch(() => route.hash, () => {
  scrollToPartners()
})
</script>

<template>
  <div class="page-base kratom-content-page">
    <div class="container kratom-page-container kratom-content-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />
    </div>

    <div style="margin-top: -32px">
      <section-landing-contacts show-partners />
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-content-shell { padding-top: 24px; margin-bottom: 24px; }
</style>
