// Fenêtre modale simple (sans le JavaScript de Bootstrap) : Échap ou clic à côté pour fermer.
import { useEffect, useRef } from 'react'
import { createPortal } from 'react-dom'
import './modal.css'

export default function Modal({ open, onClose, title, icon, size = 'md', footer, children }) {
  const onCloseRef = useRef(onClose)
  useEffect(() => { onCloseRef.current = onClose })

  useEffect(() => {
    if (!open) return undefined
    const onKey = (e) => { if (e.key === 'Escape') onCloseRef.current() }
    document.addEventListener('keydown', onKey)
    document.body.style.overflow = 'hidden'
    return () => {
      document.removeEventListener('keydown', onKey)
      document.body.style.overflow = ''
    }
  }, [open])

  if (!open) return null
  return createPortal(
    <div className="nmodal-overlay" onMouseDown={(e) => { if (e.target === e.currentTarget) onClose() }}>
      <div className={`nmodal nmodal-${size}`} role="dialog" aria-modal="true" aria-label={title}>
        <div className="nmodal-head">
          <h5>{icon && <i className={`bi ${icon} me-2`}></i>}{title}</h5>
          <button type="button" className="nmodal-close" aria-label="Fermer" onClick={onClose}><i className="bi bi-x-lg"></i></button>
        </div>
        <div className="nmodal-body">{children}</div>
        {footer && <div className="nmodal-foot">{footer}</div>}
      </div>
    </div>,
    document.body,
  )
}
