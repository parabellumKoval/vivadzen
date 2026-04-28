<script setup lang="ts">
import type { PartnerStore } from '../../../../composables/usePartnerStores'
import { PARTNER_STORES_HASH } from '../../../../composables/usePartnerStores'

const { t } = useI18n()
const props = withDefaults(defineProps<{
  showPartners?: boolean
}>(), {
  showPartners: false,
})

const contactInfo = useContacts()
const { partners } = usePartnerStores()

const mapLocations = computed(() => contactInfo.mapLocations.value)

const contacts = computed(() => {
  return [
    // {
    //   icon: 'iconoir:map',
    //   colorClass: 'contact-item--address',
    //   label: t('label.address'),
    //   value: contactInfo.addressSummary.value,
    //   lines: contactInfo.addressLines.value
    // },
    {
      icon: 'iconoir:phone',
      colorClass: 'contact-item--phone',
      label: t('label.phone'),
      value: contactInfo.phone.value
    },
    {
      icon: 'iconoir:mail',
      colorClass: 'contact-item--email',
      label: t('label.email'),
      value: contactInfo.email.value
    },
    // {
    //   icon: 'iconoir:clock',
    //   colorClass: 'contact-item--schedule',
    //   label: t('label.schedule'),
    //   value: contactInfo.scheduleSummary.value,
    //   lines: contactInfo.scheduleLines.value
    // }
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
              <p class="contact-item__value">
                <template v-if="contact.lines?.length">
                  <template v-for="(line, index) in contact.lines" :key="`${contact.label}-${index}`">
                    <template v-if="index"><br></template>{{ line }}
                  </template>
                </template>
                <template v-else>
                  {{ contact.value }}
                </template>
              </p>
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

      <div v-if="mapLocations.length" class="map-list">
        <div
          v-for="location in mapLocations"
          :key="location.id"
          class="map-card"
        >
          <p class="map-card__title">{{ location.title || location.address }}</p>
          <p v-if="location.title" class="map-card__subtitle">{{ location.address }}</p>
          <p v-if="location.schedule" class="map-card__schedule">{{ location.schedule }}</p>
          <div class="map-wrapper">
            <iframe
              :src="location.mapSrc"
              class="map"
              allowFullScreen
              loading="lazy"
              referrerPolicy="no-referrer-when-downgrade"
            ></iframe>
          </div>
        </div>
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
