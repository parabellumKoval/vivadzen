import type { PudoPoint } from '#packeta-types'


/**
* Компонент‑обёртка над Packeta Widget v6.
* Вызывает окно выбора ПВЗ/почтомата и возвращает выбранную точку.
*/
export function usePacketa () {
const {$packetaLoad} = useNuxtApp()
const cfg = useRuntimeConfig().public.packeta || {}


const pickPudo = async (inElement?: HTMLElement | null): Promise<PudoPoint | null> => {
await $packetaLoad()
const w = window as any
if (!w.Packeta?.Widget) throw new Error('Packeta.Widget missing')


const opts: Record<string, any> = {
language: cfg.language || 'en',
country: cfg.defaultCountry || 'CZ'
}
if (Array.isArray(cfg.carriers) && cfg.carriers.length) {
opts.carriers = cfg.carriers
}


return new Promise((resolve) => {
const cb = (point: PudoPoint | null) => resolve(point || null)
if (inElement) {
w.Packeta.Widget.pickInElement(cfg.apiKey, cb, opts, inElement)
} else {
w.Packeta.Widget.pick(cfg.apiKey, cb, opts)
}
})
}


return { pickPudo }
}