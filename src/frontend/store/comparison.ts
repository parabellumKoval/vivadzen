import { defineStore } from "pinia";

export const useComparisonStore = defineStore('comparisonStore', () => {
  const ids = ref([] as Number[])

  const toggle = async (id: number) => {
    const index = ids.value.indexOf(id)

    if(index === -1) {
      ids.value.push(id)
      return 'add'
    }else {
      ids.value.splice(index, 1)
      return 'remove'
    }
  }

  const isActive = (id: number) => {
    if(ids.value.includes(id)){
      return true
    }else {
      return false
    }
  }

  return {
    ids,
    toggle,
    isActive
  }
}, {
  persist: true
})