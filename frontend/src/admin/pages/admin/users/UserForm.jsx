// Création d'un utilisateur — POST /admin/users (invitation par email).
import { useState } from 'react'
import api from '../../../lib/api'
import useApi from '../../../lib/useApi'
import Modal from '../../../ui/Modal'

const TYPES = ['admin', 'medecin', 'patient', 'entreprise', 'investisseur', 'partenaire', 'broker']

export default function UserForm({ open, onClose, onSaved }) {
  const pays = useApi('/pays')
  const roles = useApi(open ? '/admin/roles' : null)
  const [form, setForm] = useState({
    email: '', nom: '', prenom: '', type_utilisateur: 'patient', role_id: '', telephone: '', genre: '', pays_id: '',
    send_invitation: true, est_verifie: false,
  })
  const [sending, setSending] = useState(false)
  const [error, setError] = useState(null)
  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))
  const fieldErr = (name) => error?.field?.(name)

  const submit = async (e) => {
    e.preventDefault()
    if (!form.email.trim() || !form.nom.trim() || !form.prenom.trim()) return
    setSending(true)
    setError(null)
    try {
      const body = {
        email: form.email.trim(), nom: form.nom.trim(), prenom: form.prenom.trim(),
        type_utilisateur: form.type_utilisateur, telephone: form.telephone || null,
        genre: form.genre || null, pays_id: form.pays_id ? Number(form.pays_id) : null,
        send_invitation: form.send_invitation, est_verifie: form.est_verifie,
      }
      if (form.role_id) body.role_id = Number(form.role_id)
      await api.post('/admin/users', body)
      onSaved(); onClose()
    } catch (err) {
      setError(err)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open={open} onClose={onClose} icon="bi-person-plus" title="Nouvel utilisateur" size="lg">
      <form onSubmit={submit} noValidate>
        {error && !fieldErr('email') && !fieldErr('nom') && !fieldErr('prenom') && (
          <div className="alert alert-danger py-2 small">{error.message}</div>
        )}
        <div className="row g-3">
          <div className="col-md-4">
            <label className="form-label">Nom *</label>
            <input className="form-control" value={form.nom} onChange={(e) => set('nom', e.target.value)} />
            {fieldErr('nom') && <small className="text-danger">{fieldErr('nom')}</small>}
          </div>
          <div className="col-md-4">
            <label className="form-label">Prénom *</label>
            <input className="form-control" value={form.prenom} onChange={(e) => set('prenom', e.target.value)} />
            {fieldErr('prenom') && <small className="text-danger">{fieldErr('prenom')}</small>}
          </div>
          <div className="col-md-4">
            <label className="form-label">Email *</label>
            <input type="email" className="form-control" value={form.email} onChange={(e) => set('email', e.target.value)} />
            {fieldErr('email') && <small className="text-danger">{fieldErr('email')}</small>}
          </div>
          <div className="col-md-4">
            <label className="form-label">Type</label>
            <select className="form-select" value={form.type_utilisateur} onChange={(e) => set('type_utilisateur', e.target.value)}>
              {TYPES.map((t) => <option key={t} value={t}>{t}</option>)}
            </select>
          </div>
          <div className="col-md-4">
            <label className="form-label">Rôle</label>
            <select className="form-select" value={form.role_id} onChange={(e) => set('role_id', e.target.value)}>
              <option value="">Automatique (selon le type)</option>
              {(roles.data ?? []).map((r) => <option key={r.id} value={r.id}>{r.nom}</option>)}
            </select>
          </div>
          <div className="col-md-4">
            <label className="form-label">Téléphone</label>
            <input className="form-control" value={form.telephone} placeholder="+257…" onChange={(e) => set('telephone', e.target.value)} />
          </div>
          <div className="col-md-4">
            <label className="form-label">Genre</label>
            <select className="form-select" value={form.genre} onChange={(e) => set('genre', e.target.value)}>
              <option value="">—</option>
              <option value="M">Masculin</option>
              <option value="F">Féminin</option>
              <option value="Autre">Autre</option>
            </select>
          </div>
          <div className="col-md-6">
            <label className="form-label">Pays</label>
            <select className="form-select" value={form.pays_id} onChange={(e) => set('pays_id', e.target.value)}>
              <option value="">—</option>
              {(pays.data ?? []).map((p) => <option key={p.id} value={p.id}>{p.pays}</option>)}
            </select>
          </div>
          <div className="col-12 d-flex flex-column gap-2">
            <div className="form-check">
              <input className="form-check-input" type="checkbox" id="uInvite" checked={form.send_invitation} onChange={(e) => set('send_invitation', e.target.checked)} />
              <label className="form-check-label" htmlFor="uInvite">Envoyer l'invitation (code pour choisir le mot de passe)</label>
            </div>
            <div className="form-check">
              <input className="form-check-input" type="checkbox" id="uVerifie" checked={form.est_verifie} onChange={(e) => set('est_verifie', e.target.checked)} />
              <label className="form-check-label" htmlFor="uVerifie">Compte vérifié (KYC)</label>
            </div>
          </div>
          <div className="col-12 d-flex justify-content-end gap-2">
            <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
            <button type="submit" className="btn btn-primary" disabled={sending}>
              {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : null} Créer
            </button>
          </div>
        </div>
      </form>
    </Modal>
  )
}
