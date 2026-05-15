<script setup>
const { t, locale, locales } = useI18n()
const regionStore = useRegion()
const contacts = useContacts()
const regionPath = useToLocalePath()
const route = useRoute()
const { isAuthenticated, avatar } = useAuth()

const HEADER_SCROLL_TRIGGER = 100

const headerRef = ref(null)
const navContainerRef = ref(null)
const menuRef = ref(null)
const langRef = ref(null)
const moreRef = ref(null)
const navMeasureRefs = ref([])
const navMoreMeasureRef = ref(null)

const isLangOpen = ref(false)
const isMoreOpen = ref(false)
const isMenuOpen = ref(false)
const isScrolled = ref(false)
const isDesktopCatalogAvailable = ref(false)
const isCatalogDropdownEnabled = ref(false)
const accountAvatarFailed = ref(false)
let desktopCatalogMediaQuery = null
let cleanupDesktopCatalogMediaQuery = null

const currentRegionCode = computed(() => regionStore.region.value)
const allowedLocales = computed(() => regionStore.getLocalesForRegion(currentRegionCode.value))
const getLocaleLink = (code) => regionStore.currentUrl(null, code)

const languageLinks = computed(() => {
  const items = Array.isArray(locales.value) ? locales.value : []
  const allowed = new Set(allowedLocales.value || [])

  return items
    .filter((item) => allowed.has(String(item?.code || '').toLowerCase()))
    .map((item) => ({
      code: item.code,
      shortName: item.shortName || item.code?.toUpperCase?.() || item.code,
      name: item.name || item.code,
      link: getLocaleLink(item.code),
      isActive: item.code === locale.value
    }))
})

const activeLanguage = computed(() => {
  return languageLinks.value.find((item) => item.isActive) || languageLinks.value[0]
})

const cartCount = computed(() => useCartStore().cartLength)
const homeLink = computed(() => regionPath('/'))
const accountLink = computed(() => regionPath(isAuthenticated.value ? '/account/orders' : '/auth/login'))
const accountLabel = computed(() => isAuthenticated.value ? t('title.account.account') : t('button.login'))
const accountAvatarSrc = computed(() => {
  if (!isAuthenticated.value || accountAvatarFailed.value) {
    return null
  }

  return avatar.value || null
})
const entheogensStoreUrl = computed(() => {
  const localeCode = String(locale.value || 'cs').toLowerCase()
  const localeSuffix = localeCode === 'cs' ? '' : `/${localeCode}`
  return `https://shop.vivadzen.com/cz${localeSuffix}`
})

watch(avatar, () => {
  accountAvatarFailed.value = false
})

const navItems = computed(() => [
  { id: 'catalog', to: '/catalog', title: t('title.catalog'), priority: 40 },
  { id: 'delivery', to: '/delivery', title: t('title.delivery'), priority: 48 },
  { id: 'payment', to: '/payment', title: t('title.payment'), priority: 46 },
  { id: 'age-verification', to: '/age-verification', title: t('title.age_verification'), priority: 42 },
  { id: 'reviews', to: '/reviews', title: t('title.reviews'), priority: 25 },
  { id: 'about', to: '/about', title: t('title.about'), priority: 20 },
  { id: 'contacts', to: '/contacts', title: t('title.contacts'), priority: 18 }
])

const mobileNavItems = computed(() => [
  { id: 'home', to: '/', title: t('nav.home') },
  ...navItems.value
])

const visibleNav = ref(navItems.value)
const overflowNav = ref([])

const stripLocalePrefix = (path) => {
  const normalizedPath = String(path || '/').replace(/\/+$/, '') || '/'
  const segments = normalizedPath.split('/').filter(Boolean)
  const localesList = ['cs', 'en', 'ru', 'uk']
  if (segments[0] && localesList.includes(segments[0])) {
    const next = '/' + segments.slice(1).join('/')
    return next === '/' ? '/' : next.replace(/\/+$/, '') || '/'
  }
  return normalizedPath
}

