<script setup>
const props = defineProps({
  title: {
      type: String,
      default: 'View all'
    },
    
    link: {
      type: String,
      required: true
    },

    items: {
      type: Number,
      required: true
    },

    activeIndex: {
      type: Number,
      default: 0
    },

    isArrows: {
      type: Boolean,
      default: 0
    }
})

const emit = defineEmits(['prev', 'next', 'select'])

// METHODS
const selectHandler = (key) => {
  emit('select', key)
}

const prevHandler = () => {
  emit('prev')
}

const nextHandler = () => {
  emit('next')
}
</script>

<style src="./slider-btns.scss" lang="sass" scoped />

<template>
  <div>
    <div class="dots">
      <template v-for="(item, key) in items" :key="key">
        <button
          @click="selectHandler(key)"
          :class="{active: key === activeIndex}"
          class="dots-item" 
        >
        </button>
      </template>
    </div>

    <div v-if="isArrows && link && title" class="btns-wrapper">

      <button
        v-if="isArrows"
        @click="prevHandler"
        class="nav-button slider-button prev"
        type="button"
        title="prev"
        clickable
      >
        <IconCSS name="fluent:chevron-left-48-filled" size="30px" class="icon"></IconCSS>
      </button>
      
      <NuxtLink
        :to="localePath(link)"
        :aria-label="title"
        class="button secondary action-button slider-button"
        clickable
      >
        <span class="text">{{ title }}</span>
      </NuxtLink>

      <button
        v-if="isArrows"
        @click="nextHandler"
        class="nav-button slider-button next"
        type="button"
        title="next"
        clickable
      >
        <IconCSS name="fluent:chevron-right-48-filled" size="30px" class="icon"></IconCSS>
      </button>

    </div>
  </div>
</template>