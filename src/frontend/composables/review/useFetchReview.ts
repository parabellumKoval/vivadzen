import {useReviewStore} from '~/store/review'

export const useFetchReview = () => {

const isLoading = ref(false)

const getReviewsAmount = async (type: string) => {
  let typeValue = null;

  switch(type) {
    case 'products':
      typeValue = String.raw`Backpack\Store\app\Models\Product`;
      break;
    case 'shop':
      typeValue = null
      break;
  }

  const query = {
    reviewable_type: typeValue,
    count: 1
  }

  return await useAsyncData('reviews-amount', () => useReviewStore().getAll(query, false)).then(({data, error}) => {
    if(data?.value?.meta?.total) {
      return data.value.meta.total
    }
  })
}

const getReviews = async (query: object, refresh: boolean, uid: string = 'reviews') => {
  isLoading.value = true

  return await useAsyncData(uid, () => useReviewStore().getAll(query, refresh)).then(({data, error}) => {
    
    if(data.value) {
      return data.value
    }else {
      return {reviews: null, meta: null}
    }
     
  }).finally(() => {
    isLoading.value = false
  })
}

const loadReviewsHandler = async (reviewQuery, page: number) => {
  const query = {
    ...reviewQuery,
    page: page
  }
  
  return await getReviews(query, false)
}

  return {
    getReviews,
    getReviewsAmount,
    loadReviewsHandler
  }
}