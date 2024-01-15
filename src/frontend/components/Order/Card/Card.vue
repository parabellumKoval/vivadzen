<script setup>
const {t} = useI18n()

const props = defineProps({
  order: {
    type: Object,
    required: true
  }
})

// COMPUTED
const user = computed(() => {
  const string = {
    value: ''
  }

  string.value += props.order.user.firstname ? props.order.user.firstname + ' ': ''
  string.value += props.order.user.lastname ? props.order.user.lastname: ''
  string.value += props.order.user.phone ? ', ' + props.order.user.phone: ''
  string.value += props.order.user.email ? ', ' + props.order.user.email: ''

  return string.value.length && string.value || null
})

const delivery = computed(() => {
  const string = {
    value: ''
  }

  string.value += props.order.delivery.method ? t(`form.delivery.${props.order.delivery.method}`): ''
  string.value += props.order.delivery.city ? ', ' + props.order.delivery.city: ''
  string.value += props.order.delivery.warehouse ? ', ' + props.order.delivery.warehouse: ''
  string.value += props.order.delivery.address ? ', ' + props.order.delivery.address: ''
  string.value += props.order.delivery.zip ? ', ' + props.order.delivery.zip: ''

  return string.value.length && string.value || null
})

const payment = computed(() => {
  const string = {
    value: ''
  }

  string.value += props.order.payment.method ? t(`form.payment.${props.order.payment.method}`): ''

  return string.value.length && string.value || null
})

</script>
<style src="./card.scss" lang="scss" scoped />

<template>
  <simple-table-row>
    <template v-slot:columns>
      <simple-table-column :label="t('number')" :value="order.code"></simple-table-column>
      <simple-table-column :label="t('created_at')">
        {{ $d(order.created_at, 'short') }}
      </simple-table-column>
      <simple-table-column :label="t('status')">
        <span :class="order.status" class="status">{{ $t(`status.${order.status}`) }}</span>
      </simple-table-column>
      <simple-table-column :label="t('amount')">
        <simple-price :value="order.price"></simple-price>
      </simple-table-column>
    </template>
    <template v-slot:details>
      <div class="detailes-box">
        <div class="order-products">
          <product-card-checkout-static
            v-for="(product, index) in order.products"
            :key="product.id"
            :item="product"
            class="product"
          >
          </product-card-checkout-static>
        </div>
        <div class="order-meta">
          <div v-if="delivery" class="order-meta__item">
            <div class="label">{{ t('delivery') }}</div>
            <div class="value">{{ delivery }}</div>
          </div>
          <div v-if="payment" class="order-meta__item">
            <div class="label">{{ t('payment') }}</div>
            <div class="value">{{ payment }}</div>
          </div>
          <div v-if="user" class="order-meta__item">
            <div class="label">{{ t('recipient') }}</div>
            <div class="value">{{ user }}</div>
          </div>
          <div v-if="order.comment" class="order-meta__item">
            <div class="label">{{ t('comment') }}</div>
            <div class="value">{{ order.comment }}</div>
          </div>
        </div>
      </div>
    </template>
  </simple-table-row>
</template>

<i18n src="./lang.yaml" lang="yaml"></i18n>