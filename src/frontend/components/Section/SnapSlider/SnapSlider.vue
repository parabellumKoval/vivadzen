<script setup>
const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  component: {
    required: true
  },
  gutter: {
    type: Number
  },
  options: {
    type: Object,
  },
  title: {
    type: String
  },
  link: {
    type: String
  },
  itemDataName: {
    type: String
  }
})

const simpleSnapSliderRef = ref(null)

const pagination = ref({
  isActive: false,
  activeIndex: 0,
  total: 0,
  progress: 0
})


const setPaginationHandler = (val) => {
  pagination.value.isActive = val.isActive
  pagination.value.total = val.total
}

const setProgress = (val) => {
  pagination.value.progress = val
}

const setActiveIndex = (val) => {
  pagination.value.activeIndex = val
}

// HANDLERS
const prevHandler = () => {
  simpleSnapSliderRef.value.prevHandler()
}
const nextHandler = () => {
  simpleSnapSliderRef.value.nextHandler()
}

</script>

<style src="./snap-slider.scss" lang="sass" scoped />

<template>
  <div v-if="items && items.length">
    <simple-snap-slider
      :values="items"
      :component="component"
      :gutter="gutter"
      :options="options"
      :item-data-name="itemDataName"
      @setPagination="setPaginationHandler"
      @setProgress="setProgress"
      @setIndex="setActiveIndex"
      class="slider"
      ref="simpleSnapSliderRef"
    >
    </simple-snap-slider>

    <simple-slider-btns
      :items="pagination.total"
      :title="title"
      :link="link"
      :active-index="pagination.activeIndex"
      :is-arrows="pagination.isActive"
      @prev="prevHandler"
      @next="nextHandler"
      class="nav-btns"
    >
    </simple-slider-btns>
  </div>
</template>