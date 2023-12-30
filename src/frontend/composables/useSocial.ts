export const useSocial = () => {
  const messengers = [
    {
      id: 1,
      link: 'https://t.me/Allbeukraine',
      key: 'telegram',
      name: 'Telegram',
      icon: 'ph:telegram-logo',
    },{
      id: 2,
      link: '/',
      key: 'viber',
      name: 'Viber',
      icon: 'la:viber',
    },{
      id: 3,
      link: '/',
      key: 'whatsup',
      name: 'WhatsUp',
      icon: 'ph:whatsapp-logo',
    }
  ]

  const networks = [
    {
      id: 4,
      link: '/',
      key: 'facebook',
      name: 'Facebook',
      icon: 'iconoir:facebook',
    },{
      id: 5,
      link: 'https://www.instagram.com/abu.com.ua/?igshid=ZDc4ODBmNjlmNQ%3D%3D',
      key: 'instagram',
      name: 'Instagram',
      icon: 'iconoir:instagram',
    },{
      id: 6,
      link: '/',
      key: 'tiktok',
      name: 'Tik-tok',
      icon: 'mingcute:tiktok-line',
    }
  ]

  const all = [
    ...messengers,
    ...networks,
  ]

  return {
    all,
    messengers,
    networks
  }
}