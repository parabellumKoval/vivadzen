import { defineStore } from 'pinia'

interface User {
  id: number
  name: string
  email: string
  role: string
  last_login_at?: string | null
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null as string | null,
    user: null as User | null,
  }),

  getters: {
    isAuthenticated: (s) => !!s.token,
  },

  actions: {
    setSession(token: string, user: User) {
      this.token = token
      this.user = user
    },
    clear() {
      this.token = null
      this.user = null
    },
  },

  persist: true,
})
