<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import type { PropType } from 'vue'
import { DEFAULT_PHONE_MASKS } from '../Phone/phone.config'
import type { PhoneMaskConfig } from '../Phone/phone.config'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
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
  readonly: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'tel'
  },
  regions: {
    type: Array as PropType<string[]>,
    default: () => (['ua', 'cz', 'de', 'es'])
  },
  masks: {
    type: Object as PropType<Record<string, PhoneMaskConfig>>,
    default: () => ({})
  },
  region: {
    type: String as PropType<string | null>,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'update:region', 'input', 'focused', 'blured'])

const regionStore = useRegion()

const containerRef = ref(null)
const isOpen = ref(false)
const userSelected = ref(false)

const masksMap = computed(() => ({
  ...DEFAULT_PHONE_MASKS,
  ...(props.masks || {})
}))

const normalizeRegionCode = (value?: string | null) => {
  return String(value || '').trim().toLowerCase()
}

const allowedRegions = computed(() => {
  const list = Array.isArray(props.regions) ? props.regions : []
  return list
    .map((code) => normalizeRegionCode(code as string))
    .filter((code) => Boolean(masksMap.value[code]))
})

const fallbackRegion = computed(() => {
  return allowedRegions.value[0] || Object.keys(masksMap.value)[0] || 'ua'
})

const resolveInitialRegion = () => {
  const fromProp = normalizeRegionCode(props.region)
  if (fromProp && allowedRegions.value.includes(fromProp)) {
    return fromProp
  }

  const fromStore = normalizeRegionCode(regionStore.region.value)
  if (allowedRegions.value.includes(fromStore)) {
    return fromStore
  }

  return fallbackRegion.value
}

const selectedRegion = ref(resolveInitialRegion())

const options = computed(() => {
  return allowedRegions.value.map((code) => {
    const meta = regionStore.regionsMeta?.[code]
    const mask = masksMap.value[code]

    return {
      code,
      name: meta?.name || code.toUpperCase(),
      flagClass: meta?.flagClass || '',
      dialCode: mask?.dialCode || ''
    }
  })
})

const selectedOption = computed(() => {
  return options.value.find((item) => item.code === selectedRegion.value) || options.value[0] || null
})

const closeDropdown = () => {
  isOpen.value = false
}

const toggleDropdown = () => {
  if (props.isDisabled || props.readonly) return
  isOpen.value = !isOpen.value
}

const selectRegion = (code: string) => {
  if (!code || code === selectedRegion.value) {
    closeDropdown()
    return
  }

  selectedRegion.value = code
  userSelected.value = true
  emit('update:region', code)
  closeDropdown()
}

const handleOutsideClick = (event: MouseEvent) => {
  if (!containerRef.value || !(event.target instanceof Node)) return
  if (!containerRef.value.contains(event.target)) {
    closeDropdown()
  }
}

const updateFromProps = () => {
  const next = resolveInitialRegion()
  if (next !== selectedRegion.value) {
    userSelected.value = false
    selectedRegion.value = next
  }
}

const forwardInput = (value: string) => {
  emit('update:modelValue', value)
  emit('input', value)
}

const focusHandler = () => {
  emit('focused')
}

const blurHandler = () => {
  closeDropdown()
  emit('blured')
}

watch(
  () => props.region,
  (next) => {
    const normalized = normalizeRegionCode(next)
    if (normalized && normalized !== selectedRegion.value && allowedRegions.value.includes(normalized)) {
      userSelected.value = false
      selectedRegion.value = normalized
    }
  }
)

watch(
  () => regionStore.region.value,
  (next) => {
    if (userSelected.value || props.region) return
    const normalized = normalizeRegionCode(next)
    if (normalized && allowedRegions.value.includes(normalized)) {
      selectedRegion.value = normalized
    }
  }
)

watch(allowedRegions, () => {
  if (!allowedRegions.value.includes(selectedRegion.value)) {
    updateFromProps()
  }
})

onMounted(() => document.addEventListener('click', handleOutsideClick))
onBeforeUnmount(() => document.removeEventListener('click', handleOutsideClick))
</script>

<style src="./phone-region.scss" lang="scss" scoped></style>

<template>
  <div class="phone-region" ref="containerRef">
    <button
      type="button"
      class="phone-region__selector"
      :class="{ open: isOpen, disabled: isDisabled || readonly }"
      @click="toggleDropdown"
      :disabled="isDisabled || readonly"
    >
      <Icon v-if="selectedOption?.flagClass" :name="selectedOption.flagClass" class="phone-region__flag"></Icon>
      <span class="phone-region__dial">{{ selectedOption?.dialCode || '+' }}</span>
      <IconCSS name="fluent:chevron-down-48-filled" size="18px" class="phone-region__chevron"></IconCSS>
    </button>

    <form-phone
      :model-value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :error="error"
      :is-disabled="isDisabled"
      :readonly="readonly"
      :type="type"
      :region="selectedRegion"
      :masks="masksMap"
      @update:modelValue="forwardInput"
      @focused="focusHandler"
      @blured="blurHandler"
    ></form-phone>

    <transition name="move-y">
      <div v-if="isOpen" class="phone-region__dropdown">
        <button
          v-for="option in options"
          :key="option.code"
          type="button"
          class="phone-region__option"
          :class="{ active: option.code === selectedRegion }"
          @click="selectRegion(option.code)"
        >
          <Icon v-if="option.flagClass" :name="option.flagClass" class="phone-region__flag"></Icon>
          <div class="phone-region__option-label">
            <div class="phone-region__option-name">{{ option.name }}</div>
            <div class="phone-region__option-dial">{{ option.dialCode }}</div>
          </div>
        </button>
      </div>
    </transition>
  </div>
</template>
