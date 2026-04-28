<script setup>
const {t} = useI18n()
const { messengers, networks } = useSocial()
const { phone, email, pickupLocations, map, addressSummary } = useContacts()

</script>

<style src="./contacts.scss" lang="scss" scoped />
<!-- <i18n src="./lang.yaml" lang="yaml"></i18n> -->

<template>
  <modal-wrapper :title="t('title.contacts')">
    <div class="contacts">
      <div>
        <div class="social">
          <div v-if="messengers.length" class="social-group">
            <div class="social-group-title">{{ t('label.messengers') }}</div>
            <div class="social-group-hint">{{ t('label.messengers_consultation_hint') }}</div>
            <a v-for="messenger in messengers" :key="messenger.id" :href="messenger.link" :class="messenger.key + '-bg'" class="button social-btn contacts-btn">
              <IconCSS :name="messenger.icon" class="icon"></IconCSS>
              <span class="text">{{ messenger.name }}</span>
            </a>
          </div>
          <div v-if="networks.length" class="social-group">
            <div class="social-group-title">{{ t('label.social_networks') }}</div>
            <a v-for="network in networks" :key="network.id" :href="network.link" :class="network.key + '-bg'" class="button social-btn contacts-btn">
              <IconCSS :name="network.icon" class="icon"></IconCSS>
              <span class="text">{{ network.name }}</span>
            </a>
          </div>
        </div>
        <div class="phones">
          <a href="/" class="button color-dark contacts-btn">
            <IconCSS name="iconoir:phone" class="icon"></IconCSS>
            <span class="text">{{ phone }}</span>
          </a>
          <a href="/" class="button color-dark contacts-btn email-btn">
            <IconCSS name="iconoir:mail-opened" class="icon"></IconCSS>
            <span class="text">{{ email }}</span>
          </a>
        </div>
      </div>
      <div class="divide"></div>
      <div class="map">
        <template v-if="pickupLocations.length">
          <div
            v-for="location in pickupLocations"
            :key="location.id"
            class="map-item"
          >
            <div class="map-title">{{ location.title || location.address }}</div>
            <div v-if="location.title" class="map-address">{{ location.address }}</div>
            <div v-if="location.schedule" class="map-schedule">{{ location.schedule }}</div>
            <div v-if="location.map" class="map-container" v-html="location.map"></div>
          </div>
        </template>
        <template v-else-if="map">
          <div class="map-title">{{ addressSummary }}</div>
          <div class="map-container" v-html="map"></div>
        </template>
      </div>
    </div>
  </modal-wrapper>
</template>
