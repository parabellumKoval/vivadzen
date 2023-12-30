<script setup>
// PROPS
const props = defineProps({
  component: {
    type: Object
  },

  values: {
    type: Array,
    required: true
  },

  gutter: {
    type: Number,
    default: 0
  },

  width: {
    type: String,
  },

  keyName: {
    type: String,
    default: 'id'
  },

  margin: {
    type: Object,
    default: () => {
      return {
        mobile: 15,
        tablet: 35,
        desktop: 35,
      }
    }
  },
})

// EMITS
const emit = defineEmits([
  'setPagination',
  'setProgress',
  'setIndex'
])

// REFS
const cardRef = ref([])
const listRef = ref(null)

// DATA
// Currently active slider index
const activeIndex = ref(0)

// How many slider items?
const arrayLength = ref(0)

// Pagination offset
const offset = ref(0)

// How many pixels are currently hidden?
const offsetSize = ref(0)

// How many items are currently hidden?
const offsetItems = ref(0)

// Margin between slider items
const gap = ref(20)

// Is slider have already mounted? 
const isCardSizesSet = ref(false)


const totalWidth = ref(0)
const cardSizes = ref([])
const scrollEnd = ref(true)
const scrollTimeout = ref(null)
const progress = ref(0)

const pagination = ref({
  isActive: false,
  total: 0,
})

// COMPUTED
// css
const gutterCss = computed(() => {
  return props.gutter + 'px'
})

const gridAutoColumnsCss = computed(() => {
  if(props.width)
    return props.width
  else
    return `minmax(min-content, auto)`
})

const widthCss = computed(() => {
  if(!props.width)
    return `initial`
  else
    return `100%`
})

const slides = computed(() => {
  return props.values
})

// METHODS
const setPadination = () => {
  offsetSize.value = listRef.value.offsetWidth - totalWidth.value
  offsetItems.value = Math.ceil(Math.abs(offsetSize.value) / cardRef.value[0].offsetWidth)

  pagination.value.isActive = offsetSize.value < 0
  pagination.value.total = offsetItems.value + 1

  emit('setPagination', pagination.value)
}

const setCardSizes = () => {
  const sizes = []
  totalWidth.value = 0

  cardRef.value.forEach((item, key) => {
    sizes.push({
      width: item.offsetWidth,
      startX: item.offsetLeft,
      endX: item.offsetLeft + item.offsetWidth
    })

    totalWidth.value += item.offsetWidth
  })

  cardSizes.value = sizes
}

const findAndSetActiveIndex = (x) => {
  const v = Math.floor(x / cardSizes.value[0].width)
  activeIndex.value = v

  // const index = cardSizes.value.findIndex((item) => {
  //   return item.startX >= x - 2 && item.startX <= x + 2
  // })

  // if(index !== -1)
  //   activeIndex.value = index
}

const calcProgress = (x) => {
  const cardWidth = cardSizes.value[activeIndex.value].width + props.gutter
  const stateX = x - cardSizes.value[activeIndex.value].startX
  const persent = stateX * 100 / cardWidth
  progress.value = persent

  emit('setProgress', progress.value)
}

const getProgress = (index) => {
  if(activeIndex.value === index){
    if(progress.value >= 0)
      return 100 - progress.value
    else
      return 100 + progress.value
  }

  if(activeIndex.value - 1 === index && progress.value < 0){
      return -progress.value
  }

  if(activeIndex.value + 1 === index && progress.value > 0){
      return progress.value
  }
}

// HANDLERS
const prevHandler = () => {
  if(!cardSizes.value[activeIndex.value - 1])
    return

  const offset = cardSizes.value[activeIndex.value - 1].startX
  listRef.value.scrollTo(offset, 0)
}

const nextHandler = () => {
  if(!cardSizes.value[activeIndex.value + 1])
    return

  const offset = cardSizes.value[activeIndex.value + 1].startX
  listRef.value.scrollTo(offset, 0)
}

const selectHandler = (value) => {
  if(!cardSizes.value[value])
    return

  const offset = cardSizes.value[value].startX
  listRef.value.scrollTo(offset, 0)
}

const resizeHandler = () => {
  activeIndex.value = 0
  listRef.value.scrollTo(0, 0)

  setCardSizes()
  setPadination()
}

const listScrollHandler = (event) => {
  scrollEnd.value = false
  const x = event.target.scrollLeft

  findAndSetActiveIndex(x)
  // clearTimeout(scrollTimeout.value)
  // scrollTimeout.value = setTimeout(() => {
  //   findAndSetActiveIndex(x)
  //   scrollEnd.value = true
  // }, 0)


  // console.log('x', x)
  calcProgress(x)
}

// WATCHERS
watch(activeIndex, (val) => {
  emit('setIndex', val)
}, {
  immediate: true
})

// watchEffect(() => {
//   if (cardRef.value) {
//     console.log('cardRef.value', cardRef.value)
//     setCardSizes()
//   }
// })

watchEffect(() => {
  if (listRef.value && cardRef.value && totalWidth.value) {
    setCardSizes()
    setPadination()
  }
})

// HOOKS
onMounted(() => {
  arrayLength.value = props.values.length
  window.addEventListener('resize', resizeHandler)

  setTimeout(() => {
    resizeHandler()
  }, 300)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', resizeHandler)
})

// Expose
defineExpose({
  prevHandler,
  nextHandler,
  selectHandler
})
</script>

<style src="./snap-slider.scss" lang="scss" scoped />

<style lang="scss" scoped>
@import '~/assets/scss/mixins';
.list-ul {
  grid-auto-columns: v-bind(gridAutoColumnsCss);
  grid-gap: v-bind(gutterCss);
}
.item {
  width: v-bind(widthCss);
}
</style>

<template>
  <div class="slider-wrapper">
    <div
      ref="wrapper"
      class="list"
      scrollable
    >
      <ul @scroll.passive="listScrollHandler" ref="listRef" class="list-ul hide-scroll">
        <li
          v-for="(item, index) in slides"
          :key="item[props.keyName]"
          class="item"
          ref="cardRef"
        >
          <component
            :is="component"
            :item="item"
            :progress="getProgress(index)"
            :is-active="index === activeIndex"
          >
          </component>
        </li>
      </ul>
    </div>

  </div>
</template>