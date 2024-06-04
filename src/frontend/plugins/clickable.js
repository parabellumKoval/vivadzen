const vClickable = {
  timer: null,

  setClass(el) {
    el.classList.add('clickable')
  },

  clickHandler(event){
    const el = event.currentTarget || event.target

    el.classList.add('clickable--effect')

    clearTimeout(vClickable.timer)
    
    vClickable.timer = setTimeout(() => {
      el.classList.remove('clickable--effect')
    }, 600)

    console.log('event', event)
  },

  mounted: (el, binding, vnode, prevVnode) => {
    
    if(window.innerWidth > 1024)
      return

    // setup
    vClickable.setClass(el)

    // set event handler
    el.addEventListener('click', vClickable.clickHandler);
  },

  beforeUnmount: (el, binding, vnode, prevVnode) => {
    el.removeEventListener('click', vClickable.clickHandler)
  }
}

export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.directive('clickable', vClickable)
})