<script setup>
const props = defineProps({
  filter: {
    type: Object
  },
  depth: {
    default: 1
  }
})

const opened = ref([])

const openOrSelectHandler = (id) => {
  const searchedIndex = opened.value.indexOf(id)

  if(searchedIndex === -1) {
    opened.value.push(id)
  }else {
    opened.value.splice(searchedIndex, 1)
  }
}
</script>

<style src="./tree.scss" lang="scss" scoped></style>

<template>
  <ul :class="'depth-' + depth" class="tree">
    <li v-for="value in filter.values" :key="value.id" :class="{'has-children': value.children?.length}" class="item">
      <button @click="openOrSelectHandler(value.id)" class="item-btn">
        <template v-if="value.children?.length">
          <IconCSS v-if="opened.includes(value.id)" name="iconoir:minus" class="item-icon"></IconCSS>
          <IconCSS v-else name="iconoir:plus" class="item-icon"></IconCSS>
        </template>
        <template v-else>
          <div class="icon-faker"></div>
        </template>
        <span class="item-content">
          <span class="item-name">{{ value.name }}</span>
          <span v-if="value.children?.length" class="item-count">({{ value.children?.length }})</span>
        </span>
      </button>

      <template v-if="value.children?.length && opened.includes(value.id)">
        <filter-type-tree :filter="{values: value.children}" :depth="depth + 1" class="item-children"></filter-type-tree>
      </template>
    </li>
  </ul>
</template>