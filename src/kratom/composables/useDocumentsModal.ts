/**
 * Composable для управления модальными окнами с документами
 */

export const useDocumentsModal = () => {
  const { t } = useI18n()
  const modal = useModal()

  const documentsModal = defineAsyncComponent(() =>
    import('~/components/Section/Landing/DocumentsModal/DocumentsModal.client.vue')
  )

  /**
   * PDF файлы для сертификатов (безопасность - из TimelineSection)
   */
  const certificateDocuments = computed(() => [
    { title: `${t('modal_titles.certificates')} 1`, src: '/pdf/green.pdf' },
    { title: `${t('modal_titles.certificates')} 1`, src: '/pdf/green2.pdf' },
    { title: `${t('modal_titles.certificates')} 1`, src: '/pdf/green3.PDF' },

    { title: `${t('modal_titles.certificates')} 2`, src: '/pdf/white.pdf' },
    { title: `${t('modal_titles.certificates')} 2`, src: '/pdf/white2.pdf' },

    { title: `${t('modal_titles.certificates')} 3`, src: '/pdf/red.PDF' },
    { title: `${t('modal_titles.certificates')} 3`, src: '/pdf/red2.pdf' }
  ])

  /**
   * PDF файлы для лицензий (правовые документы - из LegalMarket)
   */
  const licenseDocuments = computed(() => [
    { title: `${t('modal_titles.licenses')} 1`, src: '/pdf/vivadzen.pdf' },
  ])

  /**
   * Открыть модалку с сертификатами
   */
  const openCertificatesModal = () => {
    modal.open(
      documentsModal,
      {
        title: t('modal_titles.certificates'),
        documents: certificateDocuments.value
      },
      null,
      {
        width: { min: 320, max: 1100 },
        height: { min: 520, max: 900 }
      }
    )
  }

  /**
   * Открыть модалку с лицензиями
   */
  const openLicenseModal = () => {
    modal.open(
      documentsModal,
      {
        title: t('modal_titles.licenses'),
        documents: licenseDocuments.value
      },
      null,
      {
        width: { min: 320, max: 1100 },
        height: { min: 520, max: 900 }
      }
    )
  }

  return {
    certificateDocuments,
    licenseDocuments,
    openCertificatesModal,
    openLicenseModal
  }
}
