<script setup lang="ts">
const { t } = useI18n()
const route = useRoute()
const regionPath = useToLocalePath()
const { phone, email, addressLines } = useContacts()
const { messengers, networks } = useSocial()
const { vendors: paymentVendors } = usePayment()
const isFlushTop = computed(() => Boolean(route.meta?.footerFlushTop))

const legalLinks = computed(() => [
  { to: '/delivery', label: t('title.delivery') },
  { to: '/payment', label: t('title.payment') },
  { to: '/policy', label: t('title.policy') },
  { to: '/terms', label: t('title.terms') },
  { to: '/returns', label: t('title.returns') },
])

const infoLinks = computed(() => [
  { to: '/catalog', label: t('title.catalog') },
  { to: '/reviews', label: t('title.reviews') },
  { to: '/about', label: t('title.about') },
  { to: '/contacts', label: t('title.contacts') },
])
</script>

<template>
  <footer class="site-footer" :class="{ 'site-footer--flush-top': isFlushTop }">
    <div class="container">
      <KratomRegionSwitcher class="site-footer__region-switcher" />

      <div class="site-footer__grid">
        <div class="site-footer__intro">
          <p class="site-footer__eyebrow">{{ t('kratom.footer.eyebrow') }}</p>
          <h2>{{ t('kratom.footer.title') }}</h2>
          <p>{{ t('kratom.footer.text') }}</p>
          <div v-if="paymentVendors.length" class="site-footer__payments" :aria-label="t('title.payment')">
            <span v-for="vendor in paymentVendors" :key="vendor.id" class="site-footer__payment">
              <nuxt-img
                :src="vendor.logo"
                :alt="vendor.title"
                width="150"
                height="90"
                fit="outside"
                loading="lazy"
              />
            </span>
          </div>
        </div>

        <div>
          <p class="site-footer__title">{{ t('title.catalog') }}</p>
          <div class="site-footer__links">
            <NuxtLink v-for="item in infoLinks" :key="item.to" :to="regionPath(item.to)">{{ item.label }}</NuxtLink>
          </div>
        </div>

        <div>
          <p class="site-footer__title">{{ t('kratom.footer.legal') }}</p>
          <div class="site-footer__links">
            <NuxtLink v-for="item in legalLinks" :key="item.to" :to="regionPath(item.to)">{{ item.label }}</NuxtLink>
          </div>
        </div>

        <div>
          <p class="site-footer__title">{{ t('title.contacts') }}</p>
          <div class="site-footer__contacts">
            <a :href="`tel:${String(phone || '').replace(/\s+/g, '')}`">{{ phone }}</a>
            <a :href="`mailto:${email}`">{{ email }}</a>
            <span v-for="(line, index) in addressLines" :key="`footer-address-${index}`">{{ line }}</span>
          </div>
          <div class="site-footer__socials">
            <a v-for="item in [...messengers, ...networks]" :key="item.key" :href="item.link" target="_blank" rel="noopener">
              <IconCSS :name="item.icon" />
            </a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped lang="scss">
.site-footer {
  padding: 50px 0 60px;
  background:
    radial-gradient(circle at top left, rgba(138, 90, 43, 0.2), transparent 35%),
    linear-gradient(180deg, #1e241f, #0f1410);
  color: rgba(255, 247, 236, 0.8);
}

.site-footer--flush-top {
  margin-top: 0;
}

.site-footer__region-switcher {
  margin-bottom: 34px;
}

.site-footer__grid {
  display: grid;
  gap: 32px;

  @include tablet {
    grid-template-columns: 1.4fr 1fr 1fr 1fr;
  }
}

.site-footer__eyebrow,
.site-footer__title {
  margin-bottom: 14px;
  color: #d9bf96;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 700;
}

.site-footer__intro h2 {
  margin-bottom: 14px;
  color: #fff7ec;
  font-size: 34px;
  line-height: 1.05;
}

.site-footer__intro p {
  line-height: 1.7;
}

.site-footer__payments {
  margin-top: 18px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.site-footer__payment {
  width: 58px;
  height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255, 247, 236, 0.12);
  border-radius: 6px;
  background: rgba(255, 247, 236, 1);

  img {
    max-width: 42px;
    max-height: 24px;
    object-fit: contain;
  }
}

.site-footer__links,
.site-footer__contacts {
  display: grid;
  gap: 10px;
}

.site-footer a,
.site-footer span {
  color: rgba(255, 247, 236, 0.85);
  text-decoration: none;
  line-height: 1.6;
}

.site-footer__socials {
  margin-top: 16px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;

  a {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: rgba(255, 247, 236, 0.08);
  }
}
</style>
