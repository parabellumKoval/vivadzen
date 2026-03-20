<script>
export default {
  props: {
    modelValue: { type: Number, default: 1 },
    min: { type: Number, default: 1 },
    max: { type: Number, default: 999999 },
    size: { type: String, default: 'medium' }
  },
  data() {
    const initial =
      typeof this.modelValue === 'number' && !isNaN(this.modelValue) ? this.modelValue : this.min
    const clamped = Math.max(this.min, Math.min(initial, this.max))
    return {
      localValue: clamped,
      inputValue: typeof clamped === 'number' && !isNaN(clamped) ? String(clamped) : ''
    }
  },
  watch: {
    modelValue(val) {
      this.localValue = val
      this.inputValue = this.formatValue(val)
    },
    localValue(val) {
      this.$emit('update:modelValue', val)
      this.inputValue = this.formatValue(val)
    }
  },
  methods: {
    minusHandler() {
      const normalized = this.clampValue(this.localValue)
      if (normalized !== this.localValue) {
        this.localValue = normalized
      } else if (normalized > this.min) {
        this.localValue = normalized - 1
      }
    },
    plusHandler() {
      const normalized = this.clampValue(this.localValue)
      if (normalized !== this.localValue) {
        this.localValue = normalized
      } else if (normalized < this.max) {
        this.localValue = normalized + 1
      }
    },
    updateHandler(e) {
      const raw = e.target.value
      this.inputValue = raw
      const parsed = parseInt(raw, 10)
      if (!isNaN(parsed)) {
        this.localValue = parsed
      }
    },
    blurHandler() {
      const normalized = this.clampValue(this.inputValue)
      this.localValue = normalized
      this.inputValue = this.formatValue(normalized)
    },
    clampValue(value) {
      let val = parseInt(value, 10)
      if (isNaN(val)) val = this.min
      if (val < this.min) val = this.min
      if (val > this.max) val = this.max
      return val
    },
    formatValue(value) {
      return typeof value === 'number' && !isNaN(value) ? String(value) : ''
    }
  }
}
</script>

<template>
  <div :class="['amount', size]">
    <button @click="minusHandler" :class="{disabled: localValue <= min}" type="button" class="btn">
      <IconCSS name="fluent:subtract-20-regular" class="icon" />
    </button>
    <input
      :value="inputValue"
      @input="updateHandler"
      @blur="blurHandler"
      type="number"
      :min="min"
      :max="max"
      step="1"
      class="calc-input"
    />
    <button @click="plusHandler" :class="{disabled: localValue >= max}" type="button" class="btn">
      <IconCSS name="fluent:add-20-regular" class="icon" />
    </button>
  </div>
</template>

<style src="./amount.scss" lang="postcss" scoped />
