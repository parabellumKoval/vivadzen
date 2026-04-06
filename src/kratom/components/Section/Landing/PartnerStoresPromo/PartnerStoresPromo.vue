<script setup lang="ts">
import { PARTNER_STORES_HASH } from '../../../../composables/usePartnerStores'

const { t } = useI18n()
const regionPath = useToLocalePath()
const { uniqueCities } = usePartnerStores()

const partnerStoresLink = computed(() => regionPath(`/contacts#${PARTNER_STORES_HASH}`))
const citiesText = computed(() => uniqueCities.value.join(', '))
</script>

<style src="./partner-stores-promo.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section v-if="uniqueCities.length" class="partner-stores-promo">
    <div class="container">
      <div class="partner-stores-promo__card">
        <div class="partner-stores-promo__content">
          <p class="partner-stores-promo__eyebrow">{{ t('eyebrow') }}</p>
          <h3 class="partner-stores-promo__title">{{ t('title') }}</h3>
          <p class="partner-stores-promo__description">
            {{ t('description') }}
          </p>
          <p class="partner-stores-promo__cities">
            {{ t('citiesPrefix') }}
            <strong>{{ citiesText }}</strong>
          </p>
        </div>

        <NuxtLink :to="partnerStoresLink" class="partner-stores-promo__button">
          {{ t('button') }}
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
