<script setup lang="ts">
import {
  LayoutDashboard, Package, Tags, ShoppingCart, Image as ImageIcon, LogOut, ExternalLink, RefreshCw, Star, MessageSquare,
} from 'lucide-vue-next'

const auth = useAuthStore()
const api = useApi()
const config = useRuntimeConfig()
const route = useRoute()
const { t, contentLocales, previewContentLocale } = useAdminI18n()

const nav = [
  { to: '/',           label: t('nav.dashboard'),  icon: LayoutDashboard },
  { to: '/products',   label: t('nav.products'),   icon: Package },
  { to: '/taxonomies', label: t('nav.taxonomies'), icon: Tags },
  { to: '/orders',     label: t('nav.orders'),     icon: ShoppingCart },
  { to: '/reviews',    label: 'Отзывы',            icon: Star },
  { to: '/questions',  label: 'Вопросы',           icon: MessageSquare },
  { to: '/media',      label: t('nav.media'),      icon: ImageIcon },
]

const warming = ref(false)
async function warmCache() {
  warming.value = true
  try {
    await api('/cache/warm', { method: 'POST' })
  } finally {
    warming.value = false
  }
}

async function logout() {
  try { await api('/logout', { method: 'POST' }) } catch {}
  auth.clear()
  navigateTo('/login')
}

const isActive = (path: string) =>
  path === '/' ? route.path === '/' : route.path.startsWith(path)
</script>

<template>
  <div class="min-h-screen flex w-full">
    <!-- Sidebar -->
    <aside class="w-64 shrink-0 bg-moss-900 text-cream-50 flex flex-col">
      <div class="px-6 py-5 border-b border-moss-700/40">
        <div class="font-display text-2xl tracking-wide">Vivadzen</div>
        <div class="text-xs text-cream-50/60 uppercase tracking-widest">Admin</div>
      </div>

      <nav class="flex-1 px-3 py-4 space-y-1">
        <NuxtLink
          v-for="item in nav"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors"
          :class="isActive(item.to)
            ? 'bg-moss-700 text-white'
            : 'text-cream-50/80 hover:bg-moss-700/60 hover:text-white'"
        >
          <component :is="item.icon" :size="18" />
          <span>{{ item.label }}</span>
        </NuxtLink>
      </nav>

      <div class="px-3 py-3 border-t border-moss-700/40 space-y-1">
        <button
          class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-cream-50/80 hover:bg-moss-700/60"
          :disabled="warming"
          @click="warmCache"
        >
          <RefreshCw :size="16" :class="warming ? 'animate-spin' : ''" />
          {{ warming ? t('layout.warming_cache') : t('layout.warm_cache') }}
        </button>
        <a
          :href="config.public.siteBase"
          target="_blank"
          class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-cream-50/80 hover:bg-moss-700/60"
        >
          <ExternalLink :size="16" />{{ t('layout.open_site') }}
        </a>
        <button
          class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-cream-50/80 hover:bg-moss-700/60"
          @click="logout"
        >
          <LogOut :size="16" />{{ t('layout.logout') }}
        </button>
      </div>
    </aside>

    <!-- Main -->
    <main class="flex-1 min-w-0 flex flex-col">
      <header class="h-14 px-6 flex items-center justify-between border-b border-ink-700/10 bg-white">
        <div class="text-sm text-ink-700/70">
          {{ auth.user?.email }} <span class="ml-2 text-ink-700/40">·</span>
          <span class="ml-2 capitalize">{{ auth.user?.role }}</span>
        </div>
        <div class="text-xs text-ink-700/50 text-right">
          <div>{{ t('layout.content_locale') }}: {{ contentLocales.find((locale) => locale.code === previewContentLocale)?.native }}</div>
          <div>API: {{ config.public.apiBase }}</div>
        </div>
      </header>
      <div class="flex-1 p-6 overflow-auto">
        <slot />
      </div>
    </main>
  </div>
</template>
