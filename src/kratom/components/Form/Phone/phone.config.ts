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
  },
  at: {
    dialCode: '+43',
    mask: '+43 ### ### ####',
    maxDigits: 10,
    placeholder: '+43 ___ ___ ____'
  },
  be: {
    dialCode: '+32',
    mask: '+32 ### ## ## ##',
    maxDigits: 9,
    placeholder: '+32 ___ __ __ __'
  },
  bg: {
    dialCode: '+359',
    mask: '+359 ### ### ###',
    maxDigits: 9,
    placeholder: '+359 ___ ___ ___'
  },
  ch: {
    dialCode: '+41',
    mask: '+41 ## ### ## ##',
    maxDigits: 9,
    placeholder: '+41 __ ___ __ __'
  },
  cy: {
    dialCode: '+357',
    mask: '+357 ## ### ###',
    maxDigits: 8,
    placeholder: '+357 __ ___ ___'
  },
  dk: {
    dialCode: '+45',
    mask: '+45 ## ## ## ##',
    maxDigits: 8,
    placeholder: '+45 __ __ __ __'
  },
  ee: {
    dialCode: '+372',
    mask: '+372 #### ####',
    maxDigits: 8,
    placeholder: '+372 ____ ____'
  },
  fi: {
    dialCode: '+358',
    mask: '+358 ## ### ####',
    maxDigits: 9,
    placeholder: '+358 __ ___ ____'
  },
  fr: {
    dialCode: '+33',
    mask: '+33 # ## ## ## ##',
    maxDigits: 9,
    placeholder: '+33 _ __ __ __ __'
  },
  gb: {
    dialCode: '+44',
    mask: '+44 #### ### ####',
    maxDigits: 11,
    placeholder: '+44 ____ ___ ____'
  },
  gr: {
    dialCode: '+30',
    mask: '+30 ### ### ####',
    maxDigits: 10,
    placeholder: '+30 ___ ___ ____'
  },
  hr: {
    dialCode: '+385',
    mask: '+385 ## ### ####',
    maxDigits: 9,
    placeholder: '+385 __ ___ ____'
  },
  hu: {
    dialCode: '+36',
    mask: '+36 ## ### ####',
    maxDigits: 9,
    placeholder: '+36 __ ___ ____'
  },
  ie: {
    dialCode: '+353',
    mask: '+353 ## ### ####',
    maxDigits: 9,
    placeholder: '+353 __ ___ ____'
  },
  it: {
    dialCode: '+39',
    mask: '+39 ### ### ####',
    maxDigits: 10,
    placeholder: '+39 ___ ___ ____'
  },
  lt: {
    dialCode: '+370',
    mask: '+370 ### #####',
    maxDigits: 8,
    placeholder: '+370 ___ _____'
  },
  lu: {
    dialCode: '+352',
    mask: '+352 ### ### ###',
    maxDigits: 9,
    placeholder: '+352 ___ ___ ___'
  },
  lv: {
    dialCode: '+371',
    mask: '+371 ## ### ###',
    maxDigits: 8,
    placeholder: '+371 __ ___ ___'
  },
  mt: {
    dialCode: '+356',
    mask: '+356 #### ####',
    maxDigits: 8,
    placeholder: '+356 ____ ____'
  },
  nl: {
    dialCode: '+31',
    mask: '+31 ## ### ####',
    maxDigits: 9,
    placeholder: '+31 __ ___ ____'
  },
  no: {
    dialCode: '+47',
    mask: '+47 ### ## ###',
    maxDigits: 8,
    placeholder: '+47 ___ __ ___'
  },
  pl: {
    dialCode: '+48',
    mask: '+48 ### ### ###',
    maxDigits: 9,
    placeholder: '+48 ___ ___ ___'
  },
  pt: {
    dialCode: '+351',
    mask: '+351 ### ### ###',
    maxDigits: 9,
    placeholder: '+351 ___ ___ ___'
  },
  ro: {
    dialCode: '+40',
    mask: '+40 ### ### ###',
    maxDigits: 9,
    placeholder: '+40 ___ ___ ___'
  },
  se: {
    dialCode: '+46',
    mask: '+46 ## ### ####',
    maxDigits: 9,
    placeholder: '+46 __ ___ ____'
  },
  si: {
    dialCode: '+386',
    mask: '+386 ## ### ###',
    maxDigits: 8,
    placeholder: '+386 __ ___ ___'
  },
  sk: {
    dialCode: '+421',
    mask: '+421 ### ### ###',
    maxDigits: 9,
    placeholder: '+421 ___ ___ ___'
  }
}
