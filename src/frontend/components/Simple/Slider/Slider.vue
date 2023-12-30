<script setup>

  const cardRef = ref(null);

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
  const isMounted = ref(false)
  
  // 
  const touch = ref({
    from: null,
    to: null
  })

  // PROPS
  const props = defineProps({
    component: {
      type: Object
    },
    values: {
      type: Array,
      required: true
    },
    allItemsLabel: {
      type: String,
      default: ''
    },
    allItemsLink: {
      type: String,
      default: ''
    },
    initOn: {
      type: String,
      default: 'tablet'
    }
  })

  // COMPUTED
  const listStyleValue = computed(() => {
    return {
      transform: `translateX(${offset.value}px)`
    }
  })

  const slides = computed(() => {
    return props.values
  })

  const isOffset = computed(() => {
    if(isMounted.value) {
      //let cardWidth = this.$refs.card[0].offsetWidth + this.gap
      let cardWidth = 300 + gap.value
      // console.log('isMounted', this.$refs.card[0].offsetWidth, cardWidth, this.arrayLength, this.gap,  (cardWidth * this.arrayLength), window.innerWidth )
      return (cardWidth * arrayLength.value - window.innerWidth) > 0
    }
    else
      return false
  })

  // METHODS
  const prevHandler = () => {
    const cardWidth = cardRef.value[0].offsetWidth + gap.value
    const maxOffset = (cardWidth * arrayLength.value - window.innerWidth) * -1
    const emptySpace = Math.abs(offset.value)

    if(emptySpace >= cardWidth) {
      offset.value = offset.value + cardWidth
      activeIndex.value--
    } else if(emptySpace === 0) {
      offset.value = maxOffset - gap.value
      activeIndex.value = arrayLength.value - 1
    } else {
      offset.value = offset.value + emptySpace
      activeIndex.value--
    }
  }

  const nextHandler = () => {
    const cardWidth = cardRef.value[0].offsetWidth + gap.value
    const maxOffset = (cardWidth * arrayLength.value - window.innerWidth) * -1
    const emptySpace = Math.abs(maxOffset - offset.value - gap.value)

    if(emptySpace >= cardWidth) {
      offset.value = offset.value - cardWidth
      activeIndex.value++
    } else if(emptySpace === 0) {
      offset.value = 0
      activeIndex.value = 0
    } else {
      offset.value = offset.value - emptySpace
      activeIndex.value++
    }
  }

  const touchStartHandler = (event) => {
    touch.value.from = event.changedTouches[0].screenX
  }

  const touchEndHandler = (event) => {
    touch.value.to = event.changedTouches[0].screenX

    const step = touch.value.from - touch.value.to
    
    if(Math.abs(step) < 30)
      return

    if(step < 0)
      prevHandler()
    else
      nextHandler()
  }

  const selectHandler = (value) => {
    activeIndex.value = value
  }

  const resizeHandler = () => {
    offset.value = 0
    activeIndex.value = 0

    const cardWidth = cardRef.value[0].offsetWidth + gap.value
    const sliderWidth = cardWidth * arrayLength.value
    
    offsetSize.value = window.innerWidth - sliderWidth
    offsetItems.value = Math.ceil(Math.abs(offsetSize.value / cardWidth))
  }

  // HOOKS
  onMounted(() => {
    arrayLength.value = props.values.length
    isMounted.value = true

    window.addEventListener('resize', resizeHandler)
    resizeHandler()
  })
</script>

<style src="./slider.scss" lang="scss" scoped />

<template>
  <div class="slider-wrapper">
    <div
      @touchmove="touchHandler"
      @touchstart="touchStartHandler"
      @touchend="touchEndHandler"
      :class="initOn"
      ref="wrapper"
      class="list"
      scrollable
    >
      <ul :style="listStyleValue" class="list-ul" ref="list">
        <li
          v-for="(item, index) in slides"
          :key="item.id"
          class="item"
          ref="cardRef"
        >
          <component :is="component" :item="item"></component>
        </li>
      </ul>
    </div>

    <simple-pagination
      v-if="offsetSize < 0"
      :current = "activeIndex + 1"
      :total = "offsetItems + 1"
      @select = "selectHandler"
      @prev = "prevHandler"
      @next = "nextHandler"
      class="pagination"
    >
    </simple-pagination>

  </div>
</template>