export default defineNuxtPlugin(() => {
let loadPromise: Promise<void> | null = null


function loadPacketaWidget(): Promise<void> {
if (typeof window === 'undefined') return Promise.resolve()
const w = window as any
if (w.Packeta?.Widget) return Promise.resolve()
if (loadPromise) return loadPromise


loadPromise = new Promise((resolve, reject) => {
const s = document.createElement('script')
s.src = 'https://widget.packeta.com/v6/www/js/library.js'
s.async = true
s.onload = () => resolve()
s.onerror = () => reject(new Error('Failed to load Packeta Widget'))
document.head.appendChild(s)
})


return loadPromise
}


return {
provide: {
packetaLoad: loadPacketaWidget
}
}
})