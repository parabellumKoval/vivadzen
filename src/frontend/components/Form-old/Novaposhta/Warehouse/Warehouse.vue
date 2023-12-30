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

const emit = defineEmits(['selected', 'update:modelValue'])

const results = ref([])
const searchWarehouse = ref('')
const isLoading = ref(false)


const getWarehouses = async (city, search = '') => {
  isLoading.value = true
  return await useNovaposhtaStore().getWarehouses(city, search).then((res) => {
    if(res.data && res.data.length){
      results.value = res.data
    }else {
      results.value = []
    }

    return res.data
  }).finally(() => {
    isLoading.value = false
  })
}

// WATCH
watch(() => props.city, (val) => {
  getWarehouses(val, searchWarehouse.value)
})

// COMPUTED
const postOffices = computed(() => {
  return results.value && results.value.map((item) => {
    return item.Description
  })
})

// HANDLERS
const updateSearchWarehouseHandler = (v) => {
  searchWarehouse.value = v
  getWarehouses(props.city, v)
}

const updateModelValueHandler = (v) => {
  getWarehouses(props.city, v).then((res) => {
    if(res && res.length === 1)
      emit('selected', res[0])
  })
  emit('update:modelValue', v)
}

</script>
<style src="./warehouse.scss" lang="scss" scoped />

<template>
  <div class="warehouse">
    <transition name="scale-x">
      <simple-loader v-if="isLoading"></simple-loader>
    </transition>

    <form-dropdown
      :model-value = "modelValue"
      @update:modelValue="updateModelValueHandler"
      :search-value = "searchWarehouse"
      @update:searchValue = "updateSearchWarehouseHandler"
      :values = "postOffices"
      :placeholder="$t('form.warehouse')"
      :error="error"
      required
    >
    </form-dropdown>
  </div>
</template>
