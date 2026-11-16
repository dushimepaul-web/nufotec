// Connexion (admin / médecin). Après succès, redirection selon le rôle.
import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import api from '../lib/api'
import { saveSession } from '../lib/auth'
import { homeFor } from '../lib/routes'

export default function Login() {
  const navigate = useNavigate()
  const [form, setForm] = useState({ login: '', password: '' })
  const [sending, setSending] = useState(false)
  const [error, setError] = useState('')

  const submit = async (e) => {
    e.preventDefault()
    setError('')
    setSending(true)
    try {
      const data = await api.post('/auth/login', form)
      saveSession(data)
      navigate(homeFor(data.user?.role?.slug), { replace: true })
    } catch (err) {
      setError(err.message)
    } finally {
      setSending(false)
    }
  }

  return (
    <main>
      <div className="container">
        <section className="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
          <div className="container">
            <div className="row justify-content-center">
              <div className="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                <div className="d-flex justify-content-center py-4">
                  <a href="/" className="logo d-flex align-items-center w-auto">
                    <img src="/logo.jpeg" alt="NUFOTEC" />
                    <span className="d-none d-lg-block">NUFOTEC</span>
                  </a>
                </div>

                <div className="card mb-3">
                  <div className="card-body">
                    <div className="pt-4 pb-2">
                      <h5 className="card-title text-center pb-0 fs-4">Connexion</h5>
                      <p className="text-center small">Espace administration & médecins</p>
                    </div>

                    {error && <div className="alert alert-danger py-2 small" role="alert">{error}</div>}

                    <form className="row g-3" onSubmit={submit}>
                      <div className="col-12">
                        <label htmlFor="login" className="form-label">Email ou téléphone</label>
                        <input type="text" className="form-control" id="login" required value={form.login}
                               autoComplete="username" placeholder="admin@nufotec.bi"
                               onChange={(e) => setForm({ ...form, login: e.target.value })} />
                      </div>
                      <div className="col-12">
                        <label htmlFor="password" className="form-label">Mot de passe</label>
                        <input type="password" className="form-control" id="password" required value={form.password}
                               autoComplete="current-password"
                               onChange={(e) => setForm({ ...form, password: e.target.value })} />
                      </div>
                      <div className="col-12">
                        <button className="btn btn-primary w-100" type="submit" disabled={sending}>
                          {sending ? <span className="spinner-border spinner-border-sm me-1"></span> : null}
                          Se connecter
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </main>
  )
}
