// Administration du chatbot — réglages, fournisseurs, articles, sources, conversations, statistiques.
import { useState } from 'react'
import api from '../../../lib/api'
import { formatDateTime } from '../../../lib/format'
import useApi from '../../../lib/useApi'
import DataTable from '../../../ui/DataTable'
import Modal from '../../../ui/Modal'
import PageTitle from '../../../ui/PageTitle'
import Spinner from '../../../ui/Spinner'
import StatusBadge from '../../../ui/StatusBadge'

const TABS = [
  ['reglages', 'Réglages'],
  ['fournisseurs', 'Fournisseurs'],
  ['articles', 'Articles'],
  ['sources', 'Sources'],
  ['tables', 'Tables'],
  ['conversations', 'Conversations'],
  ['tester', 'Tester'],
  ['stats', 'Statistiques'],
]

const KINDS = ['openai', 'groq', 'anthropic', 'gemini', 'mistral', 'ollama', 'openai_compatible']

// ── Réglages ──

function Reglages() {
  const settings = useApi('/admin/chatbot/reglages')
  const fournisseurs = useApi('/admin/chatbot/fournisseurs')

  if (settings.loading) return <Spinner />
  if (settings.error) return <div className="alert alert-danger">{settings.error.message}</div>

  return <ReglagesForm values={settings.data ?? {}} providers={fournisseurs.data ?? []} />
}

