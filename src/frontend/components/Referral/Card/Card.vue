<script setup>
const {t} = useI18n()

const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})

// COMPUTED
const photo = computed(() => {
  return props.item.user.photo? props.item.user.photo: '/images/avatars/no.png'
})
</script>

<style src="./card.scss" lang="scss" scoped />

<template>
  <simple-table-row :show-details="item.referrals.length" grid="50px repeat(2, 1fr) 150px 100px  min-content" class="referral-row">
    <template v-slot:columns>
      <simple-table-column>
        <nuxt-img
          v-if="photo"
          :src = "photo"
          width="50"
          height="50"
          sizes = "mobile:50px tablet:50px desktop:50px"
          format = "webp"
          quality = "60"
          loading = "lazy"
          fit="outside"
          class="avatar-image"
        />
      </simple-table-column>
      <simple-table-column :label="t('form.name')" :value="item.user.name"></simple-table-column>
      <simple-table-column :label="t('form.email')" :value="item.user.email"></simple-table-column>
      <simple-table-column :label="t('form.phone')" :value="item.user.phone"></simple-table-column>
      <simple-table-column :label="t('form.income')">
        <simple-price :value="item.income"></simple-price>
      </simple-table-column>
      <simple-table-column :label="t('form.referrals')" :value="item.referrals.length"></simple-table-column>
    </template>
    <template v-slot:details>
      <div class="detailes-box">
       ----
      </div>
    </template>
  </simple-table-row>
</template>