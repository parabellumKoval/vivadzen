<script setup lang="ts">
import type { Crumb } from '~/composables/useCrumbs'
import { applyCrumbDefaults } from '~/composables/useCrumbs'

const props = defineProps<{ crumbs: Crumb[] }>()

const crumbs = computed(() => applyCrumbDefaults(props.crumbs ?? []))
const regionPath = useToLocalePath()

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
        :key="`${crumb.item}-${index}`"
        class="item"
        scrollable
      >
        <NuxtLink
          v-if="index + 1 !== crumbs.length"
          :to="regionPath(crumb.item)"
          :prefetch="false"
          clickable
          scrollable
          class="breadcrumbs__link link"
        >
          <template v-if="$device.isMobile && crumb.icon">
            <IconCSS :name="crumb.icon" class="breadcrumbs__icon" />
          </template>
          <template v-else>
            {{ crumb.name }}
          </template>
        </NuxtLink>
        <span v-else class="breadcrumbs__link">
          <template v-if="$device.isMobile && crumb.icon">
            <IconCSS :name="crumb.icon" class="breadcrumbs__icon" />
          </template>
          <template v-else>
            {{ crumb.name }}
          </template>
        </span>
      </li>
    </ul>
  </section>
</template>