const isHomeRoute = computed(() => stripLocalePrefix(route.path) === '/')
const isAccountRoute = computed(() => stripLocalePrefix(route.path).startsWith('/account'))
const showPromoStrip = computed(() => {
  const normalizedPath = stripLocalePrefix(route.path)
  return normalizedPath !== '/checkout' && !isAccountRoute.value
})
const isHeaderScrolled = computed(() => !isHomeRoute.value || isScrolled.value)

const resetMeasureRefs = () => {
  navMeasureRefs.value = Array(navItems.value.length).fill(null)
}

const setMeasureRef = (el, index) => {
  if (typeof index !== 'number') return
  navMeasureRefs.value[index] = el || null
}

const updateNavLayout = () => {
  if (process.server) return
  nextTick(() => {
    const container = navContainerRef.value
    if (!container) return

    const items = navItems.value
    const widths = items.map((_, index) => {
      const el = navMeasureRefs.value[index]
      return el ? el.getBoundingClientRect().width : 0
    })

    const styles = window.getComputedStyle(container)
    const gap = Number.parseFloat(styles.columnGap || styles.gap || '0') || 0
    const paddingLeft = Number.parseFloat(styles.paddingLeft || '0') || 0
    const paddingRight = Number.parseFloat(styles.paddingRight || '0') || 0
    const availableWidth = Math.max(0, container.clientWidth - paddingLeft - paddingRight)
    const moreWidth = navMoreMeasureRef.value
      ? navMoreMeasureRef.value.getBoundingClientRect().width
      : 0
    const ordered = items
      .map((item, index) => ({ item, index }))
      .sort((a, b) => {
        if (b.item.priority !== a.item.priority) {
          return b.item.priority - a.item.priority
        }
        return a.index - b.index
      })

    let selected = new Set()
    let totalWidth = 0
    let count = 0

    const addItem = (index) => {
      const width = widths[index] || 0
      totalWidth = count === 0 ? width : totalWidth + gap + width
      count += 1
      selected.add(index)
    }

    ordered.forEach(({ index }) => {
      const width = widths[index] || 0
      const nextWidth = count === 0 ? width : totalWidth + gap + width
      if (nextWidth <= availableWidth) {
        addItem(index)
      }
    })

    const hasOverflow = selected.size < items.length

    if (hasOverflow) {
      const requiredWidth = () => {
        if (count === 0) {
          return moreWidth
        }
        return totalWidth + gap + moreWidth
      }

      while (count > 0 && requiredWidth() > availableWidth) {
        const removable = [...ordered].reverse().find((entry) => selected.has(entry.index))
        if (!removable) break
        const width = widths[removable.index] || 0
        selected.delete(removable.index)
        count -= 1
        totalWidth = count === 0 ? 0 : totalWidth - width - gap
      }
    }

    visibleNav.value = items.filter((_, index) => selected.has(index))
    overflowNav.value = items.filter((_, index) => !selected.has(index))
  })
}

const updateHeaderScrollState = () => {
  if (!process.client) return
  isScrolled.value = !isHomeRoute.value || window.scrollY >= HEADER_SCROLL_TRIGGER
}

const closeMenu = () => {
  isMenuOpen.value = false
}

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const toggleLang = () => {
  isLangOpen.value = !isLangOpen.value
}

const toggleMore = () => {
  isMoreOpen.value = !isMoreOpen.value
}

const switchLanguage = (code) => {
  const newLocale = getLocaleLink(code)
  if (!newLocale) return
  isLangOpen.value = false
  isMoreOpen.value = false
  closeMenu()
  navigateTo(newLocale)
}

const openCart = () => {
  useModal().open(resolveComponent('ModalCart'), null, null, {
    width: { min: 968, max: 968 }
  })
}

const handleNavLinkClick = (options = {}) => {
  if (options.closeMore !== false) {
    isMoreOpen.value = false
  }
  if (options.closeLang) {
    isLangOpen.value = false
  }
  if (options.closeMenu !== false) {
    closeMenu()
  }
}

const syncDesktopCatalogAvailability = () => {
  isDesktopCatalogAvailable.value = Boolean(desktopCatalogMediaQuery?.matches)

  if (!isDesktopCatalogAvailable.value) {
    isCatalogDropdownEnabled.value = false
  }
}

const enableCatalogDropdown = () => {
  if (!isDesktopCatalogAvailable.value) return
  isCatalogDropdownEnabled.value = true
}

