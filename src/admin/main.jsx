// Point d'entrée de l'administration et de l'espace médecin (admin.html), dans le même projet que le site public.
// Styles propres à l'admin (NiceAdmin aux couleurs NUFOTEC) : chargés seulement ici, jamais sur le site public.
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.min.css'
import '../styles/fonts.css'
import './styles/niceadmin.css'
import './styles/theme.css'
import App from './App.jsx'

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
