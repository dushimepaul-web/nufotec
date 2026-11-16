// Choix du pays avec recherche : la liste s'ouvre dès le premier clic, se filtre au fil des lettres tapées
// (sans tenir compte des accents ni des majuscules ; les pays qui COMMENCENT par les lettres d'abord),
// navigation au clavier (flèches, Entrée, Échap). Réutilisable dans tous les formulaires.
import { useEffect, useId, useMemo, useRef, useState } from 'react'
import { cleanSearch } from '../lib/sanitize'
import './country-select.css'

/** Drapeau du pays (fichiers locaux dans public/flags) ; un globe si le drapeau n'existe pas */
export function Flag({ iso2, className = '' }) {
  const [missing, setMissing] = useState(false)
  const code = (iso2 || '').toLowerCase()
  if (!code || missing) return <i className={`bi bi-globe2 csel-flag-none ${className}`} aria-hidden="true"></i>
  return <img className={`csel-flag ${className}`} src={`/flags/${code}.png`} alt="" width="20" height="15" loading="lazy" onError={() => setMissing(true)} />
}

const norm = (s) => (s || '').normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase().trim()

export default function CountrySelect({ countries = [], value, onChange, required = false, placeholder = 'Rechercher un pays…' }) {
  const id = useId()
  const selected = countries.find((c) => c.id === Number(value))
  const [query, setQuery] = useState('')
  const [open, setOpen] = useState(false)
  const [active, setActive] = useState(0)
  const rootRef = useRef(null)
  const inputRef = useRef(null)
  const listRef = useRef(null)

  const results = useMemo(() => {
    const q = norm(query)
    if (!q) return countries
    const starts = []
    const contains = []
    for (const c of countries) {
      const name = norm(c.pays)
      if (name.startsWith(q) || (c.indicatif || '').replace(/\D/g, '').startsWith(q.replace(/\D/g, '') || '_')) starts.push(c)
      else if (name.includes(q)) contains.push(c)
    }
    return [...starts, ...contains]
  }, [countries, query])

  // Fermer en cliquant à côté
  useEffect(() => {
    if (!open) return undefined
    const onDown = (e) => { if (!rootRef.current?.contains(e.target)) close() }
    document.addEventListener('mousedown', onDown)
    return () => document.removeEventListener('mousedown', onDown)
  })

  // À l'ouverture : le pays choisi est mis en évidence et visible dans la liste
  useEffect(() => {
    if (!open) return
    const i = query ? 0 : Math.max(0, results.findIndex((c) => c.id === selected?.id))
    setActive(i)
    requestAnimationFrame(() => listRef.current?.children[i]?.scrollIntoView({ block: 'nearest' }))
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open, query])

  const openList = () => { setOpen(true) }
  const close = () => { setOpen(false); setQuery('') }
  const choose = (c) => { onChange(c.id); close(); inputRef.current?.blur() }

  const onKey = (e) => {
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault()
      if (!open) return openList()
      const next = Math.min(results.length - 1, Math.max(0, active + (e.key === 'ArrowDown' ? 1 : -1)))
      setActive(next)
      listRef.current?.children[next]?.scrollIntoView({ block: 'nearest' })
    } else if (e.key === 'Enter' && open) {
      e.preventDefault()
      if (results[active]) choose(results[active])
    } else if (e.key === 'Escape' && open) {
      e.stopPropagation()
      close()
    } else if (e.key === 'Tab') {
      close()
    }
    return undefined
  }

  return (
    <div className={`csel${open ? ' open' : ''}`} ref={rootRef}>
      <div className="csel-field">
        <span className="csel-lead">{open ? <i className="bi bi-search" aria-hidden="true"></i> : <Flag key={selected?.iso2} iso2={selected?.iso2} />}</span>
        <input
          ref={inputRef}
          role="combobox"
          aria-expanded={open}
          aria-controls={`${id}-list`}
          aria-autocomplete="list"
          aria-activedescendant={open && results[active] ? `${id}-${results[active].id}` : undefined}
          autoComplete="off"
          required={required && !selected}
          value={open ? query : (selected?.pays ?? '')}
          placeholder={open ? (selected?.pays ?? placeholder) : placeholder}
          onFocus={openList}
          onClick={openList}
          maxLength={40}
          onChange={(e) => { setQuery(cleanSearch(e.target.value)); setOpen(true) }}
          onKeyDown={onKey}
        />
        {selected?.indicatif && !open && <span className="csel-code">{selected.indicatif}</span>}
        <i className="bi bi-chevron-down csel-chev" aria-hidden="true" onMouseDown={(e) => { e.preventDefault(); if (open) close(); else inputRef.current?.focus() }}></i>
      </div>
      {open && (
        <ul className="csel-list" id={`${id}-list`} role="listbox" ref={listRef}>
          {results.length === 0 && <li className="csel-empty">Aucun pays ne correspond à « {query} »</li>}
          {results.map((c, i) => (
            <li
              key={c.id}
              id={`${id}-${c.id}`}
              role="option"
              aria-selected={c.id === selected?.id}
              className={`${i === active ? 'active' : ''}${c.id === selected?.id ? ' chosen' : ''}`}
              onMouseDown={(e) => { e.preventDefault(); choose(c) }}
              onMouseEnter={() => setActive(i)}
            >
              <Flag iso2={c.iso2} />
              <span>{c.pays}</span>
              {c.indicatif && <small>{c.indicatif}</small>}
              {c.id === selected?.id && <i className="bi bi-check2"></i>}
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
