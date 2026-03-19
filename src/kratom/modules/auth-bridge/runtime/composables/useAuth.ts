type User = Record<string, any>
type FetchInit = {
  method?: string
  body?: any
  headers?: Record<string, string>
  query?: Record<string, any>
}

function extractUser(payload: any): User | null {
  if (!payload || typeof payload !== 'object') return payload ?? null
  if ('user' in payload && payload.user) return payload.user as User
  if ('data' in payload && payload.data && typeof payload.data === 'object') {
    const { data } = payload as any
    if ('user' in data && data.user) return data.user as User
    return data as User
  }
  return payload as User
}

export function useAuth() {
  const runtime = useRuntimeConfig()
  const opts = runtime.public.authBridge || {}
  const endpoints = opts.endpoints || {}
  const tokenCookieName = opts.tokenCookieName || 'auth_token'
  const orderableType = opts.order?.orderableType || 'profile_user'

  const token = useCookie<string | null>(tokenCookieName, {
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production',
    path: '/',
  })

  const user = useState<User | null>('auth_user', () => null)
  const pending = useState<boolean>('auth_pending', () => false)
  const initialized = useState<boolean>('auth_initialized', () => false)
  const mePromise = useState<Promise<User | null> | null>('auth_me_promise', () => null)

  const withAuthHeaders = (headers: Record<string, string> = {}) => {
    const next: Record<string, string> = { Accept: 'application/json', ...headers }
    const xsrf = process.client ? useCookie('XSRF-TOKEN').value : null
    
    if (token.value && !next.Authorization) {
      next.Authorization = `Bearer ${token.value}`
    }

    if (xsrf && !next['X-XSRF-TOKEN']) {
      next['X-XSRF-TOKEN'] = xsrf
    }

    return next
  }

  const fetcher = async <T = any>(url?: string, init: FetchInit = {}): Promise<T> => {
    if (!url) throw new Error('Auth bridge endpoint is not configured')
    const { $api } = useNuxtApp() as any
    const headers = withAuthHeaders(init.headers)
    const request = { ...init, headers }

    if ($api) {
      return $api<T>(url, request)
    }

    const base = (runtime.public as any).apiBase || ''
    return $fetch<T>(base + url, {
      credentials: 'include',
      ...request,
    })
  }

  const setSession = (nextUser: User | null, nextToken?: string | null) => {
    if (typeof nextToken !== 'undefined') {
      token.value = nextToken ?? null
    }
    user.value = nextUser
  }

  const ensureInit = async () => {
    if (initialized.value) return user.value
    initialized.value = true
    if (!token.value) {
      setSession(null, null)
      return null
    }
    return me(true)
  }

  async function referrals() {
    const response = await fetcher(endpoints.referrals, { method: 'GET' })
    return response;
  }

  async function wallet() {
    const response = await fetcher(endpoints.wallet, { method: 'GET' })
    return response;
  }

  async function me(force = false) {
    if (!token.value) {
      setSession(null, null)
      initialized.value = true
      return null
    }

    if (!force && user.value) {
      initialized.value = true
      return user.value
    }

    if (!force && mePromise.value) {
      return mePromise.value
    }

    const run = (async () => {
      pending.value = true
      try {
        const response = await fetcher<any>(endpoints.me)
        const normalized = extractUser(response)
        user.value = normalized
        return normalized
      } catch (error) {
        setSession(null, null)
        throw error
      } finally {
        pending.value = false
        initialized.value = true
      }
    })()

    if (!force) {
      mePromise.value = run
      try {
        return await run
      } finally {
        mePromise.value = null
      }
    }

    return run
  }

  async function login(email: string, password: string) {
    pending.value = true
    try {
      const response = await fetcher<{ user: User; token: string }>(endpoints.login, {
        method: 'POST',
        body: { email, password },
      })
      const nextUser = extractUser(response)
      setSession(nextUser, (response as any)?.token)
      return nextUser
    } finally {
      pending.value = false
    }
  }

  async function register(payload: { name?: string; email: string; password: string }) {
    pending.value = true
    try {
      const response = await fetcher<{ user: User; token: string }>(endpoints.register, {
        method: 'POST',
        body: payload,
      })
      const nextUser = extractUser(response)
      const requiresEmailVerification = !nextUser?.email_verified_at
      
      // Only set session if email is verified or verification is not required
      if (!requiresEmailVerification) {
        setSession(nextUser, (response as any)?.token)
      }
      
      return {
        user: nextUser,
        requiresEmailVerification
      }
    } finally {
      pending.value = false
    }
  }

  async function logout() {
    try {
      await fetcher(endpoints.logout, { method: 'POST' })
    } catch {
      // ignore network/API errors on logout
    }
    setSession(null, null)
  }

  async function forgotPassword(email: string) {
    return fetcher(endpoints.forgot, { method: 'POST', body: { email } })
  }

  async function resetPassword(tokenStr: string, email: string, password: string, password_confirmation: string) {
    return fetcher(endpoints.reset, {
      method: 'POST',
      body: { token: tokenStr, email, password, password_confirmation },
    })
  }

  async function changePassword(payload: { current_password: string; password: string; password_confirmation: string }) {
    const response = await fetcher(endpoints.changePassword, {
      method: 'POST',
      body: payload,
    })
    await me(true)
    return response
  }

  async function updateProfile(payload: Record<string, any>) {
    const response = await fetcher(endpoints.profileUpdate, {
      method: 'POST',
      body: payload,
    })
    await me(true)
    return response
  }

  async function requestEmailChange(payload: Record<string, any>) {
    return fetcher(endpoints.profileEmailChange, {
      method: 'POST',
      body: payload,
    })
  }

  async function resendVerificationToLoggedIn() {
    return fetcher(endpoints.resendLoggedIn, { method: 'POST' })
  }

  async function resendVerificationByEmail(email: string) {
    return fetcher(endpoints.resendByEmail, { method: 'POST', body: { email } })
  }

  async function socialSignIn(provider: 'google' | 'facebook', opts?: { redirect_uri?: string }) {
    if (typeof window === 'undefined') return
    const urlTemplate = endpoints.socialUrl as string

    if (!urlTemplate) throw new Error('Social login endpoint is not configured')
    
    // Replace :provider in the template
    const baseUrl = urlTemplate.replace(':provider', provider)

    const finalUrl = opts?.redirect_uri ? `${baseUrl}?redirect_uri=${encodeURIComponent(opts.redirect_uri)}` : baseUrl
    const { url: redirect } = await fetcher<{ url: string }>(finalUrl, { method: 'GET' })
    if (redirect) window.location.href = redirect
  }

  async function finishWithToken(tokenFromQuery: string) {
    if (!tokenFromQuery) return
    token.value = tokenFromQuery
    await me(true)
  }

  // COMPUTEDS
  const isAuthenticated = computed(() => Boolean(user.value && token.value))

  const isEmailVerified = computed(() => Boolean(user.value?.email_verified_at))

  const displayName = computed(() => {
    if (!user.value) return ''
    const candidate =
      user.value.name ||
      [user.value.first_name, user.value.last_name].filter(Boolean).join(' ').trim() ||
      user.value.fullname
    return candidate || ''
  })
  const normalizeAvatarSource = (value: any): string | null => {
    if (typeof value !== 'string') {
      return null
    }
    const trimmed = value.trim()
    return trimmed.length ? trimmed : null
  }

  const avatar = computed(() => {
    const candidates = [
      normalizeAvatarSource(user.value?.photo),
      normalizeAvatarSource(user.value?.avatar),
      normalizeAvatarSource(user.value?.avatar_url),
    ]
    return candidates.find((value): value is string => Boolean(value)) ?? null
  })

  const orderable = computed(() => {
    if (!user.value?.id) {
      return { orderable_id: null, orderable_type: null }
    }
    return { orderable_id: user.value.id, orderable_type: orderableType }
  })

  if (!initialized.value && token.value) {
    ensureInit()
  } else if (!initialized.value && !token.value) {
    initialized.value = true
  }

  return {
    token,
    user,
    pending,
    isAuthenticated,
    isEmailVerified,
    displayName,
    avatar,
    orderable,
    init: ensureInit,
    me,
    referrals,
    wallet,
    login,
    register,
    logout,
    forgotPassword,
    resetPassword,
    changePassword,
    updateProfile,
    requestEmailChange,
    resendVerificationToLoggedIn,
    resendVerificationByEmail,
    socialSignIn,
    finishWithToken,
  }
}
