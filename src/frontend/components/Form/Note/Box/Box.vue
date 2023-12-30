<script setup>
import draggable from 'vuedraggable'

const {t} = useI18n()

const props = defineProps({
  items: {
    type: [Array, Object]
  }
})

</script>

<style src="./box.scss" lang="scss" scoped />

<template>
  <div>
    <draggable 
      :list="items" 
      handle=".handler"
      ghost-class="ghost"
      item-key="id"
      tag="ul"
      class="sortable"
    >
      <template #item="{element, index}">
        <li>
          <div class="sortable-item">
            <form-note-item
              :item="element"
              class="note-item"
            >
            </form-note-item>
          </div>
        </li>
      </template>
    </draggable>
  </div>
</template>