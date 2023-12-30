export const useReviewFaker = () => {
  const list =  [
    {
      id: 1,
      text: 'Пью витамин С всегда в осенне-зимний период, для поддержки иммунитета. Очень нравится именно этот, так как здесь оптимальная для меня дозировка, хватает на 2 месяца, приятная цена и продукт действительно высокого качества.',
      rating: 3,
      likes: 5,
      dislikes: 0,
      reviewable: {
        name: 'Гидрогелевая маска Gold & Snail, Petitfee, с улиточным муцином и коллоидным золотом, 30 мл',
        image: {
          src: '/images/products/1.png'
        },
        category: {
          name: 'Cпортивное питание',
          slug: '/fdsfsdf'
        },
        price: 140,
        rating: 5,
        link: '/fsdfsdf'
      },
      author: {
        photo: '/images/avatars/1.jpg',
        name: 'Маркиз',
        link: {
          type: 'instagram',
          href: 'https://instagram.com'
        }
      },
      created_at: new Date(),
      children: []
    },{
      id: 2,
      text: 'ABU имеет отличный выбор и качественные продукты, а также оперативную доставку и отличный сервис. Рекомендую!',
      rating: 5,
      likes: 0,
      dislikes: 0,
      reviewable: {
        name: 'Гидрогелевая маска Gold & Snail, Petitfee, с улиточным муцином и коллоидным золотом, 30 мл',
        image: {
          src: '/images/products/2.png'
        },
        category: {
          name: 'Cпортивное питание',
          slug: '/fdsfsdf'
        },
        price: 140,
        rating: 5,
        link: '/fsdfsdf'
      },
      author: {
        photo: '/images/avatars/2.jpg',
        name: 'Марина Певец',
        link: {
          type: 'facebook',
          href: 'https://facebook.com'
        }
      },
      created_at: new Date(),
      children: []
    },{
      id: 3,
      text: 'Супер, спасибо, товар получил и всем доволен!!! 5+ ',
      rating: 4,
      likes: 2,
      dislikes: 0,
      reviewable: {
        name: 'Гидрогелевая маска Gold & Snail, Petitfee, с улиточным муцином и коллоидным золотом, 30 мл',
        image: {
          src: '/images/products/3.png'
        },
        category: {
          name: 'Cпортивное питание',
          slug: '/fdsfsdf'
        },
        price: 140,
        rating: 5,
        link: '/fsdfsdf'
      },
      author: {
        photo: '/images/avatars/3.webp',
        name: 'Mr. nobody G'
      },
      created_at: new Date(),
      children: []
    },{
      id: 4,
      text: 'Этот товар помог мне улучшить состояние кожи и волос, благодаря высокому содержанию коллагена и витамина С.',
      rating: 5,
      likes: 2,
      dislikes: 0,
      reviewable: {
        name: 'Гидрогелевая маска Gold & Snail, Petitfee, с улиточным муцином и коллоидным золотом, 30 мл',
        image: {
          src: '/images/products/4.png'
        },
        category: {
          name: 'Cпортивное питание',
          slug: '/fdsfsdf'
        },
        price: 140,
        rating: 5,
        link: '/fsdfsdf'
      },
      author: {
        photo: '/images/avatars/4.jpg',
        name: 'Green Sock'
      },
      created_at: new Date(),
      children: []
    }
  ] as Review []

  return (limit: number) => {
    return list.slice(0, limit)
  }
}