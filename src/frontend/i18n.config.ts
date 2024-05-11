export default defineI18nConfig(() => {
  return {
    fallbackLocale: 'ru',
    
    numberFormats: {
      uk: {
        currency: {
          style: 'currency', 
          currency: 'UAH',
          currencyDisplay: 'narrowSymbol',
          useGrouping: true
          // notation: 'compact'
          // unitDisplay: 'symbol'
        },
        cur: {
          style: 'decimal',
          minimumFractionDigits: 2
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