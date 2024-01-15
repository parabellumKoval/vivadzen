import {useProductFaker} from '~/composables/fakers/useProductFaker.ts'

export const useOrderFaker = () => {

  const list = [
    {
      id: 1,
      code: 444554,
      created_at: Date.now(),
      status: 'new',
      price: 5453,
      products: useProductFaker()(4),
      payment: {
        method: 'liqpay',
      },
      delivery: {
        method: 'warehouse',
        city: 'Черновцы',
        warehouse: '2',
        address: 'пр. Героев Альпийских горок',
        zip: '61000',
      },
      user: {
        firstname: 'Маргаритта',
        lastname: 'Зубер',
        phone: '+380982546327',
        email: 'olga2323@gmail.com',
      },
      comment: 'Комментарий'
    },{
      id: 2,
      code: 444554,
      created_at: Date.now(),
      status: 'new',
      price: 1290,
      products: useProductFaker()(2),
      payment: {
        method: 'liqpay',
      },
      delivery: {
        method: 'warehouse',
        city: 'Черновцы',
        warehouse: '2',
        address: 'пр. Героев Альпийских горок',
        zip: '61000',
      },
      user: {
        firstname: 'Маргаритта',
        lastname: 'Зубер',
        phone: '+380982546327',
        email: 'olga2323@gmail.com',
      },
      comment: 'Комментарий'
    },{
      id: 2,
      code: 444554,
      created_at: Date.now(),
      status: 'new',
      price: 154,
      products: useProductFaker()(1),
      payment: {
        method: 'liqpay',
      },
      delivery: {
        method: 'warehouse',
        city: 'Черновцы',
        warehouse: '2',
        address: 'пр. Героев Альпийских горок',
        zip: '61000',
      },
      user: {
        firstname: 'Маргаритта',
        lastname: 'Зубер',
        phone: '+380982546327',
        email: 'olga2323@gmail.com',
      },
      comment: 'Комментарий'
    }
  ] as Object[]

  return (limit: number) => {
    return list.slice(0, limit)
  }
}