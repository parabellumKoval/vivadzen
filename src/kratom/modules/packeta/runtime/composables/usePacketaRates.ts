import { computed, ref } from 'vue'
import type { DestinationConfig, TariffMatrix, QuoteInput, QuoteOut, RateRow } from '#packeta-types'


/**
* Центральное место для тарификации.
*
* Ты фетчишь тарифы своей системой → вызываешь setTariffs(data).
* Дальше используешь quote(...) для расчёта цены.
*/
export const usePacketaRates = () => {
  // Хранилище тарифов в памяти (per app instance)
  const _tariffs = useState<TariffMatrix | null>('packeta:tariffs', () => null)


  /** Публичный setter, чтобы один раз подставить тарифы, например в layout/middleware */
  function setTariffs (matrix: TariffMatrix) {
    _tariffs.value = normalizeMatrix(matrix)
  }


  /** Удобно проверять готовность */
  const isReady = computed(() => !!_tariffs.value)


  /** Основной расчёт */
  function quote (input: QuoteInput): QuoteOut {
    const matrix = _tariffs.value
    if (!matrix) throw new Error('Packeta tariffs are not loaded')


    const origin = matrix[input.origin]
    if (!origin) throw new Error(`No tariffs for origin=${input.origin}`)


    const dest: DestinationConfig | undefined = origin[input.destination]
    if (!dest) throw new Error(`No tariffs for destination=${input.destination}`)


    const table: RateRow[] = input.method === 'pickup' ? dest.pickup : dest.home
    if (!Array.isArray(table) || table.length === 0) {
      throw new Error(`Empty tariff table for method=${input.method}`)
    }


    const maxAllowed = dest.max_weight_g ?? Infinity  

    if (input.weight_g > maxAllowed) {
      // За бизнес‑логикой: можно выбросить ошибку или вернуть «недоступно»
      return { amount: NaN, currency: dest.currency, note: `Weight exceeds limit (${maxAllowed} g)` }
    }


    const row = table.find(r => input.weight_g <= r.max_weight_g) || table[table.length - 1]
    let base = row.price


    // Бесплатно от суммы корзины
    if (dest.free_from_subtotal && input.subtotal >= dest.free_from_subtotal) {
      return {
        amount: 0,
        currency: dest.currency,
        breakdown: { base: base },
        note: `Free from ${dest.free_from_subtotal} ${dest.currency}`
      }
    }


    // Надбавки
    let cod = 0
    if (input.cod && dest.cod_surcharge) cod += dest.cod_surcharge


    let fuel = 0
    if (dest.fuel_surcharge_pct && dest.fuel_surcharge_pct > 0) {
      fuel = roundMoney(base * (dest.fuel_surcharge_pct / 100))
    }


    const amount = roundMoney(base + cod + fuel)
    return { amount, currency: dest.currency, breakdown: { base, cod: cod || undefined, fuel: fuel || undefined } }
  }


  return { isReady, setTariffs, quote }
}


// --- helpers ---
function roundMoney (n: number): number {
  // Округление до 2 знаков (при желании — делать банковское/валютно‑специфичное)
  return Math.round((n + Number.EPSILON) * 100) / 100
}


function normalizeMatrix (m: TariffMatrix): TariffMatrix {
  // Гарантируем сортировку таблиц по max_weight_g и минимальные проверки
  const out: TariffMatrix = {}
  for (const origin of Object.keys(m || {})) {
  out[origin] = out[origin] || {}
  for (const dest of Object.keys(m[origin] || {})) {
  const cfg = m[origin][dest]
  out[origin][dest] = {
  ...cfg,
  pickup: [...(cfg.pickup || [])].sort((a, b) => a.max_weight_g - b.max_weight_g),
  home: [...(cfg.home || [])].sort((a, b) => a.max_weight_g - b.max_weight_g)
  }
  }
  }
  return out
}