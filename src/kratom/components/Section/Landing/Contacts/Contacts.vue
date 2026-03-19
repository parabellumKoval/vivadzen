<script setup lang="ts">
const { t } = useI18n()
const { get } = useSettings()

const contactInfo = computed(() => {
  return {
    address: get('site.contacts.address'),
    phone: get('site.contacts.phone'),
    email: get('site.contacts.email'),
    schedule: get('site.contacts.schedule'),
    map: get('site.contacts.map')
  }
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
  ]
})
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

      <div>
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

      <div class="map-wrapper">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2560.1!2d14.4378!3d50.0755!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNTDCsDA0JzMxLjgiTiAxNMKwMjYnMTYuMSJF!5e0!3m2!1sru!2scz!4v1234567890"
          class="map"
          allowFullScreen
          loading="lazy"
          referrerPolicy="no-referrer-when-downgrade"
        ></iframe>
      </div>
      
    </div>
  </section>
</template>
