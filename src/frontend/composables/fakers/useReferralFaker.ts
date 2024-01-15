export const useReferralFaker = () => {

  const list = [
    {
      id: 1,
      status: 'active',
      income: 1032,
      referrals: [
        {
          id: 2,
          status: 'active',
          income: 200,
          referrals: [],
          user: {
            photo: '/images/avatars/2.jpg',
            name: 'Маргаритта Зубер',
            phone: '+380982546327',
            email: 'olga2323@gmail.com',
          },
        },{
          id: 3,
          status: 'active',
          income: 50,
          referrals: [],
          user: {
            photo: '/images/avatars/4.jpg',
            name: 'Маргаритта Зубер',
            phone: '+380982546327',
            email: 'olga2323@gmail.com',
          },
        }
      ],
      user: {
        photo: '/images/avatars/1.jpg',
        name: 'Маргаритта Зубер',
        phone: '+380982546327',
        email: 'olga2323@gmail.com',
      },
    },{
      id: 2,
      status: 'active',
      income: 200,
      referrals: [],
      user: {
        photo: '/images/avatars/2.jpg',
        name: 'Маргаритта Зубер',
        phone: '+380982546327',
        email: 'olga2323@gmail.com',
      },
    },{
      id: 3,
      status: 'active',
      income: 50,
      referrals: [],
      user: {
        photo: '/images/avatars/4.jpg',
        name: 'Маргаритта Зубер',
        phone: '+380982546327',
        email: 'olga2323@gmail.com',
      },
    }
  ] as Object[]

  return (limit: number) => {
    return list.slice(0, limit)
  }
}