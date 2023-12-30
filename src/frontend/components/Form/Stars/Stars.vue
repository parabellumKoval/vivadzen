<script setup>

const {t} = useI18n()

const props = defineProps({
  modelValue: {
    type: [Number],
    default: 0
  },

  slots: {
    type: Number,
    default: 5
  },

  labels: {
    type: [Array]
  }
})

const emit = defineEmits([
  'update:modelValue'
])

const starLabels = ref([])

const setHandler = (v) => {
  if(v === props.modelValue)
    emit('update:modelValue', 0)
  else
    emit('update:modelValue', v)
}

watch(() => props.labels, (v) => {
  if(v) {
    starLabels.value = v
  } else {
    starLabels.value = [
      t('mark.bad'),
      t('mark.so_so'),
      t('mark.normal'),
      t('mark.good'),
      t('mark.perfect')
    ]
  }
}, {
  immediate: true
})
</script>

<style src="./stars.scss" lang="scss" scoped />

<template>
  <ul class="stars">
    <li
      v-for="i in slots"
      :key="i"
      @click="setHandler(i)"
      :class="{active: i <= modelValue}"
      clickable
      class="item"
    >
      <IconCSS name="fluent:star-20-filled" size="40px" class="icon"></IconCSS>
      <p v-if="starLabels[i - 1]">{{ starLabels[i-1] }}</p>
    </li>
  </ul>
</template>

<i18n src="./lang.yaml" lang="yaml"></i18n>