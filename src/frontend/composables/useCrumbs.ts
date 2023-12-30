type Crumb = {
  name: String,
  item: String
}

export const useCrumbs = () => {
  const items = useState('crumbs', () => [] as Crumb[])

  const setCrumbs = (crumbs: Crumb[]) => {
    items.value = crumbs
  }

  return {
    items,
    setCrumbs
  }
}