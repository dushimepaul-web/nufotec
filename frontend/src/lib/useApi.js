import { useEffect, useState } from 'react'
import api from './api'

/**
 * Charge une ressource GET : const { data, loading, error } = useApi('/chiffres-cles')
 * `deps` relance le chargement quand ils changent.
 */
export function useApi(url, params, deps = []) {
  const [state, setState] = useState({ data: null, loading: Boolean(url), error: null })
  const key = JSON.stringify(params ?? null)

  useEffect(() => {
    if (!url) return undefined
    let alive = true
    setState((s) => ({ ...s, loading: true, error: null }))
    api
      .get(url, params)
      .then((data) => alive && setState({ data, loading: false, error: null }))
      .catch((error) => alive && setState({ data: null, loading: false, error }))
    return () => { alive = false }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [url, key, ...deps])

  return state
}

export default useApi

/**
 * Comme useApi, mais récupère toutes les pages (per_page=100) et renvoie tous les
 * éléments d'un coup : { data: { items, meta }, loading, error }.
 * Utilisé par la médiathèque pour afficher tout le contenu sans pagination.
 */
export function useApiAll(url, params) {
  const [state, setState] = useState({ data: null, loading: Boolean(url), error: null })
  const key = JSON.stringify(params ?? null)

  useEffect(() => {
    if (!url) return undefined
    let alive = true
    setState((s) => ({ ...s, loading: true, error: null }))
    const load = async () => {
      try {
        const base = { ...(params ?? {}), page: 1, per_page: 100 }
        const first = await api.get(url, base)
        let items = first?.items ?? []
        const meta = first?.meta
        const pages = meta?.pages ?? 1
        if (pages > 1) {
          const rest = await Promise.all(
            Array.from({ length: pages - 1 }, (_, i) => api.get(url, { ...(params ?? {}), page: i + 2, per_page: 100 })),
          )
          rest.forEach((r) => { items = items.concat(r?.items ?? []) })
        }
        if (alive) setState({ data: { items, meta }, loading: false, error: null })
      } catch (error) {
        if (alive) setState({ data: null, loading: false, error })
      }
    }
    load()
    return () => { alive = false }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [url, key])

  return state
}
