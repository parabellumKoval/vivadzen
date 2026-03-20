export const useImg = () => {
  
  // const noImage = computed(() => {
  //   return useRuntimeConfig().public.noimage
  // })

  const noImage = useRuntimeConfig().public.noimage
  const noImageGray = useRuntimeConfig().public.noimagegray
  const staticProvider = String(useRuntimeConfig().public.staticImageProvider || '').trim()
  const allowedProviders = new Set(['ipx', 'vercel', 'bunny'])
  const provider = allowedProviders.has(staticProvider) ? staticProvider : undefined
  const noImageTransparent = useRuntimeConfig().public.noimageTransparent


  const folderSrc = (src: string, folder: string) => {
    return '/' + folder + '/' + src
  }

  const srcOrPlaceholder = (image: Image | string, folder: string) => {
    if(typeof image === 'string') {
      return folderSrc(image, folder)
    }else {
      return image?.src? folderSrc(image.src, folder): useImage().noImage
    }
  }

  const category = (image: Image | string) => {
    return srcOrPlaceholder(image, 'categories')
  }

  const product = (image: Image | string) => {
    return srcOrPlaceholder(image, 'products')
  }

  const brand = (image: Image | string) => {
    return srcOrPlaceholder(image, 'brands')
  }

  const blog = (image: Image | string) => {
    return srcOrPlaceholder(image, 'blog')
  }

  return {
    noImageTransparent: noImageTransparent,
    noImage: noImage,
    noImageGray: noImageGray,
    provider: provider,
    folderSrc,
    category,
    product,
    brand,
    blog
  }
}
