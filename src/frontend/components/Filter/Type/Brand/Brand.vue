<script setup>
const props = defineProps({
  filter: {
    type: Object
  },

  modelValue: {
    type: Object,
    default: {}
  }
})

const emit = defineEmits(['update:modelValue'])

// console.log('filter type brand', props.filter)
// METHODS
const getImageSrc = (item) => {
  if(item?.image?.src)
    return '/server/images/brands/' + item.image.src
  else
    return './images/noimage.png'
}

const checkHandler = (id) => {
  const allFiltersCopy = {...props.modelValue}
  const thisFilter = allFiltersCopy[props.filter.id]

  // if this filter not exists inside selected yet
  if(!allFiltersCopy[props.filter.id]) {
    allFiltersCopy[props.filter.id] = [id]
  // if filter already exists
  }else {
    const findIndex = allFiltersCopy[props.filter.id].indexOf(id)

    // Add
    if(findIndex === -1) {
      allFiltersCopy[props.filter.id].push(id)
    // Remove
    }else {
      allFiltersCopy[props.filter.id].splice(findIndex, 1)
    }   
  }
  console.log('checkHandler', allFiltersCopy)
  emit('update:modelValue', allFiltersCopy)
}
</script>

<!-- <style src="checkbox2.scss" lang="scss" scoped></style> -->
<style src="./brand.scss" lang="scss" scoped></style>

<template>
  <div class="wrapper">
    <ul class="brand-list">
      <li
        v-for="(value, index) in filter.values"
        :key="value.id"
        :class="[{checked: modelValue[filter.id]?.includes(value.id)}]"
        @click="checkHandler(value.id)"
        class="brand-item"
      >
        <button class="brand-item-btn" button>
          <div class="brand-input">
            <IconCSS name="iconoir:check" class="brand-input-icon"></IconCSS>
          </div>
          <span class="brand-content">
            <nuxt-img
              :src='getImageSrc(value)'
              width='40'
              height='40'
              sizes='mobile:40px tablet:40px desktop:40px'
              format='webp'
              quality='60'
              loading='lazy'
              fit='outside'
              placeholder="./images/noimage.png"
              class='brand-image'
            />
            <span class="brand-name">{{ value.name }}</span>
            <span class="brand-count">{{ value.count }}</span>
          </span>
        </button>
      </li>
    </ul>
  </div>
</template>