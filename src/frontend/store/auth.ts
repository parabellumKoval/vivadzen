import { useLoginMock } from '~/composables/mockups/useLoginMock';
import { useProfileMock } from '~/composables/mockups/useProfileMock';

export const useAuthStore = defineStore('authStore', {
  persist: true,

  state: () => ({
    loginForm: {...useLoginMock()} as LoginForm,

    userState: null,
    
    authState: false,

    redirectToState: '/account/order-history',
  }),

  getters: {
    getLogin: (state) => state.loginForm,
    user: (state) => state.userState,
    getErrors: (state) => state.errors,
    auth: (state) => state.authState,
    redirectTo: (state) => state.redirectToState,
    getReferrerCode: (state) => state.referrerCode,
    orderable: (state) => {
      return {
        orderable_id: state.authState? state.userState.id: null,
        orderable_type: state.authState? 'supabase': null
      }
    },
    avatar: (state) => {
      return state.userState.photo? state.userState.photo: '/images/account.png'
    }
  },

  actions: {
    resetUser() {
      this.authState = false
      this.userState = null
      // this.userState = {...useProfileMock()}
    },

    setUserFromSession(supauser) {
      if(!supauser)
        return
      
      this.authState = true

      this.userState = {
        id: supauser.id,
        email: supauser.email,
        fullname: supauser.user_metadata.full_name,
        photo: supauser.user_metadata.avatar_url,
        phone: supauser.user_metadata.phone,
        telegram: supauser.user_metadata.telegram
      }
    },

    setAuthUser() {
      const supauser = useSupabaseUser()

      if(supauser.value) {
        this.authState = true

        this.userState = {
          id: supauser.value.id,
          email: supauser.value.email,
          fullname: supauser.value.user_metadata.full_name,
          photo: supauser.value.user_metadata.avatar_url,
          phone: supauser.value.user_metadata.phone,
          telegram: supauser.value.user_metadata.telegram
        }
      }else {
        this.authState = false
      }
    },
    
    resetLoginForm() {
      this.loginForm = {...useLoginMock()}
    },

    setIsAuth(value: boolean) {
      this.authenticated = value
    },

    setProfileData(data: Profile) {
      Object.keys(data).forEach((k) => data[k] == null && delete data[k]);

      this.user = {
        ...this.user,
        ...data
      }
    },

    async logout() {
      const { auth } = useSupabaseAuthClient()

      return await auth.signOut().then(() => {
        this.resetUser()
      })
    },

    async oAuth(provider: 'google' | 'facebook', redirectTo: String = '/') {
      const runtimeConfig = useRuntimeConfig()
      const { auth } = useSupabaseAuthClient()

      const { data, error } = await auth.signInWithOAuth({
        provider: provider,
        options: {
          redirectTo: `${runtimeConfig.public.frontendUrl}${redirectTo}`
        }
      })
    },

    async login(data: Object) {
      const { auth } = useSupabaseAuthClient()
      
      return await auth.signInWithPassword({
        email: data.email,
        password: data.password,
      })
    },

    async register(data: Object) {
      const { auth } = useSupabaseAuthClient()
      return await auth.signUp({
        email: data.email,
        password: data.password,
        options: {
          data: {
            full_name: data.fullname,
          }
        }
      })
    },

    async update(newData: Object, redirect: String = '/') {
      const { auth } = useSupabaseAuthClient()
      return await auth.updateUser({
        ...newData
      }, {
        redirectTo: redirect
      })
    },

    async sendPasswordResetLink(email: string, redirect: String = '/password-recovery') {
      const { auth } = useSupabaseAuthClient()

      return await auth.resetPasswordForEmail(email, {
        redirectTo: `${useRuntimeConfig().public.frontendUrl}${redirect}`,
      })
    },

    async changePassword(data: changePassword) {
      const runtimeConfig = useRuntimeConfig()
      const url = `${runtimeConfig.public.base}/change-password`
    },

    // async register(data: Auth) {
    //   const runtimeConfig = useRuntimeConfig()
    //   const url = `${runtimeConfig.public.base}/register`

    // },
  },
})