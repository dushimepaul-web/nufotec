// Bandeau d'état de la connexion (site public et administration) : hors ligne, lente, serveur injoignable,
// demandes en attente d'envoi, connexion rétablie. Le visiteur comprend ce qui se passe au lieu de croire le site cassé.
import { useEffect, useState } from 'react'
import { networkState, onNetworkChange } from './network'
import { flush, onOutboxChange, pendingCount } from './outbox'
import './network-banner.css'

const TEXT = {
  offline: ['bi-wifi-off', 'Pas de connexion internet. Vos demandes partiront dès son retour.'],
  slow: ['bi-hourglass-split', 'Connexion lente… nous réessayons automatiquement.'],
  unreachable: ['bi-cloud-slash', 'Le serveur ne répond pas pour le moment. Nous réessayons.'],
  back: ['bi-check2-circle', 'Connexion rétablie.'],
}

export default function NetworkBanner() {
  const [net, setNet] = useState(networkState())
  const [pending, setPending] = useState(pendingCount())
  const [back, setBack] = useState(false)

  useEffect(() => {
    let timer
    const offNet = onNetworkChange((s) => {
      setNet((prev) => {
        if (s === 'ok' && prev !== 'ok') {
          setBack(true)
          clearTimeout(timer)
          timer = setTimeout(() => setBack(false), 3000)
        }
        return s
      })
    })
    const offBox = onOutboxChange(setPending)
    return () => { offNet(); offBox(); clearTimeout(timer) }
  }, [])

  const key = net !== 'ok' ? net : back ? 'back' : null
  if (!key && !pending) return null
  const [icon, text] = key ? TEXT[key] : ['bi-cloud-upload', '']
  return (
    <div className={`nufnet nufnet-${key ?? 'pending'}`} role="status" aria-live="polite">
      <i className={`bi ${icon}`}></i>
      <span>
        {text}
        {pending > 0 && <>{text ? ' ' : ''}<b>{pending} demande{pending > 1 ? 's' : ''} en attente d'envoi.</b></>}
      </span>
      {pending > 0 && net === 'ok' && <button type="button" onClick={() => flush()}>Envoyer maintenant</button>}
    </div>
  )
}
