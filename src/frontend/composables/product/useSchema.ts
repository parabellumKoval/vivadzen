export const useSchema = () => {

  const setSchema = (product: Product, reviews: Review[]) => {
    const imageUrl = product.images && product.images[0]?.src? `${useRuntimeConfig().public.base}/${product.images[0]?.src}`: null
    
    const scheema = {
      name: product.name,
      image: imageUrl,
      description: product.content,
      sku: product.code,
      brand: 'Djini',
      offers: [
        { 
          price: product.price,
          priceCurrency: 'UAH'
        },
      ],
      aggregateRating: {
        ratingValue: product?.rating?.rating || 0,
        bestRating: 5,
        ratingCount: product?.rating?.rating_count || 0,
      },
      review: getReviewsScheema(reviews),
    }
  
    useSchemaOrg([
      defineProduct(scheema),
    ])
  }
  
  const getReviewsScheema = (reviews: Review[]) => {
    if(!reviews)
      return null;
  
    return reviews.map((item) => {
      return {
        comment: item.text,
        author: {
          name: item.owner?.name,
        },
        reviewRating: {
          ratingValue: item.rating,
        },
      }
    })
  }


  return {
    setSchema
  }
}