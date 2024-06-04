<script setup>
const {t} = useI18n()

// COMPUTED
// default, good, bad
const type = computed(() => {
  return confirm?.value?.type || 'default' 
})

const confirm = computed(() => {
  return useModal().active?.data
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
  useModal().close()

  if(yes.value.callback) {
    yes.value.callback()
  }
}

const falseHandler = () => {
  useModal().close()

  if(no.value.callback) {
    no.value.callback()
  }
}
</script>

<style src="./confirm.scss" lang="scss" scoped />

<template>
  <modal-wrapper :title="title" :description="desc">
    <div :class="type" class="buttons-wrapper">
      <button
        @click="trueHandler"
        :class="[{secondary: type === 'default'}, {primary: type === 'good'}]"
        class="button  full"
      >{{ yes.title || t('button.yes') }}</button>
      <button
        @click="falseHandler"
        :class="[{primary: type === 'default'}, {error: type === 'bad'}, {secondary: type === 'good'}]"
        class="button full"
      >{{ no.title || t('button.no') }}</button>
    </div>
  </modal-wrapper>
</template>