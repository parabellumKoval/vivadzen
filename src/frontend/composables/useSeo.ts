export const useSeo = () => {
  const {t} = useI18n({useScope: 'global'})

  const setPageSeo = (title: String) => {
    useHead({
      title: t('seo.page.title', {title: title}),
      meta: [
        {
          name: 'description',
          content: t('seo.page.desc', {title: title})
        },
      ],
    })
  }

  return {
    setPageSeo
  }
}