<script setup>
const {t} = useI18n()

// COMPUTED
const confirm = computed(() => {
  return useModal().active?.data?.confirm
})

const yes = computed(() => {
  return confirm?.value?.yes 
})

const no = computed(() => {
  return confirm?.value?.no
})

const title = computed(() => {
  return confirm?.value?.title || t('message.sure')
})

const desc = computed(() => {
  return confirm?.value?.desc || null
})

// HANDLERS
const trueHandler = () => {
  if(yes.value.callback) {
    yes.value.callback()
    useModal().close()
    console.log('trueHandler')
  }
}

const falseHandler = () => {
  if(no.value.callback) {
    yes.value.callback()
    useModal().close()
    console.log('falseHandler')
  }
}
</script>

<style src="./confirm.scss" lang="scss" scoped />

<template>
  <modal-wrapper :title="title" :description="desc">
    <div class="buttons-wrapper">
      <button @click="trueHandler" class="button">{{ yes.title || t('label.yes') }}</button>
      <button @click="falseHandler" class="button button-primary">{{ no.title || t('label.no') }}</button>
    </div>
  </modal-wrapper>
</template>