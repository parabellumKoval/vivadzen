export const useTgUiStore = defineStore('tgUiStore', {
  state: () => ({
    cartOpen: false,
    toast: null as { content: string; type?: 'success' | 'error' | 'info' } | null,
    toastTimer: null as ReturnType<typeof setTimeout> | null
  }),

  actions: {
    openCart() {
      this.cartOpen = true
    },

    closeCart() {
      this.cartOpen = false
    },

    showToast(content: string, type: 'success' | 'error' | 'info' = 'info') {
      this.toast = { content, type }

      if (this.toastTimer) {
        clearTimeout(this.toastTimer)
      }

      this.toastTimer = setTimeout(() => {
        this.toast = null
        this.toastTimer = null
      }, 5000)
    }
  }
})
