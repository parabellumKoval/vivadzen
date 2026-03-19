import { computed } from 'vue'

interface ReferralEntry {
  code: string
  expiresAt: number | null
}

const DAY_MS = 24 * 60 * 60 * 1000

function ensureObject(value: string | null | undefined): ReferralEntry | null {
  if (!value) return null
  try {
    const parsed = JSON.parse(value)
    if (parsed && typeof parsed === 'object') {
      const code = typeof parsed.code === 'string' ? parsed.code : ''
      const expiresAt =
        typeof parsed.expiresAt === 'number' && Number.isFinite(parsed.expiresAt) ? parsed.expiresAt : null
      if (code) {
        return { code, expiresAt }
      }
    }
    return null
  } catch {
    return null
  }
}

export function useReferralBridge() {
  const runtime = useRuntimeConfig()
  const opts = runtime.public?.authBridge?.referrals || {}
  const cookieName = opts.cookieName || 'referral_code'

  const raw = useCookie<ReferralEntry | null>(cookieName, {
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production',
    path: '/',
    default: () => null,
    encode(value) {
      if (!value) return ''
      return JSON.stringify(value)
    },
    decode(value) {
      return ensureObject(value)
    },
  })

  const purgeIfExpired = () => {
    const entry = raw.value
    if (entry?.expiresAt && entry.expiresAt <= Date.now()) {
      raw.value = null
    }
  }

  const code = computed(() => {
    purgeIfExpired()
    return raw.value?.code ?? null
  })

  const expiresAt = computed(() => {
    purgeIfExpired()
    return raw.value?.expiresAt ?? null
  })

  const isActive = computed(() => Boolean(code.value))

  const setReferral = (nextCode: string, ttlDays: number) => {
    if (!nextCode) {
      raw.value = null
      return
    }
    const parsedTtl = Number(ttlDays)
    const ttlMs = Number.isFinite(parsedTtl) && parsedTtl > 0 ? parsedTtl * DAY_MS : 0
    const expiresAt = ttlMs > 0 ? Date.now() + ttlMs : null
    raw.value = { code: nextCode, expiresAt }
  }

  const clearReferral = () => {
    raw.value = null
  }

  const isExpired = () => {
    purgeIfExpired()
    const entry = raw.value
    if (!entry?.code) return true
    if (entry.expiresAt == null) return false
    return entry.expiresAt <= Date.now()
  }

  return {
    raw,
    code,
    expiresAt,
    isActive,
    setReferral,
    clearReferral,
    isExpired,
  }
}

