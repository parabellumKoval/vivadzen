export const useToLocalePath = () => {
  const { $regionPath } = useNuxtApp()

  return (path: string) => {
    return typeof $regionPath === 'function' ? $regionPath(path) : path
  }
}
