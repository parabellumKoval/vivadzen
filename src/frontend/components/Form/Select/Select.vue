<script setup>
const { t } = useI18n()
const isActive = ref(false) 

const props = defineProps({
    nullable: {
      type: Boolean,
      default: true
    },
    modelValue: {
      type: String
    },
    placeholder: {
      type: String,
    },
    required: {
      type: Boolean,
      default: false
    },
    values: {
      type: [Array, Object],
      required: true
    },
    error: {
      type: [Object, Array, String, Boolean],
      default: false
    },
    keyName: {
      type: String,
      default: null
    },
    valueName: {
      type: String,
      default: null
    }
})

const emit = defineEmits([
  'update:modelValue'
])

// COMPUTEDS
const currentValue = computed(() => {
  const value = findValueByKey(props.modelValue)
  const valueSting = value? value: t('please_select')
  console.log('currentValue', value, valueSting)
  return valueSting
})

const id = computed(() => {
  return 'select-' + (Math.random() + 1).toString(36).substring(7);
})

// HANDLERS
const selectHandler = (val) => {
  const key = val? getKey(val): null
  emit('update:modelValue', key)
}
const focusHandler = () => {
  isActive.value = true
}
const blurHandler = (v) => {
  isActive.value = false
}

// METHODS
const findValueByKey = (key) => {
  if(!props.valueName)
    return key

  const item = props.values.find((item) => {
    return item[props.keyName] === key
  })

  if(!item)
    return null

  return getValue(item)
}

const getValue = (item) => {
  if(props.valueName)
    return item[props.valueName]
  else
    return item
}

const getKey = (item) => {
  if(props.keyName)
    return item[props.keyName]
  else
    return item
}

// COMPUTED
const mobileValues = computed(() => {
  const items = props.values.map((item) => {
    return {
      id: getKey(item),
      title: getValue(item),
      callback: selectHandler
    }
  })

  return [
    {
      id: null,
      title: t('please_select'),
      callback: selectHandler
    },
    ...items
  ]
})
</script>

<style src="./select.scss" lang="sass" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div :class="{active: isActive}" class="select-wrapper">
    <input
      :value="currentValue"
      @focus="focusHandler"
      @blur="blurHandler"
      :id="id"
      type="text"
      class="main-input"
      readonly 
      required
    > 

    <IconCSS name="ph:caret-down" size="14" class="icon"></IconCSS>

    <form-error :error="error"></form-error>

    <!-- Show this only on mobile -->
    <modal-transition :is-show="isActive" class="dropdown-mobile">
      <modal-wrapper :title="placeholder">
        <simple-list-icon :items="mobileValues" class="list"></simple-list-icon>
      </modal-wrapper>
    </modal-transition>
    
    <!-- This dropdown for desktop and tablet -->
    <div class="dropdown" scrollable>
      
      <div
        v-if="nullable"
        @mousedown="selectHandler(null)"
        clickable
        class="item"
      >
        <div class="text">{{ t('please_select') }}</div>
      </div>

      <div
        v-for="value in values"
        :key="getKey(value)"
        @mousedown="selectHandler(value)"
        :class="{active: modelValue === getKey(value)}"
        clickable
        class="item"
      >
        <div class="text">{{ getValue(value) }}</div>
      </div>

    </div>
  </div>
</template>