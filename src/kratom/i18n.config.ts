export default defineI18nConfig(() => {
  const currencyFormats = {
    style: 'currency',
    currency: 'CZK',
    currencyDisplay: 'narrowSymbol',
    useGrouping: true,
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }

  const decimalFormats = {
    style: 'decimal',
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }

  const shortDate = {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }

  const longDate = {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }

  return {
    fallbackLocale: 'cs',
    numberFormats: {
      cs: { currency: currencyFormats, cur: decimalFormats },
      en: { currency: currencyFormats, cur: decimalFormats },
      ru: { currency: currencyFormats, cur: decimalFormats },
      uk: { currency: currencyFormats, cur: decimalFormats },
    },
    datetimeFormats: {
      cs: { short: shortDate, long: longDate },
      en: { short: shortDate, long: longDate },
      ru: { short: shortDate, long: longDate },
      uk: { short: shortDate, long: longDate },
    },
  }
})
