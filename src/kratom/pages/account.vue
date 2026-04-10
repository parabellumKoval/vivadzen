<script setup lang="ts">
const { t } = useI18n()
const route = useRoute()
const regionPath = useToLocalePath()
const { user, token, init, me, logout } = useAuth()

definePageMeta({
  bg: '#f3ece2',
  middleware: 'auth-bridge-auth',
})

await init()

watch(
  [() => user.value, () => token.value],
  ([currentUser, currentToken]) => {
    if (!currentUser && currentToken) {
      me(true).catch(() => {})
    }
  },
  { immediate: true },
)

const breadcrumbs = computed(() => {
  const list = [
    { name: t('title.home'), item: '/' },
    { name: t('title.account.account'), item: '/account' },
  ]

  if (route.meta?.crumb) {
    list.push({
      name: t(String(route.meta.crumb.name)),
      item: String(route.meta.crumb.item),
    })
  }

  return list
})

const activeTab = computed(() => String(route.meta?.tab || 'orders'))

const menuItems = computed(() => [
  {
    key: 'orders',
    label: t('title.account.orders'),
    icon: 'ph:shopping-bag-open',
    link: '/account/orders',
  },
  {
    key: 'settings',
    label: t('title.account.settings'),
    icon: 'ph:sliders-horizontal',
    link: '/account/settings',
  },
  {
    key: 'addresses',
    label: t('title.account.addresses'),
    icon: 'ph:map-pin-line',
    link: '/account/addresses',
  },
  {
    key: 'logout',
    label: t('button.logout'),
    icon: 'ph:sign-out',
    action: () => openLogoutConfirm(),
  },
])

const handleItemClick = async (item: Record<string, any>) => {
  if (item.link) {
    await navigateTo(regionPath(item.link))
    return
  }

  if (typeof item.action === 'function') {
    item.action()
  }
}

const confirmLogout = async () => {
  await logout()
  await navigateTo(regionPath('/'))
}

const openLogoutConfirm = () => {
  useModal().open(resolveComponent('ModalConfirm'), {
    title: t('logout_title'),
    desc: t('logout_desc'),
    yes: {
      title: t('button.logout'),
      callback: confirmLogout,
    },
    no: {
      title: t('button.cancel'),
      callback: null,
    },
    type: 'default',
  }, null, {
    width: { max: 420 },
  })
}
</script>

<i18n src="./account/lang.yaml" lang="yaml"></i18n>

<template>
  <div class="page-base kratom-account-page">
    <div class="container kratom-page-container">
      <the-breadcrumbs :crumbs="breadcrumbs" />

      <section class="kratom-account-hero">
        <p class="kratom-account-hero__eyebrow">{{ t('eyebrow') }}</p>
        <h1 class="kratom-account-hero__title">{{ t('title.account.account') }}</h1>
        <p class="kratom-account-hero__text">{{ t('text') }}</p>
      </section>

      <div class="kratom-account-layout">
        <aside class="kratom-account-aside">
          <section class="kratom-account-card">
            <account-card />
          </section>

          <nav class="kratom-account-menu">
            <button
              v-for="item in menuItems"
              :key="item.key"
              type="button"
              class="kratom-account-menu__item"
              :class="{ 'is-active': item.key === activeTab }"
              @click="handleItemClick(item)"
            >
              <IconCSS :name="item.icon" size="20" />
              <span>{{ item.label }}</span>
            </button>
          </nav>
        </aside>

        <section class="kratom-account-content">
          <NuxtPage />
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-account-page {
  padding-top: 24px;
}

.kratom-account-hero {
  margin-bottom: 28px;
}

.kratom-account-hero__eyebrow {
  margin-bottom: 12px;
  color: #8a5a2b;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 700;
}

.kratom-account-hero__title {
  margin-bottom: 12px;
  font-size: 44px;
  line-height: 0.98;
}

.kratom-account-hero__text {
  max-width: 760px;
  color: #5f6458;
  line-height: 1.7;
}

.kratom-account-layout {
  display: grid;
  gap: 22px;

  @include desktop {
    grid-template-columns: minmax(260px, 320px) minmax(0, 1fr);
    align-items: start;
  }
}

.kratom-account-card,
.kratom-account-menu,
.kratom-account-content {
  padding: 24px;
  border-radius: 28px;
  background: rgba(255, 250, 244, 0.94);
  border: 1px solid rgba(74, 91, 68, 0.1);
  box-shadow: 0 24px 60px rgba(39, 49, 36, 0.06);
}

.kratom-account-aside {
  display: grid;
  gap: 18px;

  @include desktop {
    position: sticky;
    top: 98px;
  }
}

.kratom-account-menu {
  display: grid;
  gap: 10px;
}

.kratom-account-menu__item {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 52px;
  padding: 0 16px;
  border: 1px solid rgba(74, 91, 68, 0.12);
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.72);
  color: #35524a;
  font-size: 15px;
  font-weight: 600;
  transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;

  &:hover {
    transform: translateY(-1px);
    background: rgba(255, 255, 255, 0.92);
  }

  &.is-active {
    border-color: rgba(138, 90, 43, 0.35);
    background: linear-gradient(135deg, rgba(138, 90, 43, 0.14), rgba(53, 82, 74, 0.08));
    color: #1f2b1d;
  }
}
</style>
