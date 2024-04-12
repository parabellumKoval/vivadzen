<script setup>
import {useNovaposhtaStore} from '~/store/novaposhta'
const {t} = useI18n()

const props = defineProps({
  city: {
    type: String,
    default: null
  },
  modelValue: {
    type: String,
    default: null
  },
  error: {
    type: [Object, Array, String, Boolean],
    default: false
  }
})

const emit = defineEmits(['selected', 'update:modelValue', 'update:ref'])

const results = ref([])
const searchStreet = ref('')
const isLoading = ref(false)


const getStreets = async (city, search = '') => {
  isLoading.value = true
  return await useNovaposhtaStore().getStreets(city, search).then((res) => {
    if(res.data && res.data[0] && res.data[0]['Addresses'] && res.data[0]['Addresses'].length){
      results.value = res.data[0]['Addresses']
    }else {
      results.value = []
    }

    return res.data[0]
  }).finally(() => {
    isLoading.value = false
  })
}

// WATCH
watch(() => props.city, (val) => {
  getStreets(val, searchStreet.value)
})

// COMPUTED
const streets = computed(() => {
  return results.value && results.value.map((item) => {
    return {
      value: item.Present,
      key: item.SettlementStreetRef
    }
  })
})

// HANDLERS
const updateSearchStreetHandler = (v) => {
  searchStreet.value = v
  getStreets(props.city, v)
}

const updateModelValueHandler = (ref) => {
  const searched = results.value.find((item) => {
    return item.SettlementStreetRef === ref
  })

  getStreets(props.city, searched.Present).then((res) => {
    if(res && res.length === 1)
      emit('selected', res[0])
  })
  
  emit('update:modelValue', searched.Present)
  emit('update:ref', searched.SettlementStreetRef)
}

</script>
<style src="./street.scss" lang="scss" scoped />

<template>
  <div class="warehouse">
    <transition name="scale-x">
      <simple-loader v-if="isLoading"></simple-loader>
    </transition>

    <form-dropdown
      :model-value = "modelValue"
      @update:modelValue="updateModelValueHandler"
      :search-value = "searchStreet"
      @update:searchValue = "updateSearchStreetHandler"
      :values = "streets"
      :placeholder="$t('form.delivery.street')"
      :error="error"
      list-value="value"
      list-key="key"
      required
    >
    </form-dropdown>
  </div>
</template>
