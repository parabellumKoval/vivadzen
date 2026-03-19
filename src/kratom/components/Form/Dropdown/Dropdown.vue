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
  listValue: {
    type: String
  },
  listKey: {
    type: String
  },
  minSymbols: {
    type: Number,
    default: 0
  },
  placeholder: {
    type: String,
    required: true
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
  required: {
    type: Boolean,
    default: false
  },
  showOnEmpty: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'update:modelValue',
  'update:searchValue'
])

const inputElement = ref(null)
const isActive = ref(false)
const selectedValue = ref(null)
const isEditing = ref(false)
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

const isListVisible = computed(() => {
  if (!isListActive.value) return false
  const hasSearch = !!props.searchValue && props.searchValue.length > 0
  if (hasSearch) return true
  return props.showOnEmpty && props.values && props.values.length > 0
})

const searchOrModalValue = computed(() => {
  if (isEditing.value) {
    return props.searchValue || ''
  }

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
const updateSearchValueHandler = (value) => {
  if (!props.isDisabled) {
    isActive.value = true
  }
  isEditing.value = true
  emit('update:searchValue', value)
}

const selectHandler = (index) => {
  const selectedItem = props.values?.[index]

  if(props.listValue && props.listKey) {
    emit('update:modelValue', props.values[index][props.listKey], selectedItem)
  }else {
    emit('update:modelValue', props.values[index], selectedItem)
  }

  isEditing.value = false
  emit('update:searchValue', '')
  closeHandler()
} 

const blurHandler = () => {
  isEditing.value = false
  setTimeout(() => {
    closeHandler()
    emit('update:searchValue', '')
  }, 100)
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
  // console.log('props.modelValue', val)
  selectedValue.value = val
})
</script>

<style src="./dropdown.scss" lang="scss" scoped />

<template>
  <div class="dropdown">
    <form-text
      :model-value="searchOrModalValue"
      @update:modelValue="updateSearchValueHandler"
      @focused="openHandler"
      @blured="blurHandler"
      :placeholder="placeholder"
      :required="required"
      :error="error"
      :is-disabled="isDisabled"
      ref="ignoreElRef"
    >
      <!-- <template v-slot:icon-right>
        <button :class="{active: isActive}" @click="toggleHandler" class="chevron">
          <IconCSS name="fluent:chevron-down-48-filled" size="20px" class="icon"></IconCSS>
        </button>
      </template> -->

    </form-text>

    <transition name="move-y">
      <div v-if="isListVisible" class="list-wrapper" scrollable ref="listRef">
        <ul v-if="values && values.length" class="list">
          <li
            v-for="(item, index) in values"
            :key="index"
            class="item"
          >
            <button @mousedown.prevent="selectHandler(index)" clickable class="item-btn">{{ getItemValue(index) }}</button>
          </li>
        </ul>
        <div v-else class="no-results">
          {{ $t('messages.no_results') }}
        </div>
      </div>
    </transition>

  </div>
</template>
