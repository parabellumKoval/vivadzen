export const usePoints = () => {
  const { get } = useSettings()

  const name = computed(() => {
    return get('profile.points.name') || 'Points'
  })

  const replaceInText = (value?: string | null) => {
    if (!value) return ''
    return value.replaceAll('{pointsName}', String(name.value))
  }

  const resolve = (value: string) => {
    const resolved = replaceInText(value)
    if (['point', 'points'].includes(resolved?.toLowerCase())) {
      return name.value
    }
    return resolved
  }

  return {
    name,
    resolve,
    replaceInText
  }
}
