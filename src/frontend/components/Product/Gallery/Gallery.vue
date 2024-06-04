<script setup>
const props = defineProps({
  items: {
    type: Array
  }
})

const isZoom = ref(false)
const activeIndex = ref(0)
const position = ref({
  x: 0,
  y: 0,
  relativeX: 0,
  relativeY: 0,
  width: 0,
  height: 0
})
const zoomSizes = ref({
  width: 400,
  height: 400
})

const mainImageWrapperRef = ref(null)

// COMPUTEDS
const zoomWrapperStyle = computed(() => {
  return {
    width: zoomSizes.value.width + 'px',
    height:  zoomSizes.value.height + 'px',
  }
})

const zoomStyle = computed(() => {
  const width = position.value.width * 2
  const height = position.value.height * 2

  let x = -(position.value.x * 2 - zoomSizes.value.width / 2 )
  let y = -(position.value.y * 2 - zoomSizes.value.height / 2 )

  // Stop on left
  if(x > 0) {
    x = 0
  }

  // Stop on right
  const rightBorder = width - zoomSizes.value.width
  if(x < -rightBorder){
    x = -rightBorder
  }

  // Stop on top
  if(y > 0) {
    y = 0
  }

  // Stop on bottom
  const bottomBorder = height - zoomSizes.value.height
  if(y < -bottomBorder){
    y = -bottomBorder
  }

  return {
    width: width.toFixed() + 'px',
    height: height.toFixed() + 'px',
    transform: 'translateX(' + x.toFixed() + 'px) translateY(' + y.toFixed() + 'px)'
  }
})

const cursorStyle = computed(() => {
  const width = zoomSizes.value.width / 2
  const height = zoomSizes.value.height / 2

  const wrapperWidth = mainImageWrapperRef.value?.offsetWidth || 0
  const wrapperHeight = mainImageWrapperRef.value?.offsetHeight || 0

  const halfOffsetWidth = (wrapperWidth - position.value.width) / 2
  const halfOffsetHeight = (wrapperHeight - position.value.height) / 2

  let x = position.value.x - width / 2  + halfOffsetWidth
  let y = position.value.y - height / 2 + halfOffsetHeight

  // Stop on left border
  if(x - halfOffsetWidth < 0) {
    x = halfOffsetWidth
  }

  // Stop on right border
  const rightBorder = position.value.width + halfOffsetWidth - width
  if(x > rightBorder) {
    x = rightBorder
  }

  // Stop on top
  if(y - halfOffsetHeight < 0) {
    y = halfOffsetHeight
  }

  // Stop on bottom
  const bottomBorder = position.value.height + halfOffsetHeight - height
  if(y > bottomBorder) {
    y = bottomBorder
  }

  return  {
    width: width.toFixed() + 'px',
    height: height.toFixed() + 'px',
    transform: 'translateX(' + x.toFixed() + 'px) translateY(' + y.toFixed() + 'px)'
  }
})

const image = computed(() => {
  return props.items?.length? props.items[activeIndex.value]: {
    src: './images/noimage.png',
    alt: null,
    title: null
  }
})

const images = computed(() => {
  return props.items
})

// METHODS
const getImage = (src) => {
  if(src)
    return '/server/images/products/' + src
  else
    return null
}

// HANDLERS
const selectHandler = (index) => {
  activeIndex.value = index
}

const mouseOverHandler = (e) => {
  position.value.width = e.target.offsetWidth
  position.value.height = e.target.offsetHeight

  position.value.x = e.offsetX
  position.value.y = e.offsetY
}
</script>

<style src="./gallery.scss" lang="scss" scoped></style>

<template>
  <div class="gallery" :class="'length-' + (images?.length || 1)">
    <div class="thumbnail">
      <button
        v-for="(item, index) in images"
        :key="index"
        :class="{active: activeIndex === index}"
        @click="selectHandler(index)"
        class="thumbnail-btn"
      >
        <nuxt-img
          v-if="item.src"
          :src = "getImage(item.src)"
          :alt = "item.alt"
          :title = "item.title"
          :class="item.size"
          width="85"
          height="85"
          sizes = "mobile:100px tablet:100px desktop:100px"
          format = "webp"
          quality = "60"
          loading = "lazy"
          placeholder="./images/noimage.png"
          fit="outside"
          class="thumbnail-image"
        >
        </nuxt-img> 
      </button>
    </div>
    <div ref="mainImageWrapperRef" class="main">
      <div v-if="isZoom && images?.length" :style="cursorStyle" class="cursor"></div>
      
      <nuxt-img
        v-if="image.src"
        :src = "getImage(image.src)"
        :alt = "image.alt"
        :title = "image.title"
        :class="image.size"
        sizes = "mobile:100vw tablet:600px desktop:600px"
        format = "webp"
        quality = "60"
        loading = "lazy"
        fit="outside"
        class="main-image"
        placeholder="./images/noimage.png"
        @mousemove.passive="mouseOverHandler"
        @mouseenter="() => isZoom = true"
        @mouseleave="() => isZoom = false"
      />
    </div>
      <transition name="fade-in">
        <div
          v-if="isZoom && images?.length"
          :style="zoomWrapperStyle"
          class="zoom"
        >
          <nuxt-img
            v-if="image.src"
            :src = "getImage(image.src)"
            sizes = "mobile:0px tablet:0px desktop:1200px"
            format = "webp"
            quality = "100"
            loading = "lazy"
            fit="outside"
            class="image"
            :style="zoomStyle"
          />
        </div>
      </transition>
  </div>
</template>