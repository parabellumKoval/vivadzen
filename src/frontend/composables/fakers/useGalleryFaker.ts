export const useGalleryFaker = () => {
  const list =  [
    {
      alt: '',
      title: 'Гидрогелевая маска Gold & Snail, Petitfee, с улиточным муцином и коллоидным золотом, 30 мл',
      src: '/images/products/1.png',
    },{
      alt: '',
      name: 'Жидкий йод (йодид калия) "Liquid Iodine Plus" Life-flo, 150 мкг, 59 мл',
      src: '/images/products/2.png',
    },{
      alt: '',
      name: 'Пророщенные зерна, Добра їжа, измельченные пророщенные зерна, 300 г',
      src: '/images/products/3.png',
    },{
      alt: '',
      name: 'Гидрогелевая маска Gold & Snail, Petitfee, с улиточным муцином и коллоидным золотом, 30 мл',
      src: '/images/products/4.png',
    },{
      alt: '',
      name: 'Гидрогелевая маска Gold & Snail, Petitfee, с улиточным муцином и коллоидным золотом, 30 мл',
      src: '/images/products/5.png',
    },{
      alt: '',
      name: 'Жидкий йод (йодид калия) "Liquid Iodine Plus" Life-flo, 150 мкг, 59 мл',
      src: '/images/products/6.png',
    },{
      alt: '',
      name: 'Пророщенные зерна, Добра їжа, измельченные пророщенные зерна, 300 г',
      src: '/images/products/7.png',
    },{
      alt: '',
      name: 'Гидрогелевая маска Gold & Snail, Petitfee, с улиточным муцином и коллоидным золотом, 30 мл',
      src: '/images/products/8.png',
    }
  ]

  return (limit: number) => {
    return list.slice(0, limit)
  }
}