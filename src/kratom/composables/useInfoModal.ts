import { defineAsyncComponent } from 'vue'

export const useInfoModal = () => {
  /**
   * Open info modal with custom message
   * @param {Object} options - Options object
   * @param {string} options.title - Modal title
   * @param {string} options.message - Modal message
   * @param {string} [options.type='default'] - Modal type: 'default', 'success', 'error', 'warning'
   * @param {string} [options.buttonText='ок'] - Button text
   * @param {Object} [options.modalOptions] - Additional modal options (width, etc)
   */
  const open = (options: {
    title: string
    message: string
    type?: 'default' | 'success' | 'error' | 'warning'
    buttonText?: string
    modalOptions?: Record<string, any>
  }) => {
    const {
      title,
      message,
      type = 'default',
      buttonText = 'ок',
      modalOptions = {}
    } = options

    const defaultOptions = {
      width: { max: 420 },
      ...modalOptions
    }

    useModal().open(
      defineAsyncComponent(() => import('~/components/Modal/Info/Info.vue')),
      {
        title,
        message,
        buttonText,
        type
      },
      null,
      defaultOptions
    )
  }

  return {
    open
  }
}
