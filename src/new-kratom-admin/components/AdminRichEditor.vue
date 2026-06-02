<script setup lang="ts">
import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import Link from '@tiptap/extension-link'
import Placeholder from '@tiptap/extension-placeholder'
import {
  Bold,
  Italic,
  Strikethrough,
  Heading2,
  Heading3,
  List,
  ListOrdered,
  Quote,
  Link as LinkIcon,
  Unlink,
  Image as ImageIcon,
  Undo2,
  Redo2,
} from 'lucide-vue-next'

const props = defineProps<{
  modelValue: string
  placeholder?: string
  minHeight?: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const { resolve: resolveMediaUrl } = useMediaUrl()
const pickerOpen = ref(false)

const editor = shallowRef<Editor | null>(null)
const editorForContent = computed<Editor | undefined>(() => editor.value ?? undefined)

onMounted(() => {
  editor.value = new Editor({
    content: props.modelValue || '',
    extensions: [
      StarterKit.configure({
        heading: { levels: [2, 3, 4] },
      }),
      Image.configure({ inline: false, allowBase64: false }),
      Link.configure({
        openOnClick: false,
        autolink: true,
        HTMLAttributes: { rel: 'noopener noreferrer', target: '_blank' },
      }),
      Placeholder.configure({
        placeholder: props.placeholder || 'Начните печатать…',
      }),
    ],
    editorProps: {
      attributes: {
        class: 'rt-content focus:outline-none px-3 py-2',
        style: 'min-height: ' + (props.minHeight || '200px'),
      },
    },
    onUpdate({ editor }) {
      emit('update:modelValue', editor.getHTML())
    },
  })
})

onBeforeUnmount(() => {
  editor.value?.destroy()
})

// Если внешнее значение поменялось — синхронизируемся (но не сбрасываем
// курсор, если контент уже совпадает).
watch(
  () => props.modelValue,
  (value) => {
    if (!editor.value) return
    const current = editor.value.getHTML()
    if (value === current) return
    editor.value.commands.setContent(value || '', false)
  },
)

function pickImage() {
  pickerOpen.value = true
}

function onMediaPicked(url: string, item: { filename?: string; alt?: string | null }) {
  if (!url) return
  editor.value?.chain().focus().setImage({ src: resolveMediaUrl(url), alt: item.alt || item.filename || '' }).run()
}

function toggleLink() {
  if (!editor.value) return
  const previous = editor.value.getAttributes('link').href as string | undefined
  const url = window.prompt('URL ссылки', previous || 'https://')
  if (url === null) return
  if (url === '') {
    editor.value.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }
  editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

function unsetLink() {
  editor.value?.chain().focus().unsetLink().run()
}

const isActive = (name: string, attrs?: Record<string, unknown>) =>
  !!editor.value?.isActive(name, attrs)
</script>

<template>
  <div class="border border-ink-700/15 rounded-lg overflow-hidden bg-white">
    <div class="flex flex-wrap items-center gap-1 border-b border-ink-700/10 px-2 py-1.5 bg-ink-700/[0.02]">
      <button type="button" class="rt-btn" :class="{ 'rt-btn-active': isActive('bold') }"
        @click="editor?.chain().focus().toggleBold().run()" title="Полужирный">
        <Bold :size="16" />
      </button>
      <button type="button" class="rt-btn" :class="{ 'rt-btn-active': isActive('italic') }"
        @click="editor?.chain().focus().toggleItalic().run()" title="Курсив">
        <Italic :size="16" />
      </button>
      <button type="button" class="rt-btn" :class="{ 'rt-btn-active': isActive('strike') }"
        @click="editor?.chain().focus().toggleStrike().run()" title="Зачёркнутый">
        <Strikethrough :size="16" />
      </button>
      <span class="rt-sep" />
      <button type="button" class="rt-btn" :class="{ 'rt-btn-active': isActive('heading', { level: 2 }) }"
        @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()" title="H2">
        <Heading2 :size="16" />
      </button>
      <button type="button" class="rt-btn" :class="{ 'rt-btn-active': isActive('heading', { level: 3 }) }"
        @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()" title="H3">
        <Heading3 :size="16" />
      </button>
      <span class="rt-sep" />
      <button type="button" class="rt-btn" :class="{ 'rt-btn-active': isActive('bulletList') }"
        @click="editor?.chain().focus().toggleBulletList().run()" title="Список">
        <List :size="16" />
      </button>
      <button type="button" class="rt-btn" :class="{ 'rt-btn-active': isActive('orderedList') }"
        @click="editor?.chain().focus().toggleOrderedList().run()" title="Нумерованный список">
        <ListOrdered :size="16" />
      </button>
      <button type="button" class="rt-btn" :class="{ 'rt-btn-active': isActive('blockquote') }"
        @click="editor?.chain().focus().toggleBlockquote().run()" title="Цитата">
        <Quote :size="16" />
      </button>
      <span class="rt-sep" />
      <button type="button" class="rt-btn" :class="{ 'rt-btn-active': isActive('link') }"
        @click="toggleLink" title="Ссылка">
        <LinkIcon :size="16" />
      </button>
      <button type="button" class="rt-btn" @click="unsetLink" title="Убрать ссылку" :disabled="!isActive('link')">
        <Unlink :size="16" />
      </button>
      <button type="button" class="rt-btn" @click="pickImage" title="Вставить изображение из медиа-библиотеки">
        <ImageIcon :size="16" />
      </button>
      <span class="rt-sep" />
      <button type="button" class="rt-btn" @click="editor?.chain().focus().undo().run()" title="Отменить">
        <Undo2 :size="16" />
      </button>
      <button type="button" class="rt-btn" @click="editor?.chain().focus().redo().run()" title="Повторить">
        <Redo2 :size="16" />
      </button>
    </div>

    <EditorContent :editor="editorForContent" />

    <AdminMediaPicker v-model="pickerOpen" @pick="onMediaPicked" />
  </div>
</template>

<style scoped>
.rt-btn {
  @apply inline-flex items-center justify-center w-8 h-8 rounded text-ink-700/70 hover:bg-ink-700/5 hover:text-ink-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors;
}
.rt-btn-active {
  @apply bg-moss-700/10 text-moss-700;
}
.rt-sep {
  @apply inline-block w-px h-5 bg-ink-700/10 mx-1;
}
</style>

<style>
.rt-content { font-size: 0.95rem; line-height: 1.6; color: #2a2a2a; }
.rt-content p { margin: 0.5em 0; }
.rt-content h2 { font-family: 'Playfair Display', serif; font-size: 1.35rem; margin: 1em 0 0.4em; }
.rt-content h3 { font-family: 'Playfair Display', serif; font-size: 1.15rem; margin: 0.9em 0 0.3em; }
.rt-content h4 { font-weight: 600; margin: 0.8em 0 0.3em; }
.rt-content ul, .rt-content ol { padding-left: 1.4em; margin: 0.5em 0; }
.rt-content ul { list-style: disc; }
.rt-content ol { list-style: decimal; }
.rt-content blockquote { border-left: 3px solid #5a7a3f; padding-left: 0.8em; color: rgba(42, 42, 42, 0.7); margin: 0.7em 0; }
.rt-content a { color: #3e5728; text-decoration: underline; }
.rt-content img { max-width: 100%; height: auto; border-radius: 6px; margin: 0.5em 0; }
.rt-content p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  color: rgba(42, 42, 42, 0.35);
  pointer-events: none;
  float: left;
  height: 0;
}
</style>
