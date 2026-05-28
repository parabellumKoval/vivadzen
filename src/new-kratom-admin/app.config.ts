export default defineAppConfig({
  adminI18n: {
    uiLocale: 'ru',
    dateTimeLocale: 'ru-RU',
    primaryContentLocale: 'cs',
    previewContentLocale: 'ru',
    contentLocales: [
      { code: 'cs', native: 'CS', label: 'Чешский' },
      { code: 'en', native: 'EN', label: 'Английский' },
      { code: 'ru', native: 'RU', label: 'Русский' },
      { code: 'uk', native: 'UK', label: 'Украинский' },
    ],
  },
})
