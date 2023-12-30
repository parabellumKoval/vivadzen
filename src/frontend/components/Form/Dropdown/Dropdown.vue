<script setup>
const {t} = useI18n()

const props = defineProps({
  modelValue: {
    type: String
  },
  searchValue: {
    type: String
  },
  values: {
    type: [Object, Array]
  },
  placeholder: {
    type: String,
    required: true
  },
  required: {
    type: Boolean,
    default: false
  },
  error: {
    type: [Object, Array, String, Boolean],
    default: false
  },
  isDisabled: {
    type: Boolean,
    default: false
  },
  isLoading: {
    type: Boolean,
    default: false
  },
  listValue: {
    type: String
  },
  listKey: {
    type: String
  },
  minSymbols: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits([
  'update:modelValue',
  'update:searchValue'
])

const inputElement = ref(null)
const isActive = ref(false)
const selectedValue = ref(null)
const inputLag = ref(false)
const listRef = ref()
const ignoreElRef = ref()

// COMPUTED
const isListActive = computed(() => {
  return isActive.value
  // if(props.minSymbols)
  //   return props.searchValue?.length >= props.minSymbols && isActive.value
  // else
  //   return isActive.value
})

const searchOrModalValue = computed(() => {
  return props.modelValue || props.searchValue
})

// METHODS
const getItemValue = (index) => {
  if(props.listValue && props.listKey)
    return props.values[index][props.listValue]
  else
    return props.values[index]
}

// HANDLERS
const changeHandler = (value) => {
  clearTimeout(inputLag.value)
  inputLag.value = setTimeout(() => {
    emit('update:searchValue', value)
  }, 500)
}

const selectHandler = (index) => {
  if(props.listValue && props.listKey)
    emit('update:modelValue', props.values[index][props.listKey])
  else
    emit('update:modelValue', props.values[index])

  closeHandler()
} 

const openHandler = () => {
  isActive.value = true
}

const closeHandler = () => {
  isActive.value = false
}

const toggleHandler = () => {
  isActive.value = !isActive.value
}

// onClickOutside(listRef, closeHandler, {ignore: [ignoreElRef]})

watch(() => props.modelValue, (val) => {
  selectedValue.value = val
})
</script>

<style src="./dropdown.scss" lang="scss" scoped />

<template>
  <div class="dropdown">
    <form-text
      :model-value="searchOrModalValue"
      @update:modelValue="changeHandler"
      @focused="openHandler"
      :placeholder="placeholder"
      :required="required"
      :error="error"
      :is-disabled="isDisabledOrSelected"
      ref="ignoreElRef"
    >
      <template v-slot:icon-right>
        <button :class="{active: isActive}" @click="toggleHandler" class="chevron">
          <IconCSS name="fluent:chevron-down-48-filled" size="20px" class="icon"></IconCSS>
        </button>
      </template>

    </form-text>

    <transition name="move-y">
      <div v-if="isActive" class="list-wrapper" scrollable ref="listRef">
        <ul v-if="values && values.length" class="list">
          <li
            v-for="(item, index) in values"
            :key="index"
            @click="selectHandler(index)"
            class="item"
            clickable
          >
            <IconCSS name="fluent:chevron-right-48-filled" size="14px" class="icon"></IconCSS>
            <span>{{ getItemValue(index) }}</span>
          </li>
        </ul>
        <div v-else class="no-results">
          {{ $t('messages.no_results') }}
        </div>
      </div>
    </transition>

  </div>
</template>