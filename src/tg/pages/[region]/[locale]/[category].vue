<script setup lang="ts">
const route = useRoute()
const category = computed(() => String(route.params.category || ''))
const { categoryPath } = useTgRouting()

definePageMeta({
  key: (route) => `${route.params.region}-${route.params.locale}-${route.params.category}`
})

await navigateTo(categoryPath(category.value), { replace: true })
</script>
