<script setup>

const { t } = useI18n()

const v = ref(null)

const props = defineProps({
  modelValue: {
    required: true
  },
  value: {
    type: String
  },
  name: {
    type: String
  }
})

const emit = defineEmits([
  'update:modelValue'
])

const updateHandler = (v) => {
  emit('update:modelValue', v)
}

watch(() => props.modelValue, (value) => {
  v.value = value
}, {
  immediate: true
})

const isChecked = computed(() => {
  return props.modelValue === props.value
})

</script>

<style src="./block.scss" lang="scss" scoped />

<template>
  <div :class="{active: isChecked}" class="block">
    <form-radio v-model="v" :name="name" :value="value" @update:modelValue="updateHandler" class="form-radio">
      <slot />
    </form-radio>
  </div>
</template>