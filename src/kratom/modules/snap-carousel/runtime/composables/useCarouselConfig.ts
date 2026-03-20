type GapValue = number | string | Record<string, number | string>

export function useCarouselConfig () {
  const rc = useRuntimeConfig()
  const cfg = (rc.public as any).snapCarousel || {}
  return cfg as {
    screens: Record<string, number>
    defaults: {
      mode: 'page' | 'item'
      loop: boolean
      gap: GapValue
      showArrows: boolean
      showDots: boolean
      snapStop: 'normal' | 'always'
      itemsPerPage: Record<string, number>
    }
  }
}

/** Универсальный резолвер responsive-значений по текущей ширине */
export function resolveResponsive<T extends number | string>(
  width: number,
  screens: Record<string, number>,
  mapOrScalar: T | Record<string, T>
): T {
  if (typeof mapOrScalar !== 'object' || mapOrScalar == null) {
    return mapOrScalar as T
  }
  const map = mapOrScalar as Record<string, T>
  const entries = Object.entries(screens).sort((a, b) => a[1] - b[1])
  let pickedKey = entries[0]?.[0] || 'xs'
  for (const [k, min] of entries) if (width >= min) pickedKey = k

  if (map[pickedKey] != null) return map[pickedKey]
  for (let i = entries.findIndex(e => e[0] === pickedKey); i >= 0; i--) {
    const k = entries[i][0]
    if (map[k] != null) return map[k]
  }
  const any = Object.values(map)[0]
  return any as T
}

export function resolveItemsPerPage(
  width: number,
  screens: Record<string, number>,
  map: Record<string, number>
) {
  return resolveResponsive<number>(width, screens, map)
}

export function resolveGap(
  width: number,
  screens: Record<string, number>,
  gap: number | string | Record<string, number | string>
): string {
  const val = resolveResponsive<number | string>(width, screens, gap as any)
  return typeof val === 'number' ? `${val}px` : String(val)
}
