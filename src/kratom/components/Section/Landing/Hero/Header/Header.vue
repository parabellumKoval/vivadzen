<script setup>
const { t, locale, locales } = useI18n()
const regionStore = useRegion()
const contacts = useContacts()
const regionPath = useToLocalePath()
const { openLicenseModal, openCertificatesModal } = useDocumentsModal()

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

const localeIconMap = {
  cs: 'emojione:flag-for-czechia',
  en: 'emojione:flag-for-united-kingdom',
  de: 'emojione:flag-for-germany',
  es: 'emojione:flag-for-spain',
  ru: 'emojione:flag-for-russia',
  uk: 'emojione:flag-for-ukraine'
}

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
      icon: localeIconMap[item.code] || 'hugeicons:language-skill',
      isActive: item.code === locale.value
    }))
})

const activeLanguage = computed(() => {
  return languageLinks.value.find((item) => item.isActive) || languageLinks.value[0]
})

const cartCount = computed(() => useCartStore().cartLength)

const navItems = computed(() => [
  { id: 'legal', link: '#legal', title: t('nav.legal'), priority: 20 },
  { id: 'timeline', link: '#timeline', title: t('nav.timeline'), priority: 30 },
  { id: 'kratom', link: '#kratom', title: t('nav.kratom'), priority: 40 },
  { id: 'colors', link: '#colors', title: t('nav.colors'), priority: 50 },
  { id: 'origins', link: '#origins', title: t('nav.origins'), priority: 60 },
  { id: 'bonus', link: '#bonus', title: t('nav.bonus'), priority: 70 },
  { id: 'reviews', link: '#reviews', title: t('nav.reviews'), priority: 80 },
  { id: 'contacts', link: '#contacts', title: t('nav.contacts'), priority: 100 }
])

const infoItems = computed(() => [
  { id: 'certificates', action: 'certificates', title: t('info.certificates') },
  { id: 'labs', action: 'licenses', title: t('info.labs') },
  { id: 'store', action: 'store', title: t('info.store') }
])

const visibleNav = ref(navItems.value)
const overflowNav = ref([])

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
  isScrolled.value = window.scrollY >= HEADER_SCROLL_TRIGGER
}

const scrollToSection = (target) => {
  if (process.server) return
  if (!target) return
  const id = target.startsWith('#') ? target.slice(1) : target
  const element = document.getElementById(id)
  if (!element) return
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  element.scrollIntoView({
    behavior: prefersReducedMotion ? 'auto' : 'smooth',
    block: 'start'
  })
  window.history.replaceState(null, '', `#${id}`)
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

const handleNavClick = (link, options = {}) => {
  if (options.closeMore !== false) {
    isMoreOpen.value = false
  }
  if (options.closeLang) {
    isLangOpen.value = false
  }
  if (options.closeMenu !== false) {
    closeMenu()
  }
  nextTick(() => scrollToSection(link))
}

const handleInfoClick = (action) => {
  closeMenu()
  nextTick(() => {
    if (action === 'certificates') {
      openCertificatesModal()
  } else if (action === 'licenses') {
      openLicenseModal()
    } else if (action === 'store') {
      navigateTo(regionPath('/'))
    }
  })
}

const handleLogoClick = () => {
  handleNavClick('#home')
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
      value: contacts.address.value,
      href: null,
      icon: 'mynaui:location'
    },
    {
      id: 'schedule',
      label: t('contacts.schedule'),
      value: contacts.schedule.value,
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
</script>

<style src="./header.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <header ref="headerRef" class="hero-header" :class="{ 'hero-header--scrolled': isScrolled }">
    <div class="hero-header__inner container">
      <nuxt-img
        v-if="$device.isDesktop"
        src="/images/company-white-color.png"
        sizes="mobile: 160px"
        class="hero-header__logo"
        :alt="t('logo_alt')"
        role="button"
        tabindex="0"
        @click="handleLogoClick"
        @keydown.enter.prevent="handleLogoClick"
        @keydown.space.prevent="handleLogoClick"
      />
      <nuxt-img
        v-else
        src="/images/logo/vivadzen.png"
        sizes="mobile: 240px"
        class="hero-header__logo"
        :alt="t('logo_alt')"
        role="button"
        tabindex="0"
        @click="handleLogoClick"
        @keydown.enter.prevent="handleLogoClick"
        @keydown.space.prevent="handleLogoClick"
      />
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
          <span class="hero-header__menu-text">{{ isMenuOpen ? 'CLOSE' : t('menu.button') }}</span>
        </button>
      </div>

      <nav
        ref="navContainerRef"
        class="hero-header__nav"
        :class="{ 'is-more-open': isMoreOpen }"
        :aria-label="t('nav.label')"
      >
        <a
          v-for="item in visibleNav"
          :key="item.id"
          :href="item.link"
          class="hero-header__nav-link"
          @click.prevent="handleNavClick(item.link, { closeMenu: false })"
        >
          {{ item.title }}
        </a>

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
            <a
              v-for="item in overflowNav"
              :key="item.id"
              :href="item.link"
              class="hero-header__nav-link"
              @click.prevent="handleNavClick(item.link, { closeMenu: false })"
            >
              {{ item.title }}
            </a>
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

        <button
          type="button"
          class="hero-header__cart"
          :aria-label="t('title.cart')"
          @click="openCart"
        >
          <IconCSS name="mynaui:shopping-cart-solid" class="hero-header__cart-icon" />
          <span class="hero-header__cart-count">{{ cartCount }}</span>
        </button>
      </div>

      <div class="hero-mobile-menu" :class="{ 'is-open': isMenuOpen }">
        <div class="hero-mobile-menu__backdrop" @click="closeMenu"></div>
        <div class="hero-mobile-menu__panel">
          <div class="hero-mobile-menu__content">
            <nav class="hero-mobile-menu__nav" :aria-label="t('nav.label')">
              <a
                v-for="item in navItems"
                :key="item.id"
                :href="item.link"
                class="hero-mobile-menu__link"
                @click.prevent="handleNavClick(item.link)"
              >
                {{ item.title }}
              </a>
            </nav>

            <div class="hero-mobile-menu__info">
              <div class="hero-mobile-menu__info-title">{{ t('info.title') }}</div>
              <div class="hero-mobile-menu__info-list">
                <button
                  v-for="item in infoItems"
                  :key="item.id"
                  type="button"
                  class="hero-mobile-menu__info-link"
                  @click="handleInfoClick(item.action)"
                >
                  {{ item.title }}
                </button>
              </div>
            </div>

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
</template>
