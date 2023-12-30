<script>
export default {
  data() {
    return {}
  },

  props: {
    placeholder: {
      type: String,
      required: true
    },
    isRequired: {
      type: Boolean,
      default: false
    },
    isActive: {
      type: Boolean,
      default: false
    },
    isDisabled: {
      type: Boolean,
      default: false
    },
    targetId: {
      type: String,
      default: false
    }
  }
}
</script>

<style src="./placeholder.scss" lang="sass" scoped />

<template>
  <label :class="[{active: isActive}, {disabled: isDisabled}]" :for="targetId" class="placeholder">
    <span class="placeholder__before"></span>
    <span class="placeholder__text">
      <span class="inner-text">
        {{ placeholder }}
        <span v-if="isRequired">*</span>
      </span>
    </span>
    <span class="placeholder__after"></span>
  </label>
</template>