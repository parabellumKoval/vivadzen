export const usePromocodeFaker = () => {
  const list =  [
    {
      id: 1,
      code: '231234',
      status: 'active',
      value: '-10%',
      created_at: Date.now()
    },{
      id: 2,
      code: '231234',
      status: 'used',
      value: '-5%',
      created_at: Date.now()
    },{
      id: 3,
      code: '231234',
      status: 'used',
      value: '-12%',
      created_at: Date.now()
    },{
      id: 4,
      code: '231234',
      status: 'expired',
      value: '-20%',
      created_at: Date.now()
    }
  ] as Object []

  return (limit: number) => {
    return list.slice(0, limit)
  }
}


