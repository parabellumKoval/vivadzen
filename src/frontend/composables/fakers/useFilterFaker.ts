export const useFilterFaker = () => {
  const list =  [
    {
      id: 1,
      name: 'Страна производитель',
      type: 'Checkbox',
      values: [
        {
          id: 1,
          name: 'Англия',
          count: 5
        },{
          id: 2,
          name: 'Украина',
          count: 0
        },{
          id: 3,
          name: 'Италия',
          count: 2
        },{
          id: 4,
          name: 'Франция',
          count: 45
        },{
          id: 5,
          name: 'Бельгия',
          count: 23
        },{
          id: 6,
          name: 'Чехия',
          count: 14
        },{
          id: 3,
          name: 'Италия',
          count: 2
        },{
          id: 4,
          name: 'Франция',
          count: 45
        },{
          id: 5,
          name: 'Бельгия',
          count: 23
        },{
          id: 6,
          name: 'Чехия',
          count: 14
        }
      ]
    },{
      id: 2,
      name: 'Бренд',
      type: 'Checkbox',
      values: [
        {
          id: 1,
          name: 'All be Ukraine',
          count: 32
        },{
          id: 2,
          name: 'Powerful Progress',
          count: 1
        }
      ]
    },{
      id: 3,
      name: 'Цена',
      type: 'Doubleslider',
      min: 100,
      max: 5678,
    }
  ] as Object []

  return (limit: number) => {
    return list.slice(0, limit)
  }
}