function ReglagesForm({ values, providers }) {
  const num = (k, fallback = '') => (values[k] === '' || values[k] == null ? fallback : String(values[k]))
  const [form, setForm] = useState({
    assistant_name: values.assistant_name ?? '',
    greeting: values.greeting ?? '',
    system_prompt: values.system_prompt ?? '',
    chat_provider_id: num('chat_provider_id'),
    chat_model: values.chat_model ?? '',
    fallback_provider_id: num('fallback_provider_id'),
    fallback_model: values.fallback_model ?? '',
    temperature: num('temperature'),
    max_tokens: num('max_tokens'),
    top_k: num('top_k'),
    min_score: num('min_score'),
    history_messages: num('history_messages'),
    handoff_email: values.handoff_email ?? '',
    emergency_message: values.emergency_message ?? '',
  })
  const [sending, setSending] = useState(false)
  const [message, setMessage] = useState('')
  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const submit = async (e) => {
    e.preventDefault()
    setSending(true)
    setMessage('')
    try {
      await api.put('/admin/chatbot/reglages', {
        assistant_name: form.assistant_name || null,
        greeting: form.greeting || null,
        system_prompt: form.system_prompt || null,
        chat_provider_id: form.chat_provider_id ? Number(form.chat_provider_id) : null,
        chat_model: form.chat_model || null,
        fallback_provider_id: form.fallback_provider_id ? Number(form.fallback_provider_id) : null,
        fallback_model: form.fallback_model || null,
        temperature: form.temperature === '' ? null : Number(form.temperature),
        max_tokens: form.max_tokens === '' ? null : Number(form.max_tokens),
        top_k: form.top_k === '' ? null : Number(form.top_k),
        min_score: form.min_score === '' ? null : Number(form.min_score),
        history_messages: form.history_messages === '' ? null : Number(form.history_messages),
        handoff_email: form.handoff_email || null,
        emergency_message: form.emergency_message || null,
      })
      setMessage('Réglages enregistrés')
    } catch (err) {
      setMessage(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <div className="card">
      <div className="card-body">
        <h5 className="card-title">Réglages du chatbot</h5>
        <form onSubmit={submit} noValidate>
          {message && <div className={`alert alert-${message === 'Réglages enregistrés' ? 'success' : 'danger'} py-2`}>{message}</div>}
          <div className="row g-3">
            <div className="col-md-4">
              <label className="form-label">Nom de l'assistant</label>
              <input className="form-control" value={form.assistant_name} onChange={(e) => set('assistant_name', e.target.value)} />
            </div>
            <div className="col-md-4">
              <label className="form-label">Modèle principal</label>
              <input className="form-control" value={form.chat_model} onChange={(e) => set('chat_model', e.target.value)} />
            </div>
            <div className="col-md-4">
              <label className="form-label">Fournisseur principal</label>
              <select className="form-select" value={form.chat_provider_id} onChange={(e) => set('chat_provider_id', e.target.value)}>
                <option value="">—</option>
                {providers.map((p) => <option key={p.id} value={p.id}>{p.nom} ({p.type})</option>)}
              </select>
            </div>
            <div className="col-md-4">
              <label className="form-label">Modèle de secours</label>
              <input className="form-control" value={form.fallback_model} onChange={(e) => set('fallback_model', e.target.value)} />
            </div>
            <div className="col-md-4">
              <label className="form-label">Fournisseur de secours</label>
              <select className="form-select" value={form.fallback_provider_id} onChange={(e) => set('fallback_provider_id', e.target.value)}>
                <option value="">—</option>
                {providers.map((p) => <option key={p.id} value={p.id}>{p.nom} ({p.type})</option>)}
              </select>
            </div>
            <div className="col-md-4">
              <label className="form-label">Email de transfert</label>
              <input type="email" className="form-control" value={form.handoff_email} onChange={(e) => set('handoff_email', e.target.value)} />
            </div>
            <div className="col-md-3">
              <label className="form-label">Température</label>
              <input type="number" step="0.1" min="0" max="1.5" className="form-control" value={form.temperature} onChange={(e) => set('temperature', e.target.value)} />
            </div>
            <div className="col-md-3">
              <label className="form-label">Max tokens</label>
              <input type="number" className="form-control" value={form.max_tokens} onChange={(e) => set('max_tokens', e.target.value)} />
            </div>
            <div className="col-md-3">
              <label className="form-label">Top K</label>
              <input type="number" className="form-control" value={form.top_k} onChange={(e) => set('top_k', e.target.value)} />
            </div>
            <div className="col-md-3">
              <label className="form-label">Historique (messages)</label>
              <input type="number" className="form-control" value={form.history_messages} onChange={(e) => set('history_messages', e.target.value)} />
            </div>
            <div className="col-12">
              <label className="form-label">Message d'accueil</label>
              <input className="form-control" value={form.greeting} onChange={(e) => set('greeting', e.target.value)} />
            </div>
            <div className="col-12">
              <label className="form-label">Prompt système</label>
              <textarea className="form-control" rows="4" value={form.system_prompt} onChange={(e) => set('system_prompt', e.target.value)} />
            </div>
            <div className="col-12">
              <label className="form-label">Message d'urgence</label>
              <input className="form-control" value={form.emergency_message} onChange={(e) => set('emergency_message', e.target.value)} />
            </div>
            <div className="col-12">
              <button type="submit" className="btn btn-primary" disabled={sending}>
                {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : null} Enregistrer
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  )
}

// ── Fournisseurs ──

function ProviderForm({ open, initial, onClose, onSaved }) {
  const isEdit = Boolean(initial)
  const [form, setForm] = useState(() => (initial ? { nom: initial.nom, type: initial.type, url: initial.url ?? '', cle_api: '', actif: initial.actif } : { nom: '', type: 'groq', url: '', cle_api: '', actif: true }))
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')
  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const submit = async (e) => {
    e.preventDefault()
    setSending(true)
    setError('')
    try {
      if (isEdit) {
        await api.patch(`/admin/chatbot/fournisseurs/${initial.id}`, { nom: form.nom, url: form.url || null, cle_api: form.cle_api || null, actif: form.actif })
      } else {
        await api.post('/admin/chatbot/fournisseurs', { nom: form.nom, type: form.type, url: form.url || null, cle_api: form.cle_api || null, actif: form.actif })
      }
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open={open} onClose={onClose} icon="bi-cpu" title={isEdit ? 'Modifier le fournisseur' : 'Connecter un fournisseur'}>
      <form onSubmit={submit} noValidate>
        {error && <div className="alert alert-danger py-2 small">{error}</div>}
        <div className="row g-3">
          <div className="col-12">
            <label className="form-label">Nom *</label>
            <input className="form-control" value={form.nom} onChange={(e) => set('nom', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Type</label>
            <select className="form-select" value={form.type} disabled={isEdit} onChange={(e) => set('type', e.target.value)}>
              {KINDS.map((k) => <option key={k} value={k}>{k}</option>)}
            </select>
          </div>
          <div className="col-12">
            <label className="form-label">URL</label>
            <input className="form-control" value={form.url} placeholder="auto si vide" onChange={(e) => set('url', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Clé d'API {isEdit && '(laisser vide pour conserver)'}</label>
            <input type="password" className="form-control" value={form.cle_api} onChange={(e) => set('cle_api', e.target.value)} />
          </div>
          <div className="col-12">
            <div className="form-check">
              <input className="form-check-input" type="checkbox" id="pActif" checked={form.actif} onChange={(e) => set('actif', e.target.checked)} />
              <label className="form-check-label" htmlFor="pActif">Actif</label>
            </div>
          </div>
          <div className="col-12 d-flex justify-content-end gap-2">
            <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
            <button type="submit" className="btn btn-primary" disabled={sending}>Enregistrer</button>
          </div>
        </div>
      </form>
    </Modal>
  )
}

function Fournisseurs() {
  const [version, setVersion] = useState(0)
  const [modal, setModal] = useState(null)
  const [busy, setBusy] = useState(false)
  const providers = useApi('/admin/chatbot/fournisseurs', undefined, [version])
  const items = providers.data ?? []

  const test = async (p) => {
    setBusy(true)
    try {
      const r = await api.post(`/admin/chatbot/fournisseurs/${p.id}/tester`)
      window.alert(`${r.ok ? 'OK' : 'Échec'} — ${r.message}${r.modeles?.length ? `\nModèles : ${r.modeles.join(', ')}` : ''}`)
      setVersion((v) => v + 1)
    } catch (err) {
      window.alert(err.message)
    } finally {
      setBusy(false)
    }
  }

  const remove = async (p) => {
    if (!window.confirm('Supprimer ce fournisseur ?')) return
    try { await api.delete(`/admin/chatbot/fournisseurs/${p.id}`); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
  }

  const columns = [
    { key: 'nom', label: 'Nom', render: (p) => <span className="fw-semibold">{p.nom}</span> },
    { key: 'type', label: 'Type', render: (p) => <code>{p.type}</code> },
    { key: 'cle', label: 'Clé', render: (p) => p.cle || '—' },
    { key: 'actif', label: 'État', render: (p) => <StatusBadge value={p.actif ? 'actif' : 'inactif'} /> },
    { key: 'dernier_test', label: 'Dernier test', render: (p) => p.dernier_test ? <span className="text-nowrap small">{p.dernier_test_ok ? '✅' : '❌'} {formatDateTime(p.dernier_test)}</span> : '—' },
    {
      key: 'action', label: '', className: 'text-end',
      render: (p) => (
        <>
          <button type="button" className="btn btn-sm btn-outline-secondary me-1" disabled={busy} onClick={() => test(p)}><i className="bi bi-lightning-charge"></i></button>
          <button type="button" className="btn btn-sm btn-outline-primary me-1" onClick={() => setModal({ mode: 'edit', item: p })}><i className="bi bi-pencil"></i></button>
          <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(p)}><i className="bi bi-trash"></i></button>
        </>
      ),
    },
  ]

  return (
    <div className="card">
      <div className="card-body">
        <div className="d-flex align-items-center justify-content-between mb-2">
          <h5 className="card-title mb-0">Fournisseurs d'IA</h5>
          <button type="button" className="btn btn-primary btn-sm" onClick={() => setModal({ mode: 'new' })}>
            <i className="bi bi-plus-lg me-1"></i> Connecter
          </button>
        </div>
        <DataTable columns={columns} items={items} loading={providers.loading} error={providers.error} emptyText={{ title: 'Aucun fournisseur' }} />
      </div>

      {modal?.mode === 'new' && <ProviderForm open onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />}
      {modal?.mode === 'edit' && <ProviderForm open initial={modal.item} onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />}
    </div>
  )
}

// ── Articles (base de connaissances) ──

function ArticleForm({ open, initial, onClose, onSaved }) {
  const isEdit = Boolean(initial)
  const [form, setForm] = useState(() => (initial ? { titre: initial.titre, contenu: initial.contenu, categorie: initial.categorie ?? '', actif: initial.actif } : { titre: '', contenu: '', categorie: '', actif: true }))
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')
  const set = (k, v) => setForm((f) => ({ ...f, [k]: v }))

  const submit = async (e) => {
    e.preventDefault()
    setSending(true)
    setError('')
    try {
      const body = { titre: form.titre.trim(), contenu: form.contenu.trim(), categorie: form.categorie || null, actif: form.actif }
      if (isEdit) await api.put(`/admin/chatbot/articles/${initial.id}`, body)
      else await api.post('/admin/chatbot/articles', body)
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open={open} onClose={onClose} icon="bi-file-earmark-text" title={isEdit ? "Modifier l'article" : 'Nouvel article'} size="lg">
      <form onSubmit={submit} noValidate>
        {error && <div className="alert alert-danger py-2 small">{error}</div>}
        <div className="row g-3">
          <div className="col-md-8">
            <label className="form-label">Titre *</label>
            <input className="form-control" value={form.titre} onChange={(e) => set('titre', e.target.value)} />
          </div>
          <div className="col-md-4">
            <label className="form-label">Catégorie</label>
            <input className="form-control" value={form.categorie} onChange={(e) => set('categorie', e.target.value)} />
          </div>
          <div className="col-12">
            <label className="form-label">Contenu *</label>
            <textarea className="form-control" rows="8" value={form.contenu} onChange={(e) => set('contenu', e.target.value)} />
          </div>
          <div className="col-12">
            <div className="form-check">
              <input className="form-check-input" type="checkbox" id="aActif" checked={form.actif} onChange={(e) => set('actif', e.target.checked)} />
              <label className="form-check-label" htmlFor="aActif">Actif</label>
            </div>
          </div>
          <div className="col-12 d-flex justify-content-end gap-2">
            <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
            <button type="submit" className="btn btn-primary" disabled={sending}>Enregistrer</button>
          </div>
        </div>
      </form>
    </Modal>
  )
}

function Articles() {
  const [version, setVersion] = useState(0)
  const [page, setPage] = useState(1)
  const [modal, setModal] = useState(null)
  const articles = useApi('/admin/chatbot/articles', { page, per_page: 15 }, [version])
  const items = articles.data?.items ?? []

  const remove = async (a) => {
    if (!window.confirm("Supprimer cet article ?")) return
    try { await api.delete(`/admin/chatbot/articles/${a.id}`); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
  }

  const columns = [
    { key: 'titre', label: 'Titre', render: (a) => <span className="fw-semibold">{a.titre}</span> },
    { key: 'categorie', label: 'Catégorie', render: (a) => a.categorie || '—' },
    { key: 'actif', label: 'État', render: (a) => <StatusBadge value={a.actif ? 'actif' : 'inactif'} /> },
    { key: 'modifie_le', label: 'Modifié le', render: (a) => <span className="text-nowrap small">{formatDateTime(a.modifie_le)}</span> },
    {
      key: 'action', label: '', className: 'text-end',
      render: (a) => (
        <>
          <button type="button" className="btn btn-sm btn-outline-primary me-1" onClick={() => setModal({ mode: 'edit', item: a })}><i className="bi bi-pencil"></i></button>
          <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(a)}><i className="bi bi-trash"></i></button>
        </>
      ),
    },
  ]

  return (
    <div className="card">
      <div className="card-body">
        <div className="d-flex align-items-center justify-content-between mb-2">
          <h5 className="card-title mb-0">Base de connaissances</h5>
          <button type="button" className="btn btn-primary btn-sm" onClick={() => setModal({ mode: 'new' })}>
            <i className="bi bi-plus-lg me-1"></i> Nouvel article
          </button>
        </div>
        <DataTable columns={columns} items={items} meta={articles.data?.meta} onPage={setPage} loading={articles.loading} error={articles.error} emptyText={{ title: 'Aucun article' }} />
      </div>

      {modal?.mode === 'new' && <ArticleForm open onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />}
      {modal?.mode === 'edit' && <ArticleForm open initial={modal.item} onClose={() => setModal(null)} onSaved={() => setVersion((v) => v + 1)} />}
    </div>
  )
}

// ── Sources ──

function Sources() {
  const [version, setVersion] = useState(0)
  const [busy, setBusy] = useState(false)
  const sources = useApi('/admin/chatbot/sources', undefined, [version])
  const items = sources.data ?? []

  const toggle = async (s) => {
    try { await api.patch(`/admin/chatbot/sources/${s.id}?actif=${!s.actif}`); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
  }
  const reindex = async (s) => {
    setBusy(true)
    try { await api.post(`/admin/chatbot/sources/${s.id}/indexer`); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
    finally { setBusy(false) }
  }
  const remove = async (s) => {
    if (!window.confirm('Supprimer cette source ?')) return
    try { await api.delete(`/admin/chatbot/sources/${s.id}`); setVersion((v) => v + 1) }
    catch (err) { window.alert(err.message) }
  }
  const upload = async (e) => {
    const file = e.target.files[0]
    if (!file) return
    setBusy(true)
    try {
      const fd = new FormData()
      fd.append('fichier', file)
      await api.post('/admin/chatbot/documents', fd)
      setVersion((v) => v + 1)
    } catch (err) {
      window.alert(err.message)
    } finally {
      setBusy(false)
      e.target.value = ''
    }
  }

  const columns = [
    { key: 'nom', label: 'Nom', render: (s) => <span className="fw-semibold">{s.nom}</span> },
    { key: 'type', label: 'Type', render: (s) => <code>{s.type}</code> },
    { key: 'statut', label: 'Statut', render: (s) => <StatusBadge value={s.statut} /> },
    { key: 'passages', label: 'Passages' },
    { key: 'actif', label: 'État', render: (s) => <StatusBadge value={s.actif ? 'actif' : 'inactif'} /> },
    {
      key: 'action', label: '', className: 'text-end',
      render: (s) => (
        <>
          <button type="button" className="btn btn-sm btn-outline-secondary me-1" disabled={busy} onClick={() => reindex(s)}><i className="bi bi-arrow-repeat"></i></button>
          <button type="button" className="btn btn-sm btn-outline-primary me-1" onClick={() => toggle(s)}><i className="bi bi-power"></i></button>
          {s.type !== 'kb' && <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(s)}><i className="bi bi-trash"></i></button>}
        </>
      ),
    },
  ]

  return (
    <div className="card">
      <div className="card-body">
        <div className="d-flex align-items-center justify-content-between mb-2">
          <h5 className="card-title mb-0">Sources de connaissance</h5>
          <label className="btn btn-primary btn-sm mb-0">
            <i className="bi bi-cloud-arrow-up me-1"></i> Envoyer un document
            <input type="file" accept=".pdf,.docx,.xlsx,.csv,.txt,.md" className="d-none" onChange={upload} />
          </label>
        </div>
        <DataTable columns={columns} items={items} loading={sources.loading} error={sources.error} emptyText={{ title: 'Aucune source' }} />
      </div>
    </div>
  )
}

// ── Conversations ──

function Conversations() {
  const [version, setVersion] = useState(0)
  const [page, setPage] = useState(1)
  const [statut, setStatut] = useState('pending_human')
  const [selected, setSelected] = useState(null)

  const convs = useApi('/admin/chatbot/conversations', { statut: statut || undefined, page, per_page: 15 }, [version])
  const items = convs.data?.items ?? []

  const columns = [
    { key: 'visiteur', label: 'Visiteur', render: (c) => <span className="fw-semibold">{c.visiteur || 'Anonyme'}</span> },
    { key: 'email', label: 'Email', render: (c) => c.email || '—' },
    { key: 'statut', label: 'Statut', render: (c) => <StatusBadge value={c.statut} /> },
    { key: 'messages', label: 'Messages' },
    { key: 'dernier_message', label: 'Dernier message', render: (c) => <span className="text-truncate d-inline-block" style={{ maxWidth: 260 }}>{c.dernier_message || '—'}</span> },
    { key: 'mis_a_jour', label: 'Mis à jour', render: (c) => <span className="text-nowrap small">{formatDateTime(c.mis_a_jour)}</span> },
    {
      key: 'action', label: '', className: 'text-end',
      render: (c) => (
        <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => { setSelected(c); setVersion((v) => v + 1) }}>
          <i className="bi bi-chat-left-text me-1"></i> Ouvrir
        </button>
      ),
    },
  ]

  return (
    <div className="card">
      <div className="card-body">
        <div className="d-flex align-items-center justify-content-between mb-2">
          <h5 className="card-title mb-0">Conversations</h5>
          <select className="form-select form-select-sm w-auto" value={statut} onChange={(e) => { setStatut(e.target.value); setPage(1) }}>
            <option value="">Toutes</option>
            <option value="pending_human">En attente conseiller</option>
            <option value="human">Prise en main</option>
            <option value="bot">Bot</option>
            <option value="closed">Clôturées</option>
          </select>
        </div>
        <DataTable columns={columns} items={items} meta={convs.data?.meta} onPage={setPage} loading={convs.loading} error={convs.error} emptyText={{ title: 'Aucune conversation' }} />
      </div>

      <ConversationModal conv={selected} onClose={() => setSelected(null)} onSaved={() => setVersion((v) => v + 1)} />
    </div>
  )
}

function ConversationModal({ conv, onClose, onSaved }) {
  const [version, setVersion] = useState(0)
  const [reponse, setReponse] = useState('')
  const [busy, setBusy] = useState(false)
  const detail = useApi(conv ? `/admin/chatbot/conversations/${conv.id}` : null, undefined, [conv?.id, version])
  const c = detail.data

  const act = async (fn) => {
    setBusy(true)
    try { await fn(); setVersion((v) => v + 1); onSaved() }
    catch (err) { window.alert(err.message) }
    finally { setBusy(false) }
  }

  const reply = async () => {
    if (!reponse.trim()) return
    await act(async () => { await api.post(`/admin/chatbot/conversations/${conv.id}/repondre`, { texte: reponse.trim() }); setReponse('') })
  }

  return (
    <Modal open={Boolean(conv)} onClose={onClose} icon="bi-chat-dots" title={`Conversation #${conv?.id}`} size="lg">
      {conv && (
        <div>
          <div className="d-flex align-items-center gap-2 mb-3">
            <StatusBadge value={c?.statut ?? conv.statut} />
            <span className="text-muted small">{c?.visiteur || conv.visiteur || 'Anonyme'}</span>
            {c?.email && <span className="text-muted small">({c.email})</span>}
            <div className="ms-auto d-flex gap-2">
              {(c?.statut ?? conv.statut) !== 'human' && (c?.statut ?? conv.statut) !== 'closed' && (
                <button type="button" className="btn btn-sm btn-primary" disabled={busy} onClick={() => act(() => api.post(`/admin/chatbot/conversations/${conv.id}/prendre`))}>
                  <i className="bi bi-person-check me-1"></i> Prendre la main
                </button>
              )}
              {(c?.statut ?? conv.statut) === 'human' && (
                <button type="button" className="btn btn-sm btn-outline-secondary" disabled={busy} onClick={() => act(() => api.post(`/admin/chatbot/conversations/${conv.id}/rendre-au-bot`))}>
                  <i className="bi bi-robot me-1"></i> Rendre au bot
                </button>
              )}
              {(c?.statut ?? conv.statut) !== 'closed' && (
                <button type="button" className="btn btn-sm btn-outline-danger" disabled={busy} onClick={() => act(() => api.post(`/admin/chatbot/conversations/${conv.id}/fermer`))}>
                  <i className="bi bi-x-circle me-1"></i> Clore
                </button>
              )}
            </div>
          </div>

          <div className="mb-3" style={{ maxHeight: 360, overflowY: 'auto' }}>
            {detail.loading ? <Spinner /> : (c?.historique ?? []).map((m) => (
              <div key={m.id} className={`mb-2 ${m.auteur === 'agent' || m.auteur === 'conseiller' || m.auteur === 'system' ? 'text-end' : ''}`}>
                <div className={`d-inline-block p-2 rounded small ${m.auteur === 'agent' || m.auteur === 'conseiller' ? 'bg-primary text-white' : m.auteur === 'system' ? 'bg-light text-muted' : 'bg-light'}`} style={{ maxWidth: '80%', textAlign: 'left' }}>
                  {m.texte}
                </div>
                <div className="text-muted" style={{ fontSize: 10 }}>{m.auteur} · {formatDateTime(m.le)}</div>
              </div>
            ))}
          </div>

          {(c?.statut ?? conv.statut) !== 'closed' && (
            <div className="d-flex gap-2">
              <input className="form-control form-control-sm" placeholder="Répondre au visiteur…" value={reponse} onChange={(e) => setReponse(e.target.value)} />
              <button type="button" className="btn btn-sm btn-primary text-nowrap" disabled={!reponse.trim() || busy} onClick={reply}>
                <i className="bi bi-send me-1"></i> Envoyer
              </button>
            </div>
          )}
        </div>
      )}
    </Modal>
  )
}

// ── Statistiques ──

function Stats() {
  const stats = useApi('/admin/chatbot/statistiques', { jours: 30 })

  if (stats.loading) return <Spinner />
  if (stats.error) return <div className="alert alert-danger">{stats.error.message}</div>
  const s = stats.data

  return (
    <div className="row g-3">
      <div className="col-md-4">
        <div className="card info-card sales-card">
          <div className="card-body">
            <h5 className="card-title">Conversations (30 j)</h5>
            <div className="d-flex align-items-center">
              <div className="card-icon rounded-circle d-flex align-items-center justify-content-center"><i className="bi bi-chat-dots"></i></div>
              <div className="ps-3"><h6>{s.conversations}</h6></div>
            </div>
          </div>
        </div>
      </div>
      <div className="col-md-4">
        <div className="card info-card revenue-card">
          <div className="card-body">
            <h5 className="card-title">En attente conseiller</h5>
            <div className="d-flex align-items-center">
              <div className="card-icon rounded-circle d-flex align-items-center justify-content-center"><i className="bi bi-person-exclamation"></i></div>
              <div className="ps-3"><h6>{s.en_attente_conseiller}</h6></div>
            </div>
          </div>
        </div>
      </div>
      <div className="col-md-4">
        <div className="card info-card customers-card">
          <div className="card-body">
            <h5 className="card-title">Avis utiles</h5>
            <div className="d-flex align-items-center">
              <div className="card-icon rounded-circle d-flex align-items-center justify-content-center"><i className="bi bi-hand-thumbs-up"></i></div>
              <div className="ps-3"><h6>{s.avis?.utiles ?? 0}</h6><span className="text-muted small">{s.avis?.pas_utiles ?? 0} inutiles</span></div>
            </div>
          </div>
        </div>
      </div>

      <div className="col-12">
        <div className="card">
          <div className="card-body">
            <h5 className="card-title">Consommation par modèle</h5>
            {s.consommation?.length ? (
              <table className="table table-sm mb-0">
                <thead><tr><th>Fournisseur</th><th>Modèle</th><th>Appels</th><th>Jetons entrée</th><th>Jetons sortie</th><th>Échecs</th></tr></thead>
                <tbody>
                  {s.consommation.map((c, i) => (
                    <tr key={i}>
                      <td>{c.fournisseur}</td><td><code>{c.modele}</code></td><td>{c.appels}</td>
                      <td>{c.jetons_entree}</td><td>{c.jetons_sortie}</td><td>{c.echecs}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            ) : <p className="text-muted mb-0">Aucune consommation.</p>}
          </div>
        </div>
      </div>

      {s.questions_sans_reponse?.length > 0 && (
        <div className="col-12">
          <div className="card">
            <div className="card-body">
              <h5 className="card-title">Questions sans réponse</h5>
              <ul className="mb-0">
                {s.questions_sans_reponse.map((q, i) => <li key={i} className="small">{q}</li>)}
              </ul>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

// ── Tables ──

function TableForm({ open, table, rule, onClose, onSaved }) {
  const [form, setForm] = useState({
    colonnes: rule?.colonnes ?? table?.colonnes_choisies ?? [],
    modele_texte: rule?.modele_texte ?? '',
    colonne_titre: rule?.colonne_titre ?? '',
    synchronisation: rule?.synchronisation ?? 'nightly',
    max_lignes: rule?.max_lignes ?? 5000,
  })
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')
  const [apercu, setApercu] = useState(null)
  const [previewing, setPreviewing] = useState(false)

  const toggle = (name) => setForm((f) => ({ ...f, colonnes: f.colonnes.includes(name) ? f.colonnes.filter((c) => c !== name) : [...f.colonnes, name] }))

  const payload = () => ({
    table: table.table, colonnes: form.colonnes, filtres: rule?.filtres ?? [],
    modele_texte: form.modele_texte || null, colonne_titre: form.colonne_titre || null,
    synchronisation: form.synchronisation, max_lignes: Number(form.max_lignes) || 5000,
  })

  const preview = async () => {
    if (!form.colonnes.length) return
    setPreviewing(true)
    setError('')
    try {
      setApercu(await api.post('/admin/chatbot/tables/apercu', payload()))
    } catch (err) {
      setError(err.message)
    } finally {
      setPreviewing(false)
    }
  }

  const submit = async () => {
    if (!form.colonnes.length) return
    setSending(true)
    setError('')
    try {
      if (rule) await api.put(`/admin/chatbot/tables/${rule.id}`, payload())
      else await api.post('/admin/chatbot/tables', payload())
      onSaved(); onClose()
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <Modal open={open} onClose={onClose} icon="bi-table" title={`Table « ${table?.table} »`} size="lg">
      {error && <div className="alert alert-danger py-2 small">{error}</div>}
      <div className="mb-3">
        <label className="form-label">Colonnes que l'IA pourra lire</label>
        <div className="border rounded p-2" style={{ maxHeight: 220, overflowY: 'auto' }}>
          {(table?.colonnes ?? []).map((c) => (
            <div className="form-check" key={c.name}>
              <input className="form-check-input" type="checkbox" id={`col-${c.name}`} checked={form.colonnes.includes(c.name)}
                     disabled={c.blocked} onChange={() => toggle(c.name)} />
              <label className="form-check-label" htmlFor={`col-${c.name}`}>
                <code>{c.name}</code> <span className="text-muted small">({c.type})</span>
                {c.blocked && <span className="badge bg-danger ms-1">bloquée</span>}
                {c.sensitive && <span className="badge bg-warning text-dark ms-1">sensible</span>}
              </label>
            </div>
          ))}
        </div>
      </div>
      <div className="row g-3">
        <div className="col-12">
          <label className="form-label">Modèle de texte (ex. « Produit {`{nom}`} : {`{description}`} »)</label>
          <input className="form-control" value={form.modele_texte} onChange={(e) => setForm({ ...form, modele_texte: e.target.value })} />
        </div>
        <div className="col-md-6">
          <label className="form-label">Colonne titre</label>
          <input className="form-control" value={form.colonne_titre} onChange={(e) => setForm({ ...form, colonne_titre: e.target.value })} />
        </div>
        <div className="col-md-6">
          <label className="form-label">Synchronisation</label>
          <select className="form-select" value={form.synchronisation} onChange={(e) => setForm({ ...form, synchronisation: e.target.value })}>
            <option value="nightly">Chaque nuit</option>
            <option value="manual">Manuelle</option>
          </select>
        </div>
        {apercu && (
          <div className="col-12">
            <label className="form-label">Aperçu — ce que l'IA lira ({apercu.lignes} ligne{apercu.lignes > 1 ? 's' : ''})</label>
            <pre className="bg-light border rounded p-2 small mb-0" style={{ whiteSpace: 'pre-wrap', maxHeight: 180, overflowY: 'auto' }}>
              {(apercu.exemples ?? []).length ? apercu.exemples.join('\n') : 'Aucune ligne.'}
            </pre>
          </div>
        )}
        <div className="col-12 d-flex justify-content-end gap-2">
          <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Annuler</button>
          <button type="button" className="btn btn-outline-primary" disabled={previewing || !form.colonnes.length} onClick={preview}>
            {previewing ? <span className="spinner-border spinner-border-sm me-1"></span> : <i className="bi bi-eye me-1"></i>} Aperçu
          </button>
          <button type="button" className="btn btn-primary" disabled={sending || !form.colonnes.length} onClick={submit}>
            {rule ? 'Enregistrer' : "Donner à l'IA"}
          </button>
        </div>
      </div>
    </Modal>
  )
}

function Tables() {
  const [version, setVersion] = useState(0)
  const [selected, setSelected] = useState(null)
  const tables = useApi('/admin/chatbot/tables', undefined, [version])
  const regles = useApi('/admin/chatbot/tables/regles', undefined, [version])
  const items = tables.data ?? []
  const selectedRule = selected ? (regles.data ?? []).find((r) => r.table === selected.table) ?? null : null

  const columns = [
    { key: 'table', label: 'Table', render: (t) => <code>{t.table}</code> },
    { key: 'lignes_approx', label: 'Lignes' },
    { key: 'selectionnee', label: 'État', render: (t) => (t.bloquee ? <span className="badge bg-danger">bloquée</span> : <StatusBadge value={t.selectionnee ? 'actif' : 'inactif'} />) },
    { key: 'colonnes_choisies', label: 'Colonnes choisies', render: (t) => (t.colonnes_choisies ?? []).join(', ') || '—' },
    {
      key: 'action', label: '', className: 'text-end',
      render: (t) => (!t.bloquee && (
        <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => setSelected(t)}>
          <i className="bi bi-pencil me-1"></i> {t.selectionnee ? 'Modifier' : 'Sélectionner'}
        </button>
      )),
    },
  ]

  return (
    <div className="card">
      <div className="card-body">
        <h5 className="card-title">Tables données à l'IA</h5>
        <DataTable columns={columns} items={items} loading={tables.loading} error={tables.error} emptyText={{ title: 'Aucune table' }} />
      </div>
      {selected && !regles.loading && (
        <TableForm open table={selected} rule={selectedRule} onClose={() => setSelected(null)} onSaved={() => setVersion((v) => v + 1)} />
      )}
    </div>
  )
}

// ── Tester (espace de test) ──

function Tester() {
  const [question, setQuestion] = useState('')
  const [result, setResult] = useState(null)
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')

  const test = async () => {
    if (question.trim().length < 2) return
    setSending(true)
    setError('')
    try {
      const r = await api.post('/admin/chatbot/tester', { question: question.trim() })
      setResult(r)
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <div className="card">
      <div className="card-body">
        <h5 className="card-title">Espace de test</h5>
        <div className="row g-3">
          <div className="col-12">
            <label className="form-label">Question</label>
            <div className="d-flex gap-2">
              <input className="form-control" value={question} onChange={(e) => setQuestion(e.target.value)}
                     placeholder="Posez une question au chatbot…" onKeyDown={(e) => e.key === 'Enter' && test()} />
              <button type="button" className="btn btn-primary text-nowrap" disabled={sending} onClick={test}>
                {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : null} Tester
              </button>
            </div>
          </div>

          {error && <div className="col-12"><div className="alert alert-danger py-2">{error}</div></div>}

          {result && (
            <>
              <div className="col-12">
                <div className="p-3 bg-light rounded">
                  <strong>{result.modele || 'Réponse'}</strong>
                  <p className="mb-0 mt-1">{result.reponse || result.erreur || '—'}</p>
                </div>
              </div>
              {result.passages?.length > 0 && (
                <div className="col-12">
                  <label className="form-label">Passages retrouvés</label>
                  <table className="table table-sm mb-0">
                    <thead><tr><th>Source</th><th>Score</th><th>Extrait</th></tr></thead>
                    <tbody>
                      {result.passages.map((p, i) => (
                        <tr key={i} className={p.retenu ? '' : 'text-muted'}>
                          <td className="text-nowrap">{p.source || '—'}</td>
                          <td>{p.score?.toFixed?.(3) ?? p.score}</td>
                          <td className="small">{p.extrait}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  )
}

// ── Page principale ──

export default function Chatbot() {
  const [tab, setTab] = useState('reglages')

  return (
    <>
      <PageTitle title="Chatbot" crumbs={[{ label: 'Chatbot' }]} />
      <section className="section">
        <ul className="nav nav-tabs nav-tabs-bordered mb-3">
          {TABS.map(([key, label]) => (
            <li className="nav-item" key={key}>
              <button type="button" className={`nav-link${tab === key ? ' active' : ''}`} onClick={() => setTab(key)}>{label}</button>
            </li>
          ))}
        </ul>

        {tab === 'reglages' && <Reglages />}
        {tab === 'fournisseurs' && <Fournisseurs />}
        {tab === 'articles' && <Articles />}
        {tab === 'sources' && <Sources />}
        {tab === 'tables' && <Tables />}
        {tab === 'conversations' && <Conversations />}
        {tab === 'tester' && <Tester />}
        {tab === 'stats' && <Stats />}
      </section>
    </>
  )
}
