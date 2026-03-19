export type PhoneMaskConfig = {
  dialCode: string
  mask: string
  maxDigits?: number
  placeholder?: string
}

export const DEFAULT_PHONE_MASKS: Record<string, PhoneMaskConfig> = {
  ua: {
    dialCode: '+380',
    mask: '+380 (##) ###-##-##',
    maxDigits: 9,
    placeholder: '+380 (__) ___-__-__'
  },
  cz: {
    dialCode: '+420',
    mask: '+420 ### ### ###',
    maxDigits: 9,
    placeholder: '+420 ___ ___ ___'
  },
  de: {
    dialCode: '+49',
    mask: '+49 #### ### ####',
    maxDigits: 11,
    placeholder: '+49 ____ ___ ____'
  },
  es: {
    dialCode: '+34',
    mask: '+34 ### ### ###',
    maxDigits: 9,
    placeholder: '+34 ___ ___ ___'
  }
}
