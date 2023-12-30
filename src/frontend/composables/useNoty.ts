export const useNoty = () => {
  const noties = useState('messages', () => {return {}})

  const setNoty = (message: String, timeout: Number = 3000) => {
    const key = (Math.random() + 1).toString(36).substring(7)

    noties.value[key] = {
      k: +new Date, 
      v: message
    }

    setTimeout(() => {
      removeNoty(key)
    }, timeout)
  }

  const removeNoty = (key: Number | String) => {
    delete noties.value[key]
  }

  return {
    noties,
    setNoty,
    removeNoty
  }
}