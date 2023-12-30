<script setup>

import { EditorContent, Editor, Extension } from '@tiptap/vue-3'
import {Underline} from '@tiptap/extension-underline'
import {Italic} from '@tiptap/extension-italic'
import {Bold} from '@tiptap/extension-bold'
import {Placeholder} from '@tiptap/extension-placeholder'
// import { Highlight } from '@tiptap/extension-highlight'
// import { TextAlign } from '@tiptap/extension-text-align'
import { StarterKit } from '@tiptap/starter-kit'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
    required: true
  },
  editable: {
    type: Boolean,
    default: true
  },
  placeholder: {
    type: String,
    default: true
  }
})
const mv = ref(null)
const editor = ref()

const extensionNames = computed(() => {
  return props.extensions.map(ext => ext.name)
})

watch(
  () => props.modelValue,
  (value) => {
    const isSame = editor.value.getHTML() === value
    if (!isSame) {
      editor.value.commands.setContent(value, false)
    }
  }
)

const emit = defineEmits(['update:modelValue'])

onMounted(() => {
  editor.value = new Editor({
    content: props.modelValue,
    editable: props.editable,
    editorProps: {
      attributes: {
        class: ''
      }
    },
    extensions: [
      StarterKit,
      Underline,
      Italic,
      Bold,
      Placeholder.configure({
        // Use a placeholder:
        placeholder: props.placeholder,
      })
    ],
    onUpdate: () => {
      emit('update:modelValue', editor.value?.getHTML())
    }
  })
})

onBeforeUnmount(() => {
  editor.value?.destroy()
  editor.value = null
})

</script>

<style src="./tiptap.scss" lang="scss" scoped />

<template>
  <div class="tiptap-wrapper" v-if="editor">
    <form-tiptap-toolbar>
      <form-tiptap-button
        @click="editor.chain().focus().toggleBold().run()"
        :is-active="editor.isActive('bold')"
        icon="ph:text-b-light"
      />
      <form-tiptap-button
        @click="editor.chain().focus().toggleItalic().run()"
        :is-active="editor.isActive('italic')"
        icon="ph:text-italic-light"
      />
      <form-tiptap-button
        @click="editor.chain().focus().toggleUnderline().run()"
        :is-active="editor.isActive('underline')"
        icon="ph:text-underline-light"
      />
    </form-tiptap-toolbar>
    <!-- <Toolbar id="toolbar">
      <template #start>
      </template>
    </Toolbar> -->
    <editor-content :editor="editor" class="tiptap-editor" clickable />
    <!-- <editor v-model="mv" /> -->
  </div>
</template>