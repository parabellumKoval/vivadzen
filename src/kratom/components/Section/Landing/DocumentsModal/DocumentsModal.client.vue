<script setup>
import { PDFViewer } from '@embedpdf/vue-pdf-viewer'

const { t } = useI18n()
const modal = useModal()

const payload = computed(() => modal.active?.data ?? {})

const title = computed(() => payload.value?.title ?? '')

const documents = computed(() => {
  const list = payload.value?.documents ?? []

  return list
    .filter((doc) => doc?.src)
    .map((doc, index) => ({
      title: doc.title || t('document_title', { index: index + 1 }),
      src: doc.src
    }))
})

const buildConfig = (src) => ({
  src,
  theme: {
    preference: 'light'
  }
})
</script>

<style src="./documents-modal.scss" lang="scss" scoped></style>
<i18n lang="yaml">
cs:
  document_title: "Dokument {index}"
  loading: "Načítání dokumentu..."
en:
  document_title: "Document {index}"
  loading: "Loading document..."
ru:
  document_title: "Документ {index}"
  loading: "Загружаем документ..."
uk:
  document_title: "Документ {index}"
  loading: "Завантажуємо документ..."
</i18n>

<template>
  <modal-wrapper :title="title" class="landing-documents-modal">
    <div class="documents-modal">
      <div v-for="(doc, index) in documents" :key="`${doc.src}-${index}`" class="documents-modal__doc">
        <div class="documents-modal__doc-title">{{ doc.title }}</div>
        <div class="documents-modal__doc-viewer">
          <ClientOnly>
            <PDFViewer :config="buildConfig(doc.src)" :style="{ width: '100%', height: '100%' }" />
            <template #fallback>
              <div class="documents-modal__doc-fallback">{{ t('loading') }}</div>
            </template>
          </ClientOnly>
        </div>
      </div>
    </div>
  </modal-wrapper>
</template>
