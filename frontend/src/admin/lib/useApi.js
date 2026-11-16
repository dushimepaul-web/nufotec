import { useEffect, useState } from 'react'
import api from './api'

/**
 * Charge une ressource GET : const { data, loading, error } = useApi('/admin/users', params)
 * `deps` relance le chargement quand ils changent.
 *
 * Quand l'adresse change (ex. « nouvelle page » -> « page 57 » sans démonter le composant), on renvoie
 * tout de suite « chargement » : l'ancien état (souvent data = null) n'est jamais rendu comme s'il était fini.
 */
export function useApi(url, params, deps = []) {
  const key = JSON.stringify([url, params ?? null, ...deps])
  const [state, setState] = useState({ key, data: null, loading: Boolean(url), error: null })

  useEffect(() => {
    if (!url) return undefined
    let alive = true
    setState((s) => ({ ...s, key, loading: true, error: null }))
    api
      .get(url, params)
      .then((data) => alive && setState({ key, data, loading: false, error: null }))
      .catch((error) => alive && setState({ key, data: null, loading: false, error }))
    return () => { alive = false }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [key])

  if (state.key !== key) return { data: null, loading: Boolean(url), error: null }
  return state
}

export default useApi
