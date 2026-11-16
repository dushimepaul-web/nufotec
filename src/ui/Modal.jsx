// Fenêtre modale simple (sans le JavaScript de Bootstrap) : Échap ou clic à côté pour fermer, défilement bloqué.
import { useEffect, useRef } from 'react'
import { createPortal } from 'react-dom'
import './modal.css'

export default function Modal({ open, onClose, title, icon, size = 'md', dark = false, children, footer }) {
  const boxRef = useRef(null)
  // onClose change à chaque frappe dans le formulaire (nouvelle fonction à chaque rendu) : on le garde dans une ref,
  // sinon l'effet ci-dessous se relancerait et remettrait le curseur dans le premier champ après chaque caractère.
  const onCloseRef = useRef(onClose)
  useEffect(() => { onCloseRef.current = onClose })

  // Uniquement à l'ouverture et à la fermeture de la fenêtre
  useEffect(() => {
    if (!open) return undefined
    const onKey = (e) => { if (e.key === 'Escape') onCloseRef.current() }
    const previous = document.activeElement
    document.addEventListener('keydown', onKey)
    document.body.style.overflow = 'hidden'
    const timer = setTimeout(() => (boxRef.current?.querySelector('[data-autofocus]') ?? boxRef.current?.querySelector('input:not([tabindex="-1"]):not([aria-hidden]), select, textarea, button:not(.nmodal-close)'))?.focus(), 30)
    return () => {
      clearTimeout(timer)
      document.removeEventListener('keydown', onKey)
      document.body.style.overflow = ''
      previous?.focus?.()
    }
  }, [open])

  if (!open) return null
  return createPortal(
    <div className="nmodal-overlay" onMouseDown={(e) => { if (e.target === e.currentTarget) onClose() }}>
      <div className={`nmodal nmodal-${size}${dark ? ' nmodal-dark' : ''}`} role="dialog" aria-modal="true" aria-label={title} ref={boxRef}>
        {title ? (
          <div className="nmodal-head">
            <h5>{icon && <i className={`bi ${icon} me-2`}></i>}{title}</h5>
            <button type="button" className="nmodal-close" aria-label="Fermer" onClick={onClose}><i className="bi bi-x-lg"></i></button>
          </div>
        ) : (
          <button type="button" className="nmodal-close floating" aria-label="Fermer" onClick={onClose}><i className="bi bi-x-lg"></i></button>
        )}
        <div className="nmodal-body">{children}</div>
        {footer && <div className="nmodal-foot">{footer}</div>}
      </div>
    </div>,
    document.body,
  )
}
