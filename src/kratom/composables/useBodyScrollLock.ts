const SCROLL_LOCK_CLASS = 'modal-open-ios'

let lockCount = 0
let scrollPosition = 0

const getScrollTop = () =>
  window.pageYOffset ?? window.scrollY ?? 0

const getBody = () => document.body

const onLock = () => {
  const body = getBody()
  if (!body) return

  scrollPosition = getScrollTop()
  body.classList.add(SCROLL_LOCK_CLASS)
  body.style.top = `-${scrollPosition}px`
}

const onUnlock = () => {
  const body = getBody()
  if (!body) return

  body.classList.remove(SCROLL_LOCK_CLASS)
  body.style.top = ''
  window.scrollTo(0, scrollPosition)
}

export const useBodyScrollLock = () => {
  const disableScroll = () => {
    if (!process.client) return

    lockCount += 1
    if (lockCount > 1) return

    onLock()
  }

  const enableScroll = () => {
    if (!process.client || lockCount === 0) return

    lockCount -= 1
    if (lockCount > 0) return

    onUnlock()
  }

  return {
    disableScroll,
    enableScroll,
  }
}
