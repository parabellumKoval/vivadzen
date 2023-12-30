import fetch from 'node-fetch'

export default async () => {
  const getStaticRoutes = () => {
    const base = [
      '/',
    ]

    return base
  }

  return [
    ...getStaticRoutes()
  ]
}