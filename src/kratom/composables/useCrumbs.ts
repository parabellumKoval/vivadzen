export type Crumb = {
  name: string
  item: string
  icon?: string | null
}

const HOME_PATH = '/'
const HOME_ICON = 'ci:house-01'

export const applyCrumbDefaults = (crumbs: Crumb[]): Crumb[] =>
  crumbs.map((crumb) => {
    if (crumb.item === HOME_PATH) {
      return { ...crumb, icon: crumb.icon ?? HOME_ICON }
    }

    return { ...crumb, icon: crumb.icon ?? null }
  })

export const useCrumbs = () => {
  const items = useState('crumbs', () => [] as Crumb[])

  const setCrumbs = (crumbs: Crumb[]) => {
    items.value = applyCrumbDefaults(crumbs)
  }

  return {
    items,
    setCrumbs
  }
}