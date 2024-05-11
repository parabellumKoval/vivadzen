import {useAuthStore} from '~/store/auth'
import {useFavoritesStore} from '~/store/favorites'

export const useFavorite = (productId: Number) => {

  const {t} = useI18n()
  const id = ref(productId)

  // COMPUTEDS
  const isFavorite = computed(() => {
    const fi = useFavoritesStore().ids
    
    if(fi && fi.length)
      return fi.includes(id.value)
    
    return false
  })

  const auth = computed(() => {
    return useAuthStore().auth
  })

  const user = computed(() => {
    return useAuthStore().user
  })

  
  const toFavoriteHandler = () => {
    if(!auth.value || !user.value) {
      useModal().open(resolveComponent('ModalAuthSocial'), null, null, {width: {min: 420, max: 420}})
    }else {
      useFavoritesStore().sync({
        user_id: user.value.id,
        product_id: id.value
      }).then((r) => {
        if(r) {
          useNoty().setNoty({
            content: t('noty.favorite.success')
          })
        }
      }).catch((e) => {
        useNoty().setNoty({
          content: t('error.occurred'),
          type: 'error'
        }, 7000)
      })
    }
  }

  return {
    isFavorite,
    toFavoriteHandler
  }
}