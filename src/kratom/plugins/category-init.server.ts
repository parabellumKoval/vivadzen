import { useCategoryStore } from '~/store/category'

export default defineNuxtPlugin(async () => {
  if (process.client) {
    return
  }

  const categoryStore = useCategoryStore()
  const tasks: Promise<unknown>[] = []

  if (!Array.isArray(categoryStore.list) || categoryStore.list.length === 0) {
    tasks.push(categoryStore.listCached())
  }

  if (!Array.isArray(categoryStore.mainList) || categoryStore.mainList.length === 0) {
    tasks.push(categoryStore.listMainCached())
  }

  if (!tasks.length) {
    return
  }

  try {
    await Promise.all(tasks)
  } catch (error) {
    console.error('[category-init] Failed to hydrate categories on server', error)
  }
})
