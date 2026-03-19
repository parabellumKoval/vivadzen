export const useSocial = () => {
  const {get} = useSettings()

  const hasLink = (link: unknown) => {
    return typeof link === 'string' && link.trim().length > 0
  }

  const messengers = computed(() =>  {
    return [
      {
        id: 1,
        link: get('site.contacts.social.telegram'),
        key: 'telegram',
        name: 'Telegram',
        icon: 'ph:telegram-logo',
      },{
        id: 2,
        link: get('site.contacts.social.viber'),
        key: 'viber',
        name: 'Viber',
        icon: 'la:viber',
      },{
        id: 3,
        link: get('site.contacts.social.whatsapp'),
        key: 'whatsapp',
        name: 'Whatsapp',
        icon: 'ph:whatsapp-logo',
      }
    ].filter((item) => hasLink(item.link));
  });

  const networks = computed(() =>  {
    return [
      {
        id: 5,
        link: get('site.contacts.social.instagram'),
        key: 'instagram',
        name: 'Instagram',
        icon: 'iconoir:instagram',
      },
      {
        id: 6,
        link: get('site.contacts.social.facebook'),
        key: 'facebook',
        name: 'Facebook',
        icon: 'iconoir:facebook',
      },
      {
        id: 7,
        link: get('site.contacts.social.youtube'),
        key: 'youtube',
        name: 'YouTube',
        icon: 'iconoir:youtube',
      }
    ].filter((item) => hasLink(item.link));
  });

  const all = [
      ...messengers.value,
      ...networks.value,
    ]

  return {
    all,
    messengers,
    networks
  }
}
