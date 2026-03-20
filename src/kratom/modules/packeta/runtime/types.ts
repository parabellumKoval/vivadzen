export type PudoPoint = {
id: string
name: string
city?: string
street?: string
zip?: string
country?: string
latitude?: number
longitude?: number
carrierId?: string
openingHours?: string
[k: string]: any
}


export type RateRow = {
/** верхняя граница веса в граммах */
max_weight_g: number
/** цена (в валюте destination-конфига) */
price: number
}


export type DestinationConfig = {
currency: string
pickup: RateRow[]
home: RateRow[]
/** доплата за наложенный платёж (фикс, в валюте) */
cod_surcharge?: number
/** надбавка «топливная», в процентах, напр. 7.5 => +7.5% */
fuel_surcharge_pct?: number
/** бесплатная доставка от суммы корзины */
free_from_subtotal?: number
/** ограничения */
max_weight_g?: number
vat_included?: boolean
}


export type TariffMatrix = {
/** origin ISO2 (обычно одна страна, напр. CZ) => destinations */
[originIso2: string]: {
[destinationIso2: string]: DestinationConfig
}
}


export type QuoteInput = {
origin: string // ISO2 origin
destination: string // ISO2 destination
method: 'pickup' | 'home'
weight_g: number
subtotal: number
cod: boolean
}


export type QuoteOut = {
amount: number
currency: string
breakdown?: {
base: number
cod?: number
fuel?: number
}
note?: string
}