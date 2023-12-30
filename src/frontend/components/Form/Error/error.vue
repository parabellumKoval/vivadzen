<script setup>

const props = defineProps({
  error: {
    type: [Object, Array, String, Boolean],
    default: false
  }
})

const isActive = ref(false)

// COMPUTED

const errorMessage = computed(() => {
  return Array.isArray(props.error)? props.error[0]: props.error;
})

// METHODS
const toggleError = () => {
  isActive.value = !isActive.value
}
</script>

<style src="./error.scss" lang="sass" scoped />

<template>
  <transition name="fade-in">
    <div
      v-if="error && errorMessage"
      :class="{active: isActive}"
      class="error"
    >
      <span class="error-text" v-html="errorMessage"></span>
      <button @click="toggleError" class="error-btn" type="button">
        <IconCSS v-if="isActive" name="fluent:dismiss-20-regular" size="20px" class="error-icon"></IconCSS>
        <IconCSS v-else name="fluent:error-circle-20-filled" size="20px" class="error-icon"></IconCSS>
      </button>
    </div>
  </transition>
</template>