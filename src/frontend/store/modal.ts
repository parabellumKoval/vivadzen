export const useModalStore = defineStore('modalStore', {

  state: () => ({
    signInSocial: {
      isShow: false
    } as ModalObject,

    signInEmail: {
      isShow: false
    } as ModalObject,

    logInEmail: {
      isShow: false
    } as ModalObject,

    passwordNew: {
      isShow: false
    } as ModalObject,

    emailNew: {
      isShow: false
    } as ModalObject,

    passwordReset: {
      isShow: false,
      data: null
    } as ModalObject,

    cart: {
      isShow: false
    } as ModalObject,

    review: {
      isShow: false,
      data: null
    } as ModalObject,

    menuMobile: {
      isShow: false
    } as ModalObject,
  }),
  
  getters: {
    show: (state) => {
      return (name: Modal) => state[name].isShow
    },

    data: (state) => {
      return (name: Modal) => state[name].data
    },
  },

  actions: {
    close(name: Modal) {
      this[name].isShow = false
    },

    open(name: Modal) {
      this.closeAll()
      this[name].isShow = true
    },

    toggle(name: Modal) {
      this[name].isShow = !this[name].isShow
    },
    
    closeAll() {
      for (const [key, value] of Object.entries(this.$state)) {
        this[key].isShow = false
      }
    },

    setData(name: Modal, data: Object | String) {
      this[name].data = data
    }
  },
})