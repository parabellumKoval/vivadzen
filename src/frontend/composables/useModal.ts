type Options = {
  y: {
    top: number | string | 'initial',
    bottom: number | string | 'initial'
  },
  x: {
    right: number | string | 'initial',
    left: number | string | 'initial',
  },
  align: {
    x: string,
    y: string
  },
  margin: {
    x: number,
    y: number
  },
  height: {
    min: number | string | 'initial',
    max: number | string | 'initial'
  },
  width: {
    min: number | string | 'initial',
    max: number | string | 'initial'
  },
  transform: string
}

type Modal = "signInSocial" | "signInEmail" | "logInEmail" | "passwordNew" | "emailNew" | "passwordReset" | "modalMobileAccount" | "modalMobileApp" | "modalMobileMenu" | "modalMobileTableSettings" | "modalMobileTableView" | "modalMobileTableSort" | "modalMobileTableBulks" | "modalMobileTablePerpage" | "modalMobileTableActions" | "modalLangSwitcher" | "modalMobileMessageForm" | "modalMobileNote" | "ModalMobileFilters"

type ModalObject = {
  show: boolean,
  component: any,
  data?: Object | Object[] | null,
  options: Options,
  element: HTMLElement | null
}

export const useModal = () => {
  const optionsDefault = useState('optionsDefault', () => {return {
    y: {
      top: 'initial',
      bottom: '50%'
    },
    x: {
      right: '50%',
      left: 'initial',
    },
    align: {
      x: 'auto',
      y: 'auto'
    },
    margin: {
      x: 0,
      y: 0
    },
    height: {
      min: 'initial',
      max: 'initial'
    },
    width: {
      min: 'initial',
      max: 'initial'
    },
    transform: 'translateX(50%) translateY(50%)'
  } as Options})
  
  // const options = useState('options', () => {return {} as Options})
  // const element = useState('element', () => {return null as HTMLElement | null})
  
  const active = useState('active', () => {return {
    show: false,
    component: null,
    data: null,
    options: JSON.parse(JSON.stringify(optionsDefault.value)),
    element: null
  } as ModalObject})

  const activeDefault = useState('activeDefault', () => { return {
    show: false,
    component: null,
    data: null,
    options: JSON.parse(JSON.stringify(optionsDefault.value)),
    element: null
  } as ModalObject})


  // METHODS
  const show = computed(() => {
    return active.value.show
    // return active.value.component !== null && active.value.component !== undefined
  })

  const close = (name: Modal | null = null) => {
    active.value.show = false
    active.value.component = null
  }

  const open = (
    component = null,
    data: Object | String | null = null,
    target: HTMLElement | null | 'save' = null,
    options: Options | null = null,
    toggle: false
  ) => {
    if(toggle) {
      active.value.show = true
    }else {
      close()
      active.value.show = true
    }

    setTimeout(() => {
      // console.log('options', options)
      setPositions(target, options)

      active.value.component = component
      setData(data)
    }, 1)
  }

  const toggle = (component) => {
    if(active.value?.component === component)
      close()
    else
      active.value.component = component
  }

  const setData = (data: Object | String | null) => {
    active.value.data = data
  }

  const setPositions = (target: HTMLElement | null | 'save' = null, optionsData: Options | null = null) => {

    if(!target){
      active.value.options = JSON.parse(JSON.stringify(optionsDefault.value))
      active.value.element = null
      setupOptions(target, optionsData)
    }else if(target === 'save') {
      return
    } else {
      active.value.options = JSON.parse(JSON.stringify(optionsDefault.value))
      active.value.options.transform = 'initial'
      active.value.element = target
    }


    setupAlignX(optionsData)
    setupAlignY(optionsData)

    setupPositionX(optionsData)
    setupPositionY(optionsData)

    setupSizes(optionsData)

  }

  const setupOptions = (target: HTMLElement | null | 'save' = null, optionsData: Options | null = null) => {
    active.value.options.margin.x = optionsData?.margin?.x || 0
    active.value.options.margin.y = optionsData?.margin?.y || 0
    active.value.options.width.min = optionsData?.width?.min || 'initial'
    active.value.options.width.max = optionsData?.width?.max || 'initial'

    // console.log('setupOptions', active.value.options)
  }

  const setupSizes = (optionsData: Options | null = null) => {
    const elementPositionY = active.value.options.y
    const innerHeight = window.innerHeight

    if(elementPositionY.top !== 'initial' && typeof elementPositionY.top !== 'string') {
      active.value.options.height.max = innerHeight - elementPositionY.top
    }else if(elementPositionY.bottom !== 'initial' && typeof elementPositionY.bottom !== 'string') {
      active.value.options.height.max = innerHeight - elementPositionY.bottom
    }
    
      // console.log('setupSizes', elementPositionY, innerHeight, active.value.options)
  }

  const setupPositionX = (optionsData: Options | null = null) => {

    if(optionsData?.x?.left || optionsData?.x?.right) {
      active.value.options.x.left = optionsData?.x?.left || 'initial'
      active.value.options.x.right = optionsData?.x?.right || 'initial'
      return
    }

    if(!active.value.element)
      return

    const elementPosition = active.value.element.getBoundingClientRect()
    const documentWidth = document.documentElement.clientWidth

    if(active.value.options.align.x === 'left') {
      active.value.options.x.left = elementPosition.left + active.value.options.margin.x
    }else {
      active.value.options.x.right = documentWidth - elementPosition.right + active.value.options.margin.x
    }

  }

  const setupPositionY = (optionsData: Options | null = null) => {

    if(optionsData?.y?.top || optionsData?.y?.bottom) {
      active.value.options.y = {
        ...active.value.options.y,
        ...optionsData.y
      }

      return
    }

    if(!active.value.element)
      return

    const elementPosition = active.value.element.getBoundingClientRect()
    const innerHeight = window.innerHeight

    if(active.value.options.align.y === 'top') {
      active.value.options.y.top = elementPosition.top + active.value.options.margin.y
    }else {
      active.value.options.y.bottom = innerHeight - elementPosition.bottom + active.value.options.margin.y
    }

  }

  const setupAlignY = (optionsData: Options | null = null) => {
    if(optionsData?.align?.y) {
      active.value.options.align.y = optionsData.align.y
      return
    }

    if(!active.value.element)
      return

    const elementPosition = active.value.element.getBoundingClientRect()
    const innerHeight = window.innerHeight

    const freeSpace = {
      top: elementPosition.top,
      bottom: innerHeight - elementPosition.bottom
    }

    if(freeSpace.top > freeSpace.bottom){
      active.value.options.align.y = 'bottom'
    }else {
      active.value.options.align.y = 'top'
    }
  }

  const setupAlignX = (optionsData: Options | null = null) => {
    if(optionsData?.align?.x) {
      active.value.options.align.x = optionsData.align.x
      return
    }

    if(!active.value.element)
      return

    const elementPosition = active.value.element.getBoundingClientRect()
    const documentWidth = document.documentElement.clientWidth

    const freeSpace = {
      left: elementPosition.left,
      right: documentWidth - elementPosition.right
    }

    if(freeSpace.left > freeSpace.right){
      active.value.options.align.x = 'right'
    }else {
      active.value.options.align.x = 'left'
    }
  }

  return {
    close,
    open,
    show: show.value,
    toggle,
    setData,
    setPositions,
    active: active.value
  }
}