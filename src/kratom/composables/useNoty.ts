type Noty = {
  k: number,
  title: string,
  content: string,
  type: 'primary' | 'error' | 'warning' | 'success' | null,
  duraction: number,
  countdown: number,
  intervalInstance: any,
  persistent: boolean,
  isTest: boolean
}

interface NotyObject {
  [key: string]: Noty
}

type Message = {
  title: string,
  content: string,
  type: 'primary' | 'error' | 'warning' | 'success' | null,
}

export const useNoty = () => {
  const noties = useState('messages', () => {return {} as NotyObject})
  const testNotyKey = '__test_noty__'

  const setNoty = (message: Message, duraction: number = 7000) => {
    const key = (Math.random() + 1).toString(36).substring(7)

    noties.value[key] = {
      k: +new Date, 
      ...message,
      duraction: duraction,
      countdown: (duraction - 1000) / 1000,
      intervalInstance: null,
      persistent: false,
      isTest: false
    }

    noties.value[key].intervalInstance = startInterval(key)
  }

  const setTestNoty = (message: Partial<Message> = {}, duraction: number = 7000) => {
    const baseMessage = {
      title: 'Test Notification',
      content: 'This is a test notification. You can change type to: "success", "error", "warning", or "primary"',
      type: 'primary' as const
    }

    noties.value[testNotyKey] = {
      k: +new Date, 
      ...baseMessage,
      ...message,
      duraction: duraction,
      countdown: (duraction - 1000) / 1000,
      intervalInstance: null,
      persistent: true,
      isTest: true
    }
    noties.value[testNotyKey].intervalInstance = startInterval(testNotyKey)
  }

  const removeTestNoty = () => {
    if(noties.value[testNotyKey])
      removeNoty(testNotyKey)
  }

  const removeNoty = (key: number | string) => {
    delete noties.value[key]
  }

  const playNoty = (key: string) => {
    if(!noties.value[key])
      return

    if(noties.value[key].intervalInstance === null){
      // PLAY
      noties.value[key].intervalInstance = startInterval(key)
    }else {
      // PAUSE
      clearInterval(noties.value[key].intervalInstance)
      noties.value[key].intervalInstance = null
    }
  }

  const startInterval = (key: string) => {
    if(noties.value[key] === undefined || !noties.value[key])
      return null

    return setInterval(() => {
      if(!noties.value[key]){
        return
      }

      if(noties.value[key]?.countdown === 0) {
        clearInterval(noties.value[key].intervalInstance)
        noties.value[key].intervalInstance = null
        if(!noties.value[key].persistent)
          removeNoty(key)
      } else if(noties.value[key]?.countdown > 0) {
        noties.value[key].countdown -= 1
      }
    }, 1000)
  }

  return {
    noties,
    setNoty,
    setTestNoty,
    removeTestNoty,
    playNoty,
    removeNoty
  }
}