const closeCatalogDropdown = () => {
  isCatalogDropdownEnabled.value = false
}

const handleCatalogFocusOut = (event) => {
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

const handleLogoClick = async () => {
  isLangOpen.value = false
  isMoreOpen.value = false
  closeMenu()

  if (process.client) {
    window.location.assign(homeLink.value)
    return
  }

  await navigateTo(homeLink.value)
}

const phoneLink = computed(() => {
  const value = String(contacts.phone.value || '')
  if (!value) return null
  return `tel:${value.replace(/\s+/g, '')}`
})

const emailLink = computed(() => {
  const value = String(contacts.email.value || '')
  if (!value) return null
  return `mailto:${value}`
})

const contactItems = computed(() => {
  return [
    {
      id: 'phone',
      label: t('contacts.phone'),
      value: contacts.phone.value,
      href: phoneLink.value,
      icon: 'mynaui:telephone-call'
    },
    {
      id: 'email',
      label: t('contacts.email'),
      value: contacts.email.value,
      href: emailLink.value,
      icon: 'ic:round-alternate-email'
    },
    {
      id: 'address',
      label: t('contacts.address'),
      value: contacts.addressSummary.value,
      href: null,
      icon: 'mynaui:location'
    },
    {
      id: 'schedule',
      label: t('contacts.schedule'),
      value: contacts.scheduleSummary.value,
      href: null,
      icon: 'mynaui:clock-4'
    }
  ].filter((item) => item.value)
})

const handleDocumentClick = (event) => {
  const target = event.target
  if (isLangOpen.value && langRef.value && !langRef.value.contains(target)) {
    isLangOpen.value = false
  }
  if (isMoreOpen.value && moreRef.value && !moreRef.value.contains(target)) {
    isMoreOpen.value = false
  }
}

const handleEscape = (event) => {
  if (event.key !== 'Escape') return
  isLangOpen.value = false
  isMoreOpen.value = false
  closeMenu()
}

onBeforeUpdate(resetMeasureRefs)

onMounted(() => {
  resetMeasureRefs()
  updateNavLayout()
  updateHeaderScrollState()
  desktopCatalogMediaQuery = window.matchMedia('(min-width: 1024px)')
  syncDesktopCatalogAvailability()

  const mediaListener = () => {
    syncDesktopCatalogAvailability()
  }

  if (typeof desktopCatalogMediaQuery.addEventListener === 'function') {
    desktopCatalogMediaQuery.addEventListener('change', mediaListener)
    cleanupDesktopCatalogMediaQuery = () => desktopCatalogMediaQuery?.removeEventListener('change', mediaListener)
  } else {
    desktopCatalogMediaQuery.addListener(mediaListener)
    cleanupDesktopCatalogMediaQuery = () => desktopCatalogMediaQuery?.removeListener(mediaListener)
  }

  window.addEventListener('resize', updateNavLayout)
  window.addEventListener('resize', updateHeaderScrollState)
  window.addEventListener('scroll', updateHeaderScrollState, { passive: true })
  document.addEventListener('click', handleDocumentClick)
  document.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateNavLayout)
  window.removeEventListener('resize', updateHeaderScrollState)
  window.removeEventListener('scroll', updateHeaderScrollState)
  document.removeEventListener('click', handleDocumentClick)
  document.removeEventListener('keydown', handleEscape)
  if (process.client) {
    document.body.style.overflow = ''
  }
  cleanupDesktopCatalogMediaQuery?.()
})

watch(
  () => [locale.value, navItems.value.map((item) => item.title).join('|')],
  () => {
    resetMeasureRefs()
    updateNavLayout()
  },
  { flush: 'post' }
)

watch(isMenuOpen, (value) => {
  if (!process.client) return
  document.body.style.overflow = value ? 'hidden' : ''
})

watch(
  () => route.fullPath,
  () => {
    isLangOpen.value = false
    isMoreOpen.value = false
    closeCatalogDropdown()
    closeMenu()
    updateHeaderScrollState()
  }
)
</script>

<style src="./header.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <header ref="headerRef" class="hero-header" :class="{ 'hero-header--scrolled': isHeaderScrolled }">
    <div class="hero-header__inner container">
      <NuxtLink :to="homeLink" class="hero-header__logo-link" @click.prevent="handleLogoClick">
        <nuxt-img
          v-if="$device.isDesktop"
          src="/images/company-white-color.png"
          sizes="mobile: 160px"
          class="hero-header__logo"
          :alt="t('logo_alt')"
        />
        <nuxt-img
          v-else
          src="/images/logo/vivadzen.png"
          sizes="mobile: 240px"
          class="hero-header__logo"
          :alt="t('logo_alt')"
        />
      </NuxtLink>
      <div ref="menuRef" class="hero-header__menu">
        <button
          type="button"
          class="hero-header__menu-btn"
          :class="{ 'is-open': isMenuOpen }"
          @click="toggleMenu"
          :aria-label="isMenuOpen ? t('menu.close') : t('menu.button')"
          :aria-expanded="isMenuOpen"
        >
          <span class="hero-header__menu-burger" aria-hidden="true">
            <span class="hero-header__menu-line hero-header__menu-line--top"></span>
            <span class="hero-header__menu-line hero-header__menu-line--middle"></span>
            <span class="hero-header__menu-line hero-header__menu-line--bottom"></span>
          </span>
          <span class="hero-header__menu-text">{{ isMenuOpen ? t('menu.close') : t('menu.button') }}</span>
        </button>
      </div>

      <nav
        ref="navContainerRef"
        class="hero-header__nav"
        :class="{ 'is-more-open': isMoreOpen }"
        :aria-label="t('nav.label')"
      >
        <div
          v-for="item in visibleNav"
          :key="item.id"
          class="hero-header__nav-item"
          :class="{
            'hero-header__nav-item--catalog': item.id === 'catalog',
            'is-catalog-open': item.id === 'catalog' && isCatalogDropdownEnabled,
          }"
          @mouseenter="item.id === 'catalog' ? enableCatalogDropdown() : undefined"
          @mouseleave="item.id === 'catalog' ? closeCatalogDropdown() : undefined"
          @focusin="item.id === 'catalog' ? enableCatalogDropdown() : undefined"
          @focusout="item.id === 'catalog' ? handleCatalogFocusOut($event) : undefined"
        >
          <NuxtLink
            :to="regionPath(item.to)"
            class="hero-header__nav-link"
            :class="{ 'hero-header__nav-link--catalog': item.id === 'catalog' }"
            @click="handleNavLinkClick({ closeMenu: false })"
          >
            <span>{{ item.title }}</span>
            <IconCSS
              v-if="item.id === 'catalog'"
              name="iconoir:nav-arrow-down"
              class="hero-header__nav-link-icon"
            />
          </NuxtLink>

          <KratomCatalogDropdown
            v-if="item.id === 'catalog'"
            :enabled="isDesktopCatalogAvailable && isCatalogDropdownEnabled"
            @navigate="closeCatalogDropdown"
          />
        </div>

        <div
          v-if="overflowNav.length"
          ref="moreRef"
          class="hero-header__nav-more"
          @click.stop
        >
          <button
            type="button"
            class="hero-header__nav-more-btn"
            :aria-expanded="isMoreOpen"
            @click="toggleMore"
          >
            {{ t('nav.more') }}
            <IconCSS name="iconoir:nav-arrow-down" class="hero-header__nav-more-icon" />
          </button>

          <div v-if="isMoreOpen" class="hero-header__nav-more-menu" @click.stop>
            <NuxtLink
              v-for="item in overflowNav"
              :key="item.id"
              :to="regionPath(item.to)"
              class="hero-header__nav-link"
              @click="handleNavLinkClick({ closeMenu: false })"
            >
              {{ item.title }}
            </NuxtLink>
          </div>
        </div>

        <div class="hero-header__nav-measure" aria-hidden="true">
          <span
            v-for="(item, index) in navItems"
            :key="item.id"
            :ref="(el) => setMeasureRef(el, index)"
            class="hero-header__nav-link hero-header__nav-link--measure"
          >
            {{ item.title }}
          </span>
          <span ref="navMoreMeasureRef" class="hero-header__nav-more-btn hero-header__nav-more-btn--measure">
            {{ t('nav.more') }}
          </span>
        </div>
        <a
          :href="entheogensStoreUrl"
          class="hero-header__promo-cta"
          target="_blank"
          rel="noopener noreferrer"
        >
          <IconCSS name="fluent:flash-24-regular" class="hero-header__promo-cta-icon" />
          <span>{{ t('cta.entheogens') }}</span>
        </a>
      </nav>

      <div ref="langRef" class="hero-header__lang" :aria-label="t('lang.label')" @click.stop>
        <button
          type="button"
          class="lang-switcher"
          :aria-expanded="isLangOpen"
          @click="toggleLang"
        >
          <span class="lang-switcher__label">{{ activeLanguage?.shortName }}</span>
          <IconCSS name="iconoir:nav-arrow-down" class="lang-switcher__chevron" />
        </button>

        <div v-if="isLangOpen" class="lang-switcher__menu" role="menu" @click.stop>
          <button
            v-for="item in languageLinks"
            :key="item.code"
            type="button"
            class="lang-switcher__item"
            :class="{ 'is-active': item.isActive }"
            :aria-label="item.name"
            @click="switchLanguage(item.code)"
          >
            <span class="lang-switcher__item-text">{{ item.name }}</span>
            <span class="lang-switcher__item-short">{{ item.shortName }}</span>
          </button>
        </div>

        <NuxtLink
          :to="accountLink"
          class="hero-header__account"
          :aria-label="accountLabel"
          :title="accountLabel"
          @click="handleNavLinkClick({ closeMenu: false, closeLang: true })"
        >
          <img
            v-if="accountAvatarSrc"
            :src="accountAvatarSrc"
            :alt="accountLabel"
            class="hero-header__account-avatar"
            loading="lazy"
            @error="accountAvatarFailed = true"
          >
          <IconCSS v-else name="ph:user-circle-fill" class="hero-header__account-icon" />
        </NuxtLink>

        <button
          type="button"
          class="hero-header__cart"
          :aria-label="t('title.cart')"
          @click="openCart"
        >
          <IconCSS name="ci:shopping-cart-01" class="hero-header__cart-icon" />
          <span class="hero-header__cart-count">{{ cartCount }}</span>
        </button>
      </div>

      <div class="hero-mobile-menu" :class="{ 'is-open': isMenuOpen }">
        <div class="hero-mobile-menu__backdrop" @click="closeMenu"></div>
        <div class="hero-mobile-menu__panel">
          <div class="hero-mobile-menu__content">
            <nav class="hero-mobile-menu__nav" :aria-label="t('nav.label')">
              <NuxtLink
                v-for="item in mobileNavItems"
                :key="item.id"
                :to="regionPath(item.to)"
                class="hero-mobile-menu__link"
                @click="handleNavLinkClick()"
              >
                {{ item.title }}
              </NuxtLink>
            </nav>

            <div class="hero-mobile-menu__contacts">
              <div class="hero-mobile-menu__contacts-title">{{ t('menu.contacts_title') }}</div>
              <div class="hero-mobile-menu__contacts-list">
                <component
                  :is="item.href ? 'a' : 'div'"
                  v-for="item in contactItems"
                  :key="item.id"
                  class="hero-mobile-menu__contact"
                  :href="item.href"
                >
                  <IconCSS :name="item.icon" class="hero-mobile-menu__contact-icon" />
                  <div class="hero-mobile-menu__contact-text">
                    <span class="hero-mobile-menu__contact-label">{{ item.label }}</span>
                    <span class="hero-mobile-menu__contact-value">{{ item.value }}</span>
                  </div>
                </component>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div v-if="showPromoStrip" class="hero-header__promo-strip">
    <div class="hero-header__promo-strip-inner container">
      <a
        :href="entheogensStoreUrl"
        class="hero-header__promo-mobile-btn"
        target="_blank"
        rel="noopener noreferrer"
      >
        <IconCSS name="fluent:flash-24-regular" class="icon" />
        <span class="hero-header__promo-mobile-btn-title">{{ t('cta.entheogens') }}</span>
        <!-- <span class="hero-header__promo-mobile-btn-caption">shop.vivadzen.com</span> -->
      </a>
    </div>
  </div>
</template>
