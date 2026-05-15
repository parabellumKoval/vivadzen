<script setup lang="ts">
const { t, locale, locales } = useI18n()
const regionPath = useToLocalePath()
const route = useRoute()
const { user, isAuthenticated, init, avatar } = useAuth()
const isMenuOpen = ref(false)
const isDesktopCatalogAvailable = ref(false)
const isCatalogDropdownOpen = ref(false)
const accountAvatarFailed = ref(false)
const cartStore = useCartStore()
let desktopCatalogMediaQuery: MediaQueryList | null = null
let cleanupDesktopCatalogMediaQuery: (() => void) | null = null

const navItems = computed(() => [
  { to: '/catalog', label: t('title.catalog') },
  { to: '/delivery', label: t('title.delivery') },
  { to: '/payment', label: t('title.payment') },
  { to: '/age-verification', label: t('title.age_verification') },
  { to: '/reviews', label: t('title.reviews') },
  { to: '/about', label: t('title.about') },
  { to: '/contacts', label: t('title.contacts') },
])

const localeItems = computed(() => {
  return (Array.isArray(locales.value) ? locales.value : []).map((item: any) => ({
    code: item.code,
    shortName: item.shortName || String(item.code || '').toUpperCase(),
    href: item.code === 'cs' ? stripLocale(route.fullPath) : `/${item.code}${stripLocale(route.fullPath)}`,
  }))
})

const cartCount = computed(() => cartStore.cartLength)
const accountAvatarSrc = computed(() => {
  if (!isAuthenticated.value || accountAvatarFailed.value) {
    return null
  }

  return avatar.value || null
})
const accountLabel = computed(() => {
  if (isAuthenticated.value) {
    return user.value?.first_name || t('title.account.account')
  }

  return t('button.login')
})

watch(avatar, () => {
  accountAvatarFailed.value = false
})

const entheogensStoreUrl = computed(() => {
  const localeCode = String(locale.value || 'cs').toLowerCase()
  const localeSuffix = localeCode === 'cs' ? '' : `/${localeCode}`
  return `https://shop.vivadzen.com/cz${localeSuffix}`
})

function stripLocale(path: string) {
  const fullPath = String(path || '/')
  const pathname = fullPath.replace(/[?#].*$/, '') || '/'
  const suffix = fullPath.slice(pathname.length)
  const segments = pathname.split('/').filter(Boolean)
  if (segments[0] && ['cs', 'en', 'ru', 'uk'].includes(segments[0])) {
    const next = '/' + segments.slice(1).join('/')
    const normalized = next === '/' ? '/' : next.replace(/\/$/, '') || '/'
    return `${normalized}${suffix}`
  }
  return `${pathname || '/'}${suffix}`
}

function toggleMenu() {
  isMenuOpen.value = !isMenuOpen.value
}

function closeMenu() {
  isMenuOpen.value = false
}

function openCatalogDropdown() {
  if (!isDesktopCatalogAvailable.value) {
    return
  }

  isCatalogDropdownOpen.value = true
}

function closeCatalogDropdown() {
  isCatalogDropdownOpen.value = false
}

function handleCatalogFocusOut(event: FocusEvent) {
  const currentTarget = event.currentTarget
  const nextTarget = event.relatedTarget

  if (
    currentTarget instanceof HTMLElement
    && nextTarget instanceof Node
    && currentTarget.contains(nextTarget)
  ) {
    return
  }

  closeCatalogDropdown()
}

function syncDesktopCatalogAvailability() {
  isDesktopCatalogAvailable.value = Boolean(desktopCatalogMediaQuery?.matches)

  if (!isDesktopCatalogAvailable.value) {
    closeCatalogDropdown()
  }
}

function openCart() {
  useModal().open(resolveComponent('ModalCart'), null, null, {
    width: { min: 968, max: 968 },
  })
}

watch(() => route.fullPath, () => {
  closeMenu()
  closeCatalogDropdown()
})

onMounted(() => {
  init().catch(() => {})

  if (typeof window === 'undefined') {
    return
  }

  desktopCatalogMediaQuery = window.matchMedia('(min-width: 1024px)')
  syncDesktopCatalogAvailability()

  const listener = () => {
    syncDesktopCatalogAvailability()
  }

  if (typeof desktopCatalogMediaQuery.addEventListener === 'function') {
    desktopCatalogMediaQuery.addEventListener('change', listener)
    cleanupDesktopCatalogMediaQuery = () => desktopCatalogMediaQuery?.removeEventListener('change', listener)
    return
  }

  desktopCatalogMediaQuery.addListener(listener)
  cleanupDesktopCatalogMediaQuery = () => desktopCatalogMediaQuery?.removeListener(listener)
})

onBeforeUnmount(() => {
  if (cleanupDesktopCatalogMediaQuery) {
    cleanupDesktopCatalogMediaQuery()
  }
})
</script>

<template>
  <header class="site-header">
    <div class="site-header__promo-strip">
      <div class="site-header__promo-strip-inner container">
        <a
          :href="entheogensStoreUrl"
          class="site-header__promo-mobile-btn"
          target="_blank"
          rel="noopener noreferrer"
        >
          {{ t('kratom.header.entheogens_cta') }}
        </a>
      </div>
    </div>

    <div class="site-header__bar container">
      <NuxtLink :to="regionPath('/')" class="site-header__brand" @click="closeMenu">
        <span class="site-header__brand-mark">VD</span>
        <span class="site-header__brand-text">
          <strong>VivaDzen</strong>
          <span>{{ t('kratom.header.store') }}</span>
        </span>
      </NuxtLink>

      <div class="site-header__center">
        <nav class="site-header__nav" aria-label="Primary navigation">
          <div
            v-for="item in navItems"
            :key="item.to"
            class="site-header__nav-item"
            :class="{
              'site-header__nav-item--catalog': item.to === '/catalog',
              'is-open': item.to === '/catalog' && isCatalogDropdownOpen,
            }"
            @mouseenter="item.to === '/catalog' ? openCatalogDropdown() : undefined"
            @mouseleave="item.to === '/catalog' ? closeCatalogDropdown() : undefined"
            @focusin="item.to === '/catalog' ? openCatalogDropdown() : undefined"
            @focusout="item.to === '/catalog' ? handleCatalogFocusOut($event) : undefined"
          >
            <NuxtLink
              :to="regionPath(item.to)"
              class="site-header__nav-link"
            >
              {{ item.label }}
            </NuxtLink>

            <KratomCatalogDropdown
              v-if="item.to === '/catalog'"
              :enabled="isDesktopCatalogAvailable"
              @navigate="closeCatalogDropdown"
            />
          </div>
        </nav>

        <a
          :href="entheogensStoreUrl"
          class="site-header__promo-cta"
          target="_blank"
          rel="noopener noreferrer"
        >
          {{ t('kratom.header.entheogens_cta') }}
        </a>
      </div>

      <div class="site-header__actions">
        <div class="site-header__locales">
          <NuxtLink
            v-for="item in localeItems"
            :key="item.code"
            :to="item.href"
            class="site-header__locale"
            :class="{ 'is-active': item.code === locale }"
          >
            {{ item.shortName }}
          </NuxtLink>
        </div>

        <NuxtLink :to="regionPath(isAuthenticated ? '/account/orders' : '/auth/login')" class="site-header__account">
          <img
            v-if="accountAvatarSrc"
            :src="accountAvatarSrc"
            :alt="accountLabel"
            class="site-header__account-avatar"
            loading="lazy"
            @error="accountAvatarFailed = true"
          >
          <IconCSS v-else :name="isAuthenticated ? 'ph:user-circle-fill' : 'ph:sign-in'" class="site-header__account-icon" />
          <span>{{ accountLabel }}</span>
        </NuxtLink>

        <button type="button" class="site-header__cart" @click="openCart">
          <IconCSS name="ph:shopping-cart-fill" />
          <span class="site-header__cart-count">{{ cartCount }}</span>
        </button>

        <button type="button" class="site-header__menu-btn" @click="toggleMenu">
          <IconCSS :name="isMenuOpen ? 'ci:close-md' : 'ci:menu-alt-03'" />
        </button>
      </div>
    </div>

    <transition name="fade-in">
        <div v-if="isMenuOpen" class="site-header__mobile">
        <div class="site-header__mobile-card container">
          <NuxtLink
            :to="regionPath(isAuthenticated ? '/account/orders' : '/auth/login')"
            class="site-header__mobile-link"
            @click="closeMenu"
          >
            {{ accountLabel }}
          </NuxtLink>
          <NuxtLink
            v-for="item in navItems"
            :key="`${item.to}-mobile`"
            :to="regionPath(item.to)"
            class="site-header__mobile-link"
            @click="closeMenu"
          >
            {{ item.label }}
          </NuxtLink>
        </div>
      </div>
    </transition>
  </header>
</template>

<style scoped lang="scss">
.site-header {
  position: sticky;
  top: 0;
  z-index: 60;
  backdrop-filter: blur(18px);
  background: rgba(250, 246, 236, 0.82);
  border-bottom: 1px solid rgba(74, 91, 68, 0.12);
}

.site-header__promo-mobile-btn,
.site-header__promo-cta {
  text-decoration: none;
  transition:
    background-color 0.2s ease,
    color 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;

  &:hover {
    transform: translateY(-1px);
    }
}

.site-header__promo-strip {
  background: rgba(50, 58, 74, 0.96);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);

  @include desktop {
    display: none;
  }
}

.site-header__promo-strip-inner {
  min-height: 48px;
  padding-top: 7px;
  padding-bottom: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.site-header__promo-mobile-btn {
  min-height: 34px;
  padding: 8px 18px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  background: $color-orange;
  color: #fff;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.01em;
  line-height: 1;
  white-space: nowrap;
  text-align: center;
}

.site-header__bar {
  min-height: 78px;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 20px;
}

.site-header__center {
  display: none;

  @include desktop {
    min-width: 0;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto auto minmax(0, 1fr);
    align-items: center;
    column-gap: 18px;
  }
}

.site-header__brand {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
}

.site-header__brand-mark {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #35524a, #8a5a2b);
  color: #fff7ec;
  font-family: var(--font-display);
  font-size: 20px;
  font-weight: 700;
}

.site-header__brand-text {
  display: flex;
  flex-direction: column;
  gap: 3px;
  font-size: 12px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #5e6855;

  strong {
    font-size: 17px;
    letter-spacing: 0;
    text-transform: none;
    color: #1f2b1d;
  }
}

.site-header__nav {
  display: none;
  grid-column: 2;
  justify-content: center;
  gap: 26px;

  @include desktop {
    display: flex;
  }
}

.site-header__nav-item {
  position: relative;
  display: flex;
}

.site-header__nav-item--catalog {
  padding-bottom: 18px;
  margin-bottom: -18px;

  &.is-open {
    :deep(.catalog-dropdown) {
      opacity: 1;
      visibility: visible;
      pointer-events: auto;
      transform: translate(-50%, 0);
    }
  }
}

.site-header__nav-link,
.site-header__mobile-link,
.site-header__locale {
  text-decoration: none;
  color: #334130;
  transition: color 0.2s ease, opacity 0.2s ease;

  &:hover {
    color: #8a5a2b;
  }
}

.site-header__actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
}

.site-header__promo-cta {
  display: none;

  @include desktop {
    grid-column: 3;
    min-height: 44px;
    padding: 0 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: $color-orange;
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.02em;
    white-space: nowrap;
  }
}

.site-header__cart,
.site-header__menu-btn {
  width: 44px;
  height: 44px;
  border: 0;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #1f2b1d;
  color: #fff7ec;
  position: relative;
  cursor: pointer;
}

.site-header__account {
  min-height: 44px;
  max-width: 180px;
  padding: 0 14px;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  border-radius: 16px;
  text-decoration: none;
  background: rgba(255, 255, 255, 0.72);
  border: 1px solid rgba(74, 91, 68, 0.12);
  color: #1f2b1d;
  font-size: 14px;
  font-weight: 700;

  span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}

.site-header__account-avatar {
  width: 24px;
  height: 24px;
  border-radius: 999px;
  object-fit: cover;
  background: rgba(53, 82, 74, 0.12);
  flex: 0 0 auto;
}

.site-header__account-icon {
  font-size: 24px;
  flex: 0 0 auto;
}

.site-header__locales {
  display: none;
  align-items: center;
  gap: 6px;

  @include tablet {
    display: flex;
  }
}

.site-header__locale {
  min-width: 42px;
  height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.55);
  border: 1px solid rgba(74, 91, 68, 0.12);
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;

  &.is-active {
    background: #35524a;
    color: #fff7ec;
    border-color: #35524a;
  }
}

.site-header__menu-btn {
  @include desktop {
    display: none;
  }
}

.site-header__cart-count {
  position: absolute;
  top: 4px;
  right: 4px;
  min-width: 18px;
  height: 18px;
  border-radius: 999px;
  padding: 0 5px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #d6813f;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
}

.site-header__mobile {
  border-top: 1px solid rgba(74, 91, 68, 0.12);
  background: rgba(250, 246, 236, 0.96);

  @include desktop {
    display: none;
  }
}

.site-header__mobile-card {
  padding-top: 14px;
  padding-bottom: 18px;
  display: grid;
  gap: 12px;
}

.site-header__mobile-link {
  font-size: 18px;
  font-weight: 600;
}
</style>
