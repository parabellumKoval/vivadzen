import { usePacketaRates } from '~/modules/packeta/runtime/composables/usePacketaRates'

export default defineNuxtPlugin(async () => {
  const { setTariffs } = usePacketaRates()


  // 1) Получаешь тарифы из своего кеша/эндпоинта настроек
  // Пример: const tariffs = await $fetch('/api/_settings/shipping/packeta')
  // Здесь — просто заглушка формата TariffMatrix

  // const tariffs = {
  //   CZ: {
  //     CZ: { currency: 'CZK', pickup: [ { max_weight_g: 500, price: 69 }, { max_weight_g: 2000, price: 89 }, { max_weight_g: 5000, price: 109 }, { max_weight_g: 999999, price: 149 } ], home: [ { max_weight_g: 500, price: 99 }, { max_weight_g: 2000, price: 119 }, { max_weight_g: 5000, price: 149 }, { max_weight_g: 999999, price: 199 } ], cod_surcharge: 15, free_from_subtotal: 1500, vat_included: true, max_weight_g: 10000 },
  //     DE: { currency: 'EUR', pickup: [ { max_weight_g: 2000, price: 4.9 }, { max_weight_g: 5000, price: 6.9 }, { max_weight_g: 999999, price: 9.9 } ], home: [ { max_weight_g: 2000, price: 6.9 }, { max_weight_g: 5000, price: 8.9 }, { max_weight_g: 999999, price: 12.9 } ], cod_surcharge: 0.5, free_from_subtotal: 60, vat_included: true, max_weight_g: 15000 },
  //     ES: { currency: 'EUR', pickup: [ { max_weight_g: 2000, price: 6.9 }, { max_weight_g: 5000, price: 9.9 }, { max_weight_g: 999999, price: 14.9 } ], home: [ { max_weight_g: 2000, price: 8.9 }, { max_weight_g: 5000, price: 12.9 }, { max_weight_g: 999999, price: 18.9 } ], cod_surcharge: 1.0, free_from_subtotal: 70, vat_included: true, max_weight_g: 15000 }
  //   }
  // }

  const tariffs = {
    CZ: {
      CZ: {
        currency: 'CZK',
        pickup: [
          { max_weight_g: 5000, price: 62 },      // стандартная посылка в пункт выдачи
          { max_weight_g: 15000, price: 120 }     // крупногабаритная
        ],
        home: [
          { max_weight_g: 5000, price: 89 },      // стандартная доставка домой
          { max_weight_g: 15000, price: 130 }     // крупногабаритная
        ],
        cod_surcharge: 21,
        free_from_subtotal: 1500,
        vat_included: false, // в прайс-листе указано "цены без НДС"
        max_weight_g: 15000
      },

      DE: {
        currency: 'CZK',
        pickup: [
          { max_weight_g: 1000, price: 140 },
          { max_weight_g: 2000, price: 140 },
          { max_weight_g: 5000, price: 150 },
          { max_weight_g: 10000, price: 210 },
          { max_weight_g: 15000, price: 260 }
        ],
        home: [
          { max_weight_g: 1000, price: 170 },
          { max_weight_g: 2000, price: 180 },
          { max_weight_g: 5000, price: 190 },
          { max_weight_g: 10000, price: 200 },
          { max_weight_g: 15000, price: 250 }
        ],
        cod_surcharge: 180,
        free_from_subtotal: null,
        vat_included: false,
        max_weight_g: 15000
      },

      ES: {
        currency: 'CZK',
        pickup: [
          { max_weight_g: 1000, price: 200 },
          { max_weight_g: 2000, price: 220 },
          { max_weight_g: 5000, price: 250 },
          { max_weight_g: 10000, price: 300 },
          { max_weight_g: 15000, price: 340 }
        ],
        home: [
          { max_weight_g: 1000, price: 210 },
          { max_weight_g: 2000, price: 230 },
          { max_weight_g: 5000, price: 260 },
          { max_weight_g: 10000, price: 310 },
          { max_weight_g: 15000, price: 350 }
        ],
        cod_surcharge: 50,
        free_from_subtotal: null,
        vat_included: false,
        max_weight_g: 15000
      }
    }
}



  setTariffs(tariffs as any)
})