<script setup>
const {t} = useI18n()

const note = computed(() => {
  return useModal().active?.data?.item || {
    content: null,
    color: 'default'
  }
})

const removeHandler = () => {
  useModal().close()
}

const applyHandler = () => {
  useModal().close()
}
</script>

<style src="./note.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('edit_note')">
    <div class="form-wrapper">
      <form-label :label="t('text')">
        <form-textarea
          v-model="note.content"
          :placeholder="t('enter_text')"
          type="number"
          :class="note.color"
          class="textarea"
        ></form-textarea>
      </form-label>

      <form-label :label="t('color')">
        <form-color v-model="note.color"></form-color>
      </form-label>
    </div>

    <div class="buttons-wrapper">
      <button @click="removeHandler" class="button button-secondary button-red">
        Удалить заметку
      </button>
      <button @click="applyHandler" class="button button-primary">
        Применить
      </button>
    </div>

  </modal-wrapper>
</template>