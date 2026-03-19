<script setup>
const props = defineProps({
  modelValue: {
    type: [String,  Number, Boolean]
  },
  items: {
    type: Array
  },
  error: {
    type: [Object, Array, String, Boolean],
    default: false
  }
})

const emit = defineEmits([
  'update:modelValue'
])

const { t } = useI18n()

const errorMessage = computed(() => {
  const value = props.error

  if (!value) {
    return null
  }

  if (Array.isArray(value)) {
    return value[0] || null
  }

  if (typeof value === 'object') {
    const firstKey = Object.keys(value)[0]
    if (!firstKey) {
      return null
    }

    const nestedError = value[firstKey]

    if (Array.isArray(nestedError)) {
      return nestedError[0] || null
    }

    return nestedError || null
  }

  return value
})

const hasError = computed(() => Boolean(errorMessage.value))

const selectHandler = (v) => {
  emit('update:modelValue', v.key)
}

const hasPrice = (item) => {
  const price = item?.price
  return price !== undefined && price !== null && price !== ''
}

const isPriceObject = (item) => {
  const price = item?.price
  return Boolean(item?.isPriceObject && price && typeof price === 'object' && price.amount !== undefined)
}
</script>

<style src="./tabs.scss" lang="scss" scoped />

<template>
  <div class="tabs-field">
    <div
      :class="{ error: hasError }"
      class="tabs-wrapper"
    >
      <button
        v-for="item in items"
        :key="item.key"
        @click="selectHandler(item)"
        :class="{active: item.key === modelValue}"
        class="tab"
      >
        <template v-if="item.image">
          <nuxt-img
            :src = "item.image"
            :provider = "useImg().provider"
            width="100"
            height="20"
            sizes = "mobile:130px"
            quality = "90"
            loading = "lazy"
            fit="outside"
            class="tab-image"
          >
          </nuxt-img> 
        </template>
        <template v-if="item.title">
          {{ item.title }}
        </template>
        <div v-if="hasPrice(item)" class="tab-price">
          <template v-if="isPriceObject(item)">
            {{ t('delivery.from') }} <simple-price :value="item.price.amount" :currency-code="item.price.currency" />
          </template>
          <template v-else>
            {{ item.price }}
          </template>
        </div>
      </button>
    </div>
    <p v-if="hasError && errorMessage" class="tabs-error" v-html="errorMessage"></p>
  </div>
</template>
