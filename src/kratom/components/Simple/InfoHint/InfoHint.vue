<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  text: {
    type: String,
    required: true
  },
  icon: {
    type: String,
    default: 'pepicons-pop:info'
  },
  maxWidth: {
    type: Number,
    default: 320
  }
})

const rootRef = ref<HTMLElement | null>(null)
const triggerRef = ref<HTMLElement | null>(null)
const bubbleRef = ref<HTMLElement | null>(null)

const isOpen = ref(false)
const placement = ref<'top' | 'bottom'>('top')
const bubbleTop = ref(0)
const bubbleLeft = ref(0)
const arrowLeft = ref(0)
const bubbleMaxWidth = ref(props.maxWidth)

const horizontalPadding = 10
const offset = 10

const bubbleStyle = computed(() => ({
  maxWidth: `${bubbleMaxWidth.value}px`,
  top: `${bubbleTop.value}px`,
  left: `${bubbleLeft.value}px`,
  '--info-hint-arrow-left': `${arrowLeft.value}px`
}))

const close = () => {
  isOpen.value = false
}

const toggle = async () => {
  isOpen.value = !isOpen.value

  if (isOpen.value) {
    await nextTick()
    updatePosition()
  }
}

const updatePosition = async () => {
  if (!process.client || !isOpen.value || !bubbleRef.value || !triggerRef.value) return

  bubbleMaxWidth.value = Math.min(props.maxWidth, window.innerWidth - horizontalPadding * 2)
  placement.value = 'top'

  await nextTick()

  const bubbleEl = bubbleRef.value
  const triggerRect = triggerRef.value.getBoundingClientRect()
  const bubbleWidth = bubbleEl.offsetWidth
  const bubbleHeight = bubbleEl.offsetHeight
  const viewportWidth = window.innerWidth
  const viewportHeight = window.innerHeight
  const anchorX = triggerRect.left + triggerRect.width / 2

  const topPreferred = triggerRect.top - bubbleHeight - offset
  const bottomPreferred = triggerRect.bottom + offset
  const canPlaceTop = topPreferred >= horizontalPadding
  const canPlaceBottom = bottomPreferred + bubbleHeight <= viewportHeight - horizontalPadding

  if (!canPlaceTop && canPlaceBottom) {
    placement.value = 'bottom'
  }

  const minLeft = horizontalPadding
  const maxLeft = viewportWidth - horizontalPadding - bubbleWidth

  bubbleLeft.value = Math.min(
    Math.max(anchorX - bubbleWidth / 2, minLeft),
    Math.max(minLeft, maxLeft)
  )

  if (placement.value === 'top') {
    bubbleTop.value = Math.max(horizontalPadding, topPreferred)
  } else {
    bubbleTop.value = Math.min(
      bottomPreferred,
      viewportHeight - horizontalPadding - bubbleHeight
    )
  }

  arrowLeft.value = Math.min(
    Math.max(anchorX - bubbleLeft.value, 12),
    bubbleWidth - 12
  )
}

const handleDocumentClick = (event: MouseEvent) => {
  if (!isOpen.value || !rootRef.value || !(event.target instanceof Node)) return
  if (rootRef.value.contains(event.target)) return
  if (bubbleRef.value?.contains(event.target)) return

  close()
}

const handleWindowBlur = () => {
  if (isOpen.value) {
    close()
  }
}

const handleEscape = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    close()
  }
}

const handleViewportChange = () => {
  if (isOpen.value) {
    updatePosition()
  }
}

onMounted(() => {
  document.addEventListener('click', handleDocumentClick)
  document.addEventListener('keydown', handleEscape)
  window.addEventListener('resize', handleViewportChange)
  window.addEventListener('scroll', handleViewportChange, true)
  window.addEventListener('blur', handleWindowBlur)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
  document.removeEventListener('keydown', handleEscape)
  window.removeEventListener('resize', handleViewportChange)
  window.removeEventListener('scroll', handleViewportChange, true)
  window.removeEventListener('blur', handleWindowBlur)
})
</script>

<style src="./info-hint.scss" lang="scss" scoped></style>

<template>
  <span ref="rootRef" class="info-hint" :class="{ 'info-hint--open': isOpen }">
    <button
      ref="triggerRef"
      type="button"
      class="info-hint__trigger"
      :aria-label="text"
      :aria-expanded="isOpen"
      @click.stop="toggle"
    >
      <IconCSS :name="icon" />
    </button>

    <Teleport to="body">
      <Transition name="info-hint-pop">
        <span
          v-if="isOpen"
          ref="bubbleRef"
          role="tooltip"
          class="info-hint__bubble"
          :class="`info-hint__bubble--${placement}`"
          :style="bubbleStyle"
        >
          {{ text }}
        </span>
      </Transition>
    </Teleport>
  </span>
</template>
