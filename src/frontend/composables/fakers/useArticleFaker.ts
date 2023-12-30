export const useArticleFaker = () => {

  const list = [
    {
      id: 1,
      title: 'Пищеварительные ферменты – какую функцию они выполняют в организме и почему они так важны?',
      slug: '/',
      image: {
        src: 'https://woman.ua/media/cache/0a/f0/0af04e553cd1975194d2eea4c28341dc.jpg'
      },
      time: 7,
    },{
      id: 2,
      title: 'Добавка NAC - недооцененный  ингредиент предшественник глутатиона',
      slug: '/',
      image: {
        src: 'https://fitseven.ru/wp-content/uploads/2019/07/pitanie-pri-trenirovkah-na-massu.jpg'
      },
      time: 15,
    },{
      id: 3,
      title: 'Лесной орех Карпат. Полезные, лечебные свойства, рецепты. В Карпатах открыт сезон сбора карпатского лесного ореха.',
      slug: '/',
      image: {
        src: 'https://growfood.pro/blog/wp-content/uploads/2017/11/IMG_1481.jpg'
      },
      time: 4,
    },{
      id: 4,
      title: 'Гидрогелевая маска Gold & Snail, Petitfee, с улиточным муцином и коллоидным золотом, 30 мл',
      slug: '/',
      image: {
        src: 'https://life.pravda.com.ua/images/doc/f/7/f7cdeb3-sport-big.jpg'
      },
      time: 9,
    },
    {
      id: 5,
      title: 'Пищеварительные ферменты – какую функцию они выполняют в организме и почему они так важны?',
      slug: '/',
      image: {
        src: 'https://www.med157.ru/images/articles/2020-03-16_00_24_55_pitanie.jpg'
      },
      time: 7,
    },{
      id: 6,
      title: 'Добавка NAC - недооцененный  ингредиент предшественник глутатиона',
      slug: '/',
      image: {
        src: 'https://fitseven.ru/wp-content/uploads/2019/07/pitanie-pri-trenirovkah-na-massu.jpg'
      },
      time: 15,
    },{
      id: 7,
      title: 'Лесной орех Карпат. Полезные, лечебные свойства, рецепты. В Карпатах открыт сезон сбора карпатского лесного ореха.',
      slug: '/',
      image: {
        src: 'https://www.gastronom.ru/binfiles/images/20141105/bcecef86.jpg'
      },
      time: 4,
    },{
      id: 8,
      title: 'Гидрогелевая маска Gold & Snail, Petitfee, с улиточным муцином и коллоидным золотом, 30 мл',
      slug: '/',
      image: {
        src: 'https://life.pravda.com.ua/images/doc/f/7/f7cdeb3-sport-big.jpg'
      },
      time: 9,
    }
  ] as Article[]

  return (limit: number) => {
    return list.slice(0, limit)
  }
}