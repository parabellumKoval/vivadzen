export default defineI18nConfig(() => {
  return {
    fallbackLocale: 'ru',
    
    numberFormats: {
      uk: {
        currency: {
          style: 'currency', 
          currency: 'UAH',
          useGrouping: true,
          currencyDisplay: 'symbol'
        },
        distance: {
          style: 'unit',
          minimumFractionDigits: 2,
          unit: 'kilometer',
          unitDisplay: 'short'
        },
        minute: {
          style: 'unit', 
          useGrouping: false,
          unit: 'minute'
        },
      },
    },

    datetimeFormats: {
      uk: {
        short: {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        },
        long: {
          year: '2-digit',
          month: 'short',
          day: 'numeric',
          hour: 'numeric',
          minute: 'numeric'
        }
      },
    },
  }
})