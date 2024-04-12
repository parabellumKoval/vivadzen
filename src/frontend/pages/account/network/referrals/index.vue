<script setup>
import {useReferralFaker} from '~/composables/fakers/useReferralFaker.ts'
const {t} = useI18n()

definePageMeta({
  subTab: 1
});

const props = defineProps({})

const isLoading = ref(false)
const isInitLoading = ref(false)
const meta = ref(null)

const nextPage = computed(() => {
  return meta.value && meta.value.current_page !== meta.value.last_page
})

const referrals = computed(() => {
  return useReferralFaker()(3)
})

const loadmoreHandler = () => {}
// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./referrals.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div>
    <simple-table v-if="isInitLoading || (referrals.length && !isInitLoading)">
      <template v-if="!isInitLoading">
        <referral-card
          v-for="(referral, index) in referrals"
          :key="referral.id"
          :item="referral"
          class="order-card"
        >
        </referral-card>
      </template>
      <template v-else>
        <referral-card-skeleton
          v-for="(item, index) in 6"
          :key="item"
        >
        </referral-card-skeleton>
      </template>
    </simple-table>
    <div v-else >{{ t('messages.no_results') }}</div>

    <div
      v-if="nextPage || isLoading"
      class="load-more-wrapper"
    >
      <button
        @click="loadmoreHandler"
        :class="{loading: isLoading}"
        class="button secondary"
        type="button"
      >
        <span>{{ $t('btn.load_more') }}</span>
      </button>
    </div>
  </div>
</template>