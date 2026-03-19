
<script setup lang="ts">
import {useCartStore} from '~/store/cart'
import { usePacketa } from '#imports'


const {t} = useI18n()

const { pickPudo } = usePacketa()
const point = ref<any>(null)
const {order, setDeliveryFields, isFieldRequired} = useCartStore()

async function choose () {
  point.value = await pickPudo()
}

const setDeliveryDataToOrder = (data) => {
  setDeliveryFields({
    method: 'packeta_warehouse',
    region: data.country,
    warehouse: data.name, 
    settlement: data.city,
    street: data.street,
    zip: data.zip
  })
}

watch(point, (newPoint) => {
  if(newPoint) {
    setDeliveryDataToOrder(newPoint)
  }
})
</script>

<style src='./warehouse.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div class="warehouse">
    <button class="button small primary" @click="choose">{{ t('chose') }}</button>

    <div v-if="point" class="warehouse-point">
      <div class="warehouse-point-name">{{ point.name }}</div>
      <div class="warehouse-point-info">[{{ point.street }}, {{ point.city }} {{ point.zip }}]</div>
    </div>
  </div>
</template>

