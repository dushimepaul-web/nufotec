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
