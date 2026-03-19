<script setup lang="ts">
import { computed } from 'vue'

const { t } = useI18n()

// COMPUTEDS
/**
 * Get info data from modal
 * Expected data structure: {
 *   title: string,
 *   message: string,
 *   buttonText?: string, // default: 'ок'
 *   type?: 'default' | 'success' | 'error' | 'warning', // default: 'default'
 * }
 */
const info = computed(() => {
  return useModal().active?.data || {}
})

const type = computed(() => {
  return info.value?.type || 'default'
})

const title = computed(() => {
  return info.value?.title || ''
})

const message = computed(() => {
  return info.value?.message || ''
})

const buttonText = computed(() => {
  return info.value?.buttonText || 'ок'
})

const buttonClass = computed(() => {
  switch (type.value) {
    case 'success':
      return 'primary'
    case 'error':
      return 'secondary'
    case 'warning':
      return 'secondary'
    default:
      return 'primary'
  }
})

// HANDLERS
const closeHandler = () => {
  useModal().close()
}
</script>

<style src="./info.scss" lang="scss" scoped />

<template>
  <modal-wrapper :title="title" :type="type">
    <div class="modal-info">
      <p class="modal-info__message">{{ message }}</p>
      
      <div class="modal-info__footer">
        <button 
          @click="closeHandler" 
          :class="['button', buttonClass]"
        >
          {{ buttonText }}
        </button>
      </div>
    </div>
  </modal-wrapper>
</template>
