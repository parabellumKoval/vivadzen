<script setup>
import {useNovaposhtaStore} from '~/store/novaposhta'
const {t} = useI18n()

const props = defineProps({
  modelValue: {
    type: String,
    default: null
  },
  error: {
    type: [Object, Array, String, Boolean],
    default: false
  }
})

const emit = defineEmits([
  'update:modelValue',
  'update:ref'
])

const results = ref([])
const searchSettlement = ref(null)
const isLoading = ref(false)

const getSettlements = async (search) => {
  if(search.length > 0) {
    isLoading.value = true
    await useNovaposhtaStore().getSettlements(search).then((res) => {
      if(res.data && res.data.length){
        results.value = res.data
      }else {
        results.value = []
      }
    }).finally(() => {
      isLoading.value = false
    })
  }
}

const updateModelValue = (v) => {
  const searched = cities.value.find((item) => {
    return item.key === v
  })

  if(searched !== -1) {
    emit('update:modelValue', searched.value)
    emit('update:ref', searched.key)
  }
}

watch(searchSettlement, (val) => {
  getSettlements(val)
})

// COMPUTED
const cities = computed(() => {
  return results.value && results.value.map((item) => {
    return {
      value: `${item.Description} (${item.AreaDescription})`,
      key: item.Ref
    }
  })
})

</script>
<style src="./settlement.scss" lang="scss" scoped />

<template>
  <div class="settlement">
    <transition name="scale-x">
      <simple-loader v-if="isLoading"></simple-loader>
    </transition>

    <form-dropdown
      :model-value = "modelValue"
      @update:modelValue = "updateModelValue"
      v-model:search-value = "searchSettlement"
      :values = "cities"
      :placeholder="$t('form.settlement')"
      :min-symbols="1"
      list-value="value"
      list-key="key"
      :error="error"
      required
    >
    </form-dropdown>
  </div>
</template>
