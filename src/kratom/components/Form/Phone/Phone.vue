<script setup>
import { ref, computed, watch } from 'vue';
import { DEFAULT_PHONE_MASKS } from './phone.config';

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
  region: {
    type: String,
    default: 'ua'
  },
  masks: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:modelValue', 'input', 'focused', 'blured']);

const onFocus = ref(false);
const rawDigits = ref('');
const rawValue = ref('');

const masksMap = computed(() => {
  return {
    ...DEFAULT_PHONE_MASKS,
    ...(props.masks || {})
  };
});

const normalizedRegion = computed(() => {
  return String(props.region || 'ua').trim().toLowerCase();
});

const currentMask = computed(() => {
  return masksMap.value[normalizedRegion.value] ||
    masksMap.value.ua ||
    Object.values(masksMap.value)[0] ||
    null;
});

const defaultPlaceholder = computed(() => {
  return currentMask.value?.placeholder || currentMask.value?.mask || '';
});

const placeholderDinamic = computed(() => {
  return onFocus.value
    ? defaultPlaceholder.value
    : (props.placeholder || defaultPlaceholder.value);
});

const inputMaxLength = computed(() => {
  return currentMask.value?.mask?.length || 19;
});

function countMaskDigits(mask) {
  return (mask || '')
    .split('')
    .filter((char) => char === '#')
    .length;
}

function normalizeDigits(value, maskConfig, { skipStrip } = {}) {
  const digits = (value || '').replace(/\D/g, '');

  if (!maskConfig) {
    return digits;
  }

  const dialDigits = (maskConfig.dialCode || '').replace(/\D/g, '');
  let next = digits;

  if (!skipStrip && dialDigits && next.startsWith(dialDigits)) {
    next = next.slice(dialDigits.length);
  }

  const limit = Number(maskConfig.maxDigits) || countMaskDigits(maskConfig.mask);
  return limit ? next.slice(0, limit) : next;
}

function applyMask(digits, mask) {
  if (!digits) return '';
  if (!mask) return digits;

  let result = '';
  let index = 0;

  for (const char of mask) {
    if (char === '#') {
      if (index >= digits.length) break;
      result += digits[index++];
    } else {
      result += char;
    }
  }

  return result.replace(/[\s\-\(\)]+$/, '');
}

function syncFromDigits(value, options = {}) {
  const digits = normalizeDigits(value, currentMask.value, options);
  rawDigits.value = digits;
  rawValue.value = applyMask(digits, currentMask.value?.mask);
}

function handleInput(e) {
  const digits = e.target.value.replace(/\D/g, '');
  syncFromDigits(digits);
  emit('update:modelValue', rawValue.value);
  emit('input', rawValue.value);
}

function focusHandler() {
  if (props.isDisabled || props.readonly) return;
  onFocus.value = true;
  emit('focused');
}

function blurHandler() {
  onFocus.value = false;
  emit('blured');
}

watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue === rawValue.value) return;
    syncFromDigits(newValue);
  }
);

watch(
  currentMask,
  () => {
    syncFromDigits(rawDigits.value, { skipStrip: true });
    if (rawValue.value || rawDigits.value) {
      emit('update:modelValue', rawValue.value);
    }
  }
);

syncFromDigits(props.modelValue);
</script>


<style src="./phone.scss" lang="sass" scoped />

<template>

  <div
    :class="{
      error: error && error.length,
      disabled: isDisabled,
      required: required
    }"
    class="input__wrapper"
  >
    <input
      :type="type"
      :value="rawValue"
      :placeholder="placeholderDinamic"
      :required="required"
      :readonly="readonly"
      :disabled="isDisabled"
      @input="handleInput"
      @focus="focusHandler"
      @blur="blurHandler"
      :maxlength="inputMaxLength"
      class="main-input"
    />

    <div class="right">
      <slot name="right"></slot>
    </div>
    
    <form-error :error="error"></form-error>
  </div>
</template>
