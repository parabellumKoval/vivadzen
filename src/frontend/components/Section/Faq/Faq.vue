<script setup>

const props = defineProps({
  items: {
    type: Array,
    default: []
  }
})

const openIndex = ref(null)



const openHandler = (index) => {
  if(openIndex.value === index) {
    openIndex.value = null
  }else {
    openIndex.value = index
  }
}
</script>

<style src="./faq.scss" lang="scss" scoped></style>
<style src="~/assets/scss/_rich-text.scss" lang="scss" scoped></style>

<template>
  <ul class="faq-list">
    <li v-for="(item, index) in items" :key="item.id" :class="{active: openIndex === index}" class="faq-item">
      <button @click="openHandler(index)" class="faq-header">
        <span class="faq-count">{{ index + 1 }}</span>
        <div class="faq-title">{{ item.q }}</div>
        <span class="faq-arrow">
          <IconCSS name="iconoir:nav-arrow-down" class="faq-icon"></IconCSS>
        </span>
      </button>
      <div class="faq-content rich-text">
        <template v-if="Array.isArray(item.a)">
          <p v-for="(htmlItem, index) in item.a" :key="index">
            <template v-if="Array.isArray(htmlItem)">
              <ul>
                <li v-for="(li, index) in htmlItem" :key="li.id">
                  <template v-if="li.slug">
                    <NuxtLink :to="localePath('/' + li.slug)">{{ li.name }}</NuxtLink>
                  </template>
                  <template v-else>
                    <span v-html="li"></span>
                  </template>
                </li>
              </ul>
            </template>
            <template v-else>
              <span v-html="htmlItem"></span>
            </template>
          </p>
        </template>
        <template v-else>
          <div v-html="item.a"></div>
        </template>
      </div>
    </li>
  </ul>
</template>