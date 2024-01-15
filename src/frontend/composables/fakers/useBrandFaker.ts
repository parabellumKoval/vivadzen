export const useBrandFaker = () => {

  const list = [
    {
      id: 1,
      name: "A'pieu",
      slug: 'fsdfsdf',
      image: {
        src: 'https://woman.ua/media/cache/0a/f0/0af04e553cd1975194d2eea4c28341dc.jpg'
      }
    },{
      id: 2,
      name: 'A&D',
      slug: 'ghfghfg',
      image: {
        src: 'https://fitseven.ru/wp-content/uploads/2019/07/pitanie-pri-trenirovkah-na-massu.jpg'
      }
    },{
      id: 3,
      name: 'Acnegen',
      slug: 'hfghfgh',
      image: {
        src: 'https://growfood.pro/blog/wp-content/uploads/2017/11/IMG_1481.jpg'
      }
    },{
      id: 4,
      name: 'AHC',
      slug: 'hfghfgh',
      image: {
        src: 'https://life.pravda.com.ua/images/doc/f/7/f7cdeb3-sport-big.jpg'
      }
    },
    {
      id: 5,
      name: 'Activlab',
      slug: 'fsdf',
      image: {
        src: 'https://www.med157.ru/images/articles/2020-03-16_00_24_55_pitanie.jpg'
      }
    },{
      id: 6,
      name: 'Albert Daviv',
      slug: 'hfghfgh',
      image: {
        src: 'https://fitseven.ru/wp-content/uploads/2019/07/pitanie-pri-trenirovkah-na-massu.jpg'
      }
    },{
      id: 7,
      name: 'ABU',
      slug: 'gfhfgh',
      image: {
        src: 'https://www.gastronom.ru/binfiles/images/20141105/bcecef86.jpg'
      }
    },{
      id: 8,
      name: 'Badger Company',
      slug: 'sdfsdf',
      image: {
        src: 'https://life.pravda.com.ua/images/doc/f/7/f7cdeb3-sport-big.jpg'
      }
    },{
      id: 9,
      name: 'Bioaqua',
      slug: 'sdfsdf',
      image: {
        src: 'https://life.pravda.com.ua/images/doc/f/7/f7cdeb3-sport-big.jpg'
      }
    },{
      id: 10,
      name: 'BlenderBottle',
      slug: 'sdfsdf',
      image: {
        src: 'https://life.pravda.com.ua/images/doc/f/7/f7cdeb3-sport-big.jpg'
      }
    }
  ] as Object[]

  const brandGroups = () => {
    const groups = {}

    for(let i = 0; i < list.length; i++){
      const item = list[i]

      if(groups[item.name[0]] === undefined) {
        groups[item.name[0]] = []
      }

      groups[item.name[0]].push(item)
    }

    return groups
  }

  return {
    groups: brandGroups()
  }
}