import { useReviewStore } from '~/store/review';

export const useLikesStore = defineStore('likesStore', {
  persist: true,

  state: () => ({ 
    likes: [] as Number[],
    dislikes: [] as Number[]
  }),
  
  getters: {
    getLikes: (state) => state.likes,
    getDislikes: (state) => state.dislikes,
  },

  actions: {
    setLike(value: number) {
      const reviewStore = useReviewStore()
      
      reviewStore.like(value, {
        direction: 'plus',
        type: 'likes'
      })

      this.likes.push(value)
    },

    removeLike(value: number) {
      const index = this.likes.indexOf(value)

      if(index === -1)
        return

      const reviewStore = useReviewStore()
      
      reviewStore.like(value, {
        direction: 'minus',
        type: 'likes'
      })

      this.likes.splice(index, 1)
    },

    toggleLike(value: number) {
      const index = this.likes.indexOf(value)

      if(index !== -1)
        this.removeLike(value)
      else {
        this.setLike(value)
        this.removeDislike(value)
      }
    },

    setDislike(value: number) {
      const reviewStore = useReviewStore()
      
      reviewStore.like(value, {
        direction: 'plus',
        type: 'dislikes'
      })

      this.dislikes.push(value)
    },

    removeDislike(value: number) {
      const index = this.dislikes.indexOf(value)

      if(index === -1)
        return

      const reviewStore = useReviewStore()
      
      reviewStore.like(value, {
        direction: 'minus',
        type: 'dislikes'
      })

      this.dislikes.splice(index, 1)
    },

    toggleDislike(value: number) {
      const index = this.dislikes.indexOf(value)

      if(index !== -1)
        this.removeDislike(value)
      else {
        this.setDislike(value)
        this.removeLike(value)
      }
    },
  },
})