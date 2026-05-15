<script setup lang="ts">
const { t } = useI18n()
const route = useRoute()
const regionPath = useToLocalePath()
const { phone, email, addressLines } = useContacts()
const { messengers, networks } = useSocial()
const { vendors: paymentVendors } = usePayment()
const hasTopSpacing = computed(() => route.meta?.footerTopSpacing !== false && route.meta?.footerFlushTop !== true)
const currentYear = new Date().getFullYear()

const qualityMarks = computed(() => [
  {
    src: '/images/vscht.jpeg',
    title: t('kratom.footer.quality_marks.vscht.title'),
    description: t('kratom.footer.quality_marks.vscht.description'),
  },
  {
    src: '/images/mzcz.png',
    title: t('kratom.footer.quality_marks.mzcz.title'),
    description: t('kratom.footer.quality_marks.mzcz.description'),
  },
])

const legalLinks = computed(() => [
  { to: '/delivery', label: t('title.delivery') },
  { to: '/payment', label: t('title.payment') },
  { to: '/age-verification', label: t('title.age_verification') },
  { to: '/returns', label: t('title.returns') },
])

const footerBottomLinks = computed(() => [
  { to: '/policy', label: t('title.policy') },
  { to: '/terms', label: t('title.terms') },
])

const infoLinks = computed(() => [
  { to: '/catalog', label: t('title.catalog') },
  { to: '/reviews', label: t('title.reviews') },
  { to: '/about', label: t('title.about') },
  { to: '/contacts', label: t('title.contacts') },
])
</script>

<template>
  <footer class="site-footer" :class="{ 'site-footer--flush-top': !hasTopSpacing }">
    <div class="container">
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

      <div class="site-footer__bottom">
        <div class="site-footer__bottom-legal">
          <div class="site-footer__bottom-copy">
            <span>&copy; {{ currentYear }} VivaDzen. {{ t('kratom.footer.rights') }}</span>
            <span>{{ t('kratom.footer.vat_note') }}</span>
          </div>

          <div class="site-footer__bottom-links">
            <NuxtLink v-for="item in footerBottomLinks" :key="`footer-bottom-${item.to}`" :to="regionPath(item.to)">
              {{ item.label }}
            </NuxtLink>
          </div>
        </div>

        <div v-if="qualityMarks.length" class="site-footer__quality-marks">
          <div v-for="item in qualityMarks" :key="item.src" class="site-footer__quality-mark">
            <div class="site-footer__quality-mark-logo">
              <nuxt-img :src="item.src" :alt="item.title" width="112" height="112" fit="contain" loading="lazy" />
            </div>
            <div class="site-footer__quality-mark-copy">
              <h3>{{ item.title }}</h3>
              <p>{{ item.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped lang="scss">
.site-footer {
  margin-top: clamp(40px, 6vw, 80px);
  padding: 50px 0 60px;
  background:
    radial-gradient(circle at top left, rgba(138, 90, 43, 0.2), transparent 35%),
    linear-gradient(180deg, #1e241f, #0f1410);
  color: rgba(255, 247, 236, 0.8);
}

.site-footer--flush-top {
  margin-top: 0;
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

.site-footer__quality-marks {
  order: -1;
  display: grid;
  gap: 14px;
  width: 100%;

  @include tablet {
    order: initial;
    width: 100%;
    justify-self: end;
  }

  @include desktop {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.site-footer__quality-mark {
  display: grid;
  grid-template-columns: 72px minmax(0, 1fr);
  gap: 14px;
  align-items: center;
  padding: 14px;
  border: 1px solid rgba(255, 247, 236, 0.12);
  border-radius: 18px;
  background: rgba(255, 247, 236, 0.05);
}

.site-footer__quality-mark-logo {
  width: 72px;
  height: 72px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  overflow: hidden;
  background: rgba(255, 248, 236, 0.96);

  img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
}

.site-footer__quality-mark-copy {
  h3 {
    margin: 0 0 6px;
    color: #fff7ec;
    font-size: 14px;
    line-height: 1.35;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  p {
    margin: 0;
    color: rgba(255, 247, 236, 0.78);
    font-size: 13px;
    line-height: 1.6;
  }
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

.site-footer a {
  transition:
    color 0.2s ease,
    text-decoration-color 0.2s ease,
    background-color 0.2s ease,
    transform 0.2s ease;

  &:hover {
    color: #fff7ec;
    text-decoration: underline;
    text-decoration-color: rgba(217, 191, 150, 0.75);
    text-underline-offset: 0.2em;
  }
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

    &:hover {
      background: rgba(255, 247, 236, 0.14);
      transform: translateY(-1px);
      text-decoration: none;
    }
  }
}

.site-footer__bottom {
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid rgba(255, 247, 236, 0.12);
  display: grid;
  gap: 22px;
  align-items: start;

  @include tablet {
    grid-template-columns: max-content minmax(0, 1fr);
    gap: clamp(28px, 5vw, 72px);
  }

  @include desktop {
    align-items: center;
  }
}

.site-footer__bottom-legal {
  display: grid;
  gap: 16px;
  justify-items: start;

  @include tablet {
    grid-template-columns: minmax(260px, 320px) max-content;
    align-items: start;
    gap: 22px;
    justify-self: start;
    width: auto;
  }
}

.site-footer__bottom-copy {
  display: grid;
  gap: 6px;
  font-size: 13px;
  line-height: 1.6;
  color: rgba(255, 247, 236, 0.62);
}

.site-footer__bottom-links {
  display: grid;
  gap: 6px;
  justify-content: flex-start;

  @include tablet {
    padding-left: 22px;
    border-left: 1px solid rgba(255, 247, 236, 0.16);
  }

  a {
    color: rgba(255, 247, 236, 0.82);
    font-size: 13px;
    line-height: 1.6;
    text-decoration: underline;
    text-decoration-color: rgba(217, 191, 150, 0.6);
    text-decoration-thickness: 1px;
    text-underline-offset: 0.22em;

    &:hover {
      text-decoration-color: rgba(255, 247, 236, 0.9);
    }
  }
}

@media (max-width: 380px) {
  .site-footer__quality-mark {
    grid-template-columns: 1fr;
    justify-items: start;
  }
}
</style>
