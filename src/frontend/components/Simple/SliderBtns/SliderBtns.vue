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
    },

    isMoreBtn: {
      type: Boolean,
      default: 1
    }
})

const emit = defineEmits(['prev', 'next', 'select'])

// COMPUTEDS
const moreBtn = computed(() => {
  return props.isMoreBtn && props.link && props.title
})

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
    <div v-if="moreBtn" class="dots-wrapper">
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
    </div>

    <div :class="{short: moreBtn}" class="btns-wrapper">

      <button
        v-if="isArrows"
        @click="prevHandler"
        class="nav-button slider-button prev button secondary"
        type="button"
        title="prev"
        clickable
      >
        <IconCSS name="fluent:chevron-left-48-filled" size="30px"></IconCSS>
      </button>
      
      <template v-if="moreBtn">
        <NuxtLink
          :to="localePath(link)"
          :aria-label="title"
          class="action-button slider-button button secondary"
          clickable
        >
          <span class="text">{{ title }}</span>
        </NuxtLink>
      </template>
      <template v-else>
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
      </template>

      <button
        v-if="isArrows"
        @click="nextHandler"
        class="nav-button slider-button next button secondary"
        type="button"
        title="next"
        clickable
      >
        <IconCSS name="fluent:chevron-right-48-filled" size="30px"></IconCSS>
      </button>

    </div>
  </div>
</template>