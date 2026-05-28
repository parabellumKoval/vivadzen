<script setup lang="ts">
defineProps<{
  modelValue: string
  locales: Array<{ code: string; native: string; label: string }>
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <div class="flex flex-wrap gap-2">
    <button
      v-for="locale in locales"
      :key="locale.code"
      type="button"
      class="px-3 py-1.5 rounded-full border text-sm transition-colors"
      :class="modelValue === locale.code
        ? 'border-moss-700 bg-moss-700 text-white'
        : 'border-ink-700/10 bg-white text-ink-700/70 hover:border-moss-300 hover:text-moss-700'"
      @click="emit('update:modelValue', locale.code)"
    >
      {{ locale.native }}
    </button>
  </div>
</template>
