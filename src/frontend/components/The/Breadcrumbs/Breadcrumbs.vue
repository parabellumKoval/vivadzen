<script setup>
  const props = defineProps({
    crumbs: {
      type: Array
    }
  })

  useSchemaOrg([
    defineBreadcrumb({
      itemListElement: props.crumbs
    }),
  ])
</script>

<style src="./breadcrumbs.scss" lang="sass" scoped />

<template>
  <section class="breadcrumbs">
    <ul class="list" scrollable>
      <li
        v-for="(crumb, index) in crumbs"
        :key="crumb"
        class="item"
        scrollable
      >
        <NuxtLink
          v-if="index + 1 !== crumbs.length"
          :to="crumb.item"
          :prefetch="false"
          clickable
          scrollable
          class="breadcrumbs__link link"
        >
          {{ crumb.name }}
        </NuxtLink>
        <span v-else class="breadcrumbs__link">{{ crumb.name }}</span>
      </li>
    </ul>
  </section>
</template>