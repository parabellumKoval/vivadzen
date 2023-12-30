<script setup>
const props = defineProps({
  item: {
    type: Object
  }
})

const isControlsActive = ref(false)
const itemsData = ref([])

// COMPUTED
const isUpDisabled = (index) => {
  return index === 0
}

const isDownDisabled = (index) => {
  return index === itemsData.value.length - 1
}

// HANDLERS
const clickHandler = (event) => {
  if(useDevice().isMobile) {
    useModal().open(resolveComponent('ModalMobileNote'), {item: props.item}, null, null)
  }
}

const toggleControlsHandler = (event) => {
  useModal().open(resolveComponent('ModalMobileNote'), {item: props.item}, event.target.closest('[modalable]'), null)
  // isControlsActive.value = !isControlsActive.value
}

const clickControlsHandler = (index, v) => {
  if(v === 'up')
    moveUp(index)

  if(v === 'down')
    moveDown(index)
}

const moveDown = (index) => {
  itemsData.value = arrayMove(itemsData.value, index, index + 1)
}

const moveUp = (index) => {
  itemsData.value = arrayMove(itemsData.value, index, index - 1)
}

// METHODS
const arrayMove = (arr, old_index, new_index) => {
    if (new_index >= arr.length) {
        var k = new_index - arr.length + 1;
        while (k--) {
            arr.push(undefined);
        }
    }
    arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);
    return arr; // for testing
}
</script>

<style src="./item.scss" lang="scss" scoped />

<template>
  <div>
    <div @click="clickHandler" :class="item.color" class="note-wrapper" clickable>
      <button class="handler" clickable>
        <IconCSS name="ph:dots-six-vertical" size="20"></IconCSS>
      </button>

      <div class="content" :contenteditable="useDevice().isDesktop">
        {{ item.content }}
      </div>
      
      <div class="note-controls-wrapper">
        <button @click="toggleControlsHandler" class="btn btn-controls" modalable>
          <IconCSS name="ph:pencil-light" size="20"></IconCSS>
        </button>
      </div>
    </div>
  </div>
</template>