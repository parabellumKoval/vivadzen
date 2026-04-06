<script setup lang="ts">
import type { PartnerStore } from '../../../../composables/usePartnerStores'
import { PARTNER_STORES_HASH } from '../../../../composables/usePartnerStores'

const { t } = useI18n()
const props = withDefaults(defineProps<{
  showPartners?: boolean
}>(), {
  showPartners: false,
})

const { get } = useSettings()
const { partners } = usePartnerStores()

const contactInfo = computed(() => {
  return {
    address: get('site.contacts.address'),
    phone: get('site.contacts.phone'),
    email: get('site.contacts.email'),
    schedule: get('site.contacts.schedule'),
    map: get('site.contacts.map')
  }
})

const mapSrc = computed(() => {
  const raw = contactInfo.value.map
  if (raw && typeof raw === 'object' && 'value' in raw && typeof raw.value === 'string') {
    const match = raw.value.match(/src=["']([^"']+)["']/i)
    return match?.[1]?.trim() || raw.value.trim()
  }

  if (typeof raw !== 'string') {
    return ''
  }

  const match = raw.match(/src=["']([^"']+)["']/i)
  return match?.[1]?.trim() || raw.trim()
})

const contacts = computed(() => {
  return [
    {
      icon: 'iconoir:map',
      colorClass: 'contact-item--address',
      label: t('label.address'),
      value: contactInfo.value.address
    },
    {
      icon: 'iconoir:phone',
      colorClass: 'contact-item--phone',
      label: t('label.phone'),
      value: contactInfo.value.phone
    },
    {
      icon: 'iconoir:mail',
      colorClass: 'contact-item--email',
      label: t('label.email'),
      value: contactInfo.value.email
    },
    {
      icon: 'iconoir:clock',
      colorClass: 'contact-item--schedule',
      label: t('label.schedule'),
      value: contactInfo.value.schedule
    }
  ].filter((item) => item.value)
})

const partnerFields = (partner: PartnerStore) => {
  return [
    {
      icon: 'iconoir:clock',
      label: t('label.schedule'),
      value: partner.schedule,
    },
    {
      icon: 'iconoir:phone',
      label: t('label.phone'),
      value: partner.phone,
    },
    {
      icon: 'iconoir:mail',
      label: t('label.email'),
      value: partner.email,
    },
  ].filter((item) => item.value)
}
</script>

<style src="./contacts.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section class="contacts-section">
    <div class="container">
      <div class="contacts-section__header">
        <h2 class="contacts-section__title">
          {{ t('title.open') }}
        </h2>
        <p class="contacts-section__description">
          {{ t('description') }}
        </p>
      </div>

      <div v-if="contacts.length">
        <div class="contacts-info-card__list">
          <div
            v-for="contact in contacts"
            :key="contact.label"
            class="contact-item"
            :class="contact.colorClass"
          >
            <div class="contact-item__icon">
              <IconCSS :name="contact.icon" />
            </div>
            <div class="contact-item__content">
              <p class="contact-item__label">{{ contact.label }}</p>
              <p class="contact-item__value">{{ contact.value }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="photo__wrapper">
        <nuxt-img
          src="/images/landing/shop/3.jpg"
          sizes="desktop: 1000px"
          class="photo__item photo__item--1"
          alt=""
        />
        <nuxt-img
          src="/images/landing/shop/1.jpg"
          sizes="desktop: 1000px"
          class="photo__item photo__item--2"
          alt=""
        />
        <nuxt-img
          src="/images/landing/shop/2.jpg"
          sizes="desktop: 1000px"
          class="photo__item photo__item--3"
          alt=""
        />
      </div>

      <div v-if="mapSrc" class="map-wrapper">
        <iframe
          :src="mapSrc"
          class="map"
          allowFullScreen
          loading="lazy"
          referrerPolicy="no-referrer-when-downgrade"
        ></iframe>
      </div>

      <div
        v-if="props.showPartners && partners.length"
        :id="PARTNER_STORES_HASH"
        class="partner-stores"
      >
        <div class="partner-stores__header">
          <h3 class="partner-stores__title">{{ t('partners.title') }}</h3>
          <p class="partner-stores__description">{{ t('partners.description') }}</p>
        </div>

        <div class="partner-stores__grid">
          <article
            v-for="partner in partners"
            :key="`${partner.name}-${partner.city}-${partner.address}`"
            class="partner-card"
          >
            <div class="partner-card__top">
              <div>
                <p class="partner-card__city">{{ partner.city }}</p>
                <h4 class="partner-card__name">{{ partner.name }}</h4>
              </div>

              <a
                v-if="partner.mapSrc"
                :href="partner.mapSrc"
                target="_blank"
                rel="noopener noreferrer"
                class="partner-card__map-link"
              >
                {{ t('partners.map') }}
              </a>
            </div>

            <p class="partner-card__address">{{ partner.address }}</p>

            <div v-if="partnerFields(partner).length" class="partner-card__meta">
              <div
                v-for="item in partnerFields(partner)"
                :key="`${partner.name}-${item.label}`"
                class="partner-card__meta-item"
              >
                <IconCSS :name="item.icon" class="partner-card__meta-icon" />
                <div>
                  <p class="partner-card__meta-label">{{ item.label }}</p>
                  <p class="partner-card__meta-value">{{ item.value }}</p>
                </div>
              </div>
            </div>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>
