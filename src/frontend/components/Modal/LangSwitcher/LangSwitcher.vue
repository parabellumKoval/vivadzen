<script setup>
const {t, locale, locales} = useI18n()

const switchLocalePath = useSwitchLocalePath()

const props = defineProps({
  isActive: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'select'
])

const availableLocales = computed(() => {
  return locales.value.map((item) => {
    return {
      id: item.code,
      title: item.name,
      callback: switchHandler
    }
  })
})

const switchHandler = (item) => {
  const newLocale = switchLocalePath(item.id)
  navigateTo(newLocale)
  useModal().close()
  emit('select')
}

const selectHandler = () => {}
</script>

<style src="./lang-switcher.scss" lang="sass" scoped />
<i18n src="./lang.yaml" lang="yaml" />

<template>
  <modal-wrapper :title="t('select_lang')">
    <simple-list-icon :items="availableLocales" :active="locale" class="list"></simple-list-icon>
  </modal-wrapper>
</template>