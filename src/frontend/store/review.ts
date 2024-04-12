type Review = {
  id: number,
  text: string,
  likes: number,
  dislikes: number,
  owner: object,
  children: object[]
};

export const useReviewStore = defineStore('reviewStore', {
  state: () => ({
    reviewableId: null as Number | null,
    reviewableType: null as String | null,
    popupIsShow: false,
    allState: {
      data: [] as Review[],
      meta: null
    },
  }),
  
  getters: {
    isShow: (state) => state.popupIsShow,
    all: (state) => state.allState.data,
    meta: (state) => state.allState.meta,
  },

  actions: {
    openModal() {
      this.popupIsShow = true
    },
    
    closeModal() {
      this.popupIsShow = false
    },
    
    toggleModal() {
      this.popupIsShow = !this.popupIsShow
    },

    setReviewable(id: Number, type: String) {
      this.reviewableId = id
      this.reviewableType = type
    },

    clearReviewable() {
      this.reviewableId = null
      this.reviewableType = null
    },

    async getAll(query: string, refresh: boolean = true) {
      const runtimeConfig = useRuntimeConfig()
      const params = query? '?' + new URLSearchParams(query).toString(): '';
      const url = `${runtimeConfig.public.apiBase}/review${params}`

      return await useApiFetch(url).then(({data: {data, meta}}) => {
        // if(refresh){
        //   this.allState.data = data
        // }else {
        //   this.allState.data = this.allState.data.concat(data)
        // }
        
        // this.allState.meta = meta

        return {
          reviews: data,
          meta
        }
      })
    },

    async create(data: object) {
      const runtimeConfig = useRuntimeConfig()
      const url = `${runtimeConfig.public.apiBase}/review`

      const fullData = {
        ...data,
        // 'reviewable_id': this.reviewableId,
        // 'reviewable_type': this.reviewableType,
      }

      return await useApiFetch(url, fullData, 'POST').then((response) => {
        if(response.data) {

          // if(data.parent_id) {
          //   const parent_comment = context.allState.data.find((item) => item.id === data.parent_id)
          //   parent_comment.children.unshift(response.data as Review)
          // }else {
          //   context.allState.data.unshift(response.data as Review)
          // }

        }

        return {data: response.data, error: response.error}
      })
    },

    async like(id: number, data: object) {
      const runtimeConfig = useRuntimeConfig()
      const url = `${runtimeConfig.public.apiBase}/review/${id}/like`;

      return await useApiFetch(url, data, 'POST').then(({data, error}) => {

        if(data) {
          const likes = data.likes
          const dislikes = data.dislikes

          this.allState.data.map((item) => {

            if(item.id === id){
              if(likes !== undefined && likes !== null)
                item.likes = likes

              if(dislikes !== undefined && dislikes !== null)
                item.dislikes = dislikes
            }else {
              item.children && item.children.map((ch) => {
                if(ch.id === id){
                  if(likes !== undefined && likes !== null)
                    ch.likes = likes
  
                  if(dislikes !== undefined && dislikes !== null)
                    ch.dislikes = dislikes
                }
    
                return ch
              })
            }
    
            return item
          })
        }

        if(error) {
          throw error
        }
      })
    }
  },
})