import { lazy } from 'react'
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { getUser } from './lib/auth'
import { homeFor, ROUTES } from './lib/routes'
import AdminLayout from './layout/AdminLayout'
import DoctorLayout from './layout/DoctorLayout'
import Protected from './layout/Protected'
import Login from './pages/Login'
import NotFound from './pages/NotFound'

// Pages chargées à la demande (code-splitting) pour alléger le bundle initial.
const Dashboard = lazy(() => import('./pages/admin/Dashboard'))
const UsersList = lazy(() => import('./pages/admin/users/UsersList'))
const UserDetail = lazy(() => import('./pages/admin/users/UserDetail'))
const MedecinsList = lazy(() => import('./pages/admin/medecins/MedecinsList'))
const MedecinForm = lazy(() => import('./pages/admin/medecins/MedecinForm'))
const MedecinDetail = lazy(() => import('./pages/admin/medecins/MedecinDetail'))
const ConsultationsList = lazy(() => import('./pages/admin/consultations/ConsultationsList'))
const ConsultationDetail = lazy(() => import('./pages/admin/consultations/ConsultationDetail'))
const ModesPaiement = lazy(() => import('./pages/admin/consultations/ModesPaiement'))
const ProduitsList = lazy(() => import('./pages/admin/produits/ProduitsList'))
const ProduitForm = lazy(() => import('./pages/admin/produits/ProduitForm'))
const CategoriesList = lazy(() => import('./pages/admin/produits/CategoriesList'))
const CommandesList = lazy(() => import('./pages/admin/commandes/CommandesList'))
const CommandeDetail = lazy(() => import('./pages/admin/commandes/CommandeDetail'))
const Parametres = lazy(() => import('./pages/admin/contenu/Parametres'))
const Actualites = lazy(() => import('./pages/admin/contenu/Actualites'))
const ActualiteForm = lazy(() => import('./pages/admin/contenu/ActualiteForm'))
const Faq = lazy(() => import('./pages/admin/contenu/Faq'))
const Messages = lazy(() => import('./pages/admin/contenu/Messages'))
const Newsletter = lazy(() => import('./pages/admin/contenu/Newsletter'))
const Pages = lazy(() => import('./pages/admin/contenu/Pages'))
const PageForm = lazy(() => import('./pages/admin/contenu/PageForm'))
const Temoignages = lazy(() => import('./pages/admin/contenu/Temoignages'))
const Partenaires = lazy(() => import('./pages/admin/contenu/Partenaires'))
const ChiffresCles = lazy(() => import('./pages/admin/contenu/ChiffresCles'))
const ReseauxSociaux = lazy(() => import('./pages/admin/contenu/ReseauxSociaux'))
const Visites = lazy(() => import('./pages/admin/contenu/Visites'))
const Roles = lazy(() => import('./pages/admin/roles/Roles'))
const InvestisseursList = lazy(() => import('./pages/admin/investisseurs/InvestisseursList'))
const InvestisseurDetail = lazy(() => import('./pages/admin/investisseurs/InvestisseurDetail'))
const CourtiersList = lazy(() => import('./pages/admin/investisseurs/CourtiersList'))
const Phases = lazy(() => import('./pages/admin/investisseurs/Phases'))
const DataRoom = lazy(() => import('./pages/admin/investisseurs/DataRoom'))
const Journal = lazy(() => import('./pages/admin/journal/Journal'))
const Statistiques = lazy(() => import('./pages/admin/Statistiques'))
const MediasList = lazy(() => import('./pages/admin/medias/MediasList'))
const MediaDetail = lazy(() => import('./pages/admin/medias/MediaDetail'))
const Commentaires = lazy(() => import('./pages/admin/medias/Commentaires'))
const Collections = lazy(() => import('./pages/admin/medias/Collections'))
const Chatbot = lazy(() => import('./pages/admin/chatbot/Chatbot'))
const DoctorDashboard = lazy(() => import('./pages/medecin/Dashboard'))
const DoctorProfil = lazy(() => import('./pages/medecin/DoctorProfil'))
const DoctorHoraires = lazy(() => import('./pages/medecin/DoctorHoraires'))
const DoctorAbsences = lazy(() => import('./pages/medecin/DoctorAbsences'))
const DoctorConsultationDetail = lazy(() => import('./pages/medecin/DoctorConsultationDetail'))

// Redirection de la racine selon le rôle.
function Home() {
  const user = getUser()
  return <Navigate to={user ? homeFor(user.role?.slug) : ROUTES.login} replace />
}

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path={ROUTES.login} element={<Login />} />

        {/* Espace administrateur */}
        <Route element={<Protected roles={['admin']} />}>
          <Route path="/admin" element={<AdminLayout />}>
            <Route index element={<Dashboard />} />
            <Route path="utilisateurs" element={<UsersList />} />
            <Route path="utilisateurs/:uuid" element={<UserDetail />} />
            <Route path="roles" element={<Roles />} />
            <Route path="medecins" element={<MedecinsList />} />
            <Route path="medecins/nouveau" element={<MedecinForm />} />
            <Route path="medecins/:uuid" element={<MedecinDetail />} />
            <Route path="medecins/:uuid/modifier" element={<MedecinForm />} />
            <Route path="consultations" element={<ConsultationsList />} />
            <Route path="consultations/:numero" element={<ConsultationDetail />} />
            <Route path="modes-paiement" element={<ModesPaiement />} />
            <Route path="produits" element={<ProduitsList />} />
            <Route path="produits/nouveau" element={<ProduitForm />} />
            <Route path="produits/:uuid/modifier" element={<ProduitForm />} />
            <Route path="categories" element={<CategoriesList />} />
            <Route path="commandes" element={<CommandesList />} />
            <Route path="commandes/:numero" element={<CommandeDetail />} />
            <Route path="contenu/pages" element={<Pages />} />
            <Route path="contenu/pages/nouveau" element={<PageForm />} />
            <Route path="contenu/pages/:id" element={<PageForm />} />
            <Route path="contenu/actualites" element={<Actualites />} />
            <Route path="contenu/actualites/nouveau" element={<ActualiteForm />} />
            <Route path="contenu/actualites/:id/modifier" element={<ActualiteForm />} />
            <Route path="contenu/faq" element={<Faq />} />
            <Route path="contenu/parametres" element={<Parametres />} />
            <Route path="contenu/messages" element={<Messages />} />
            <Route path="contenu/newsletter" element={<Newsletter />} />
            <Route path="contenu/temoignages" element={<Temoignages />} />
            <Route path="contenu/partenaires" element={<Partenaires />} />
            <Route path="contenu/chiffres-cles" element={<ChiffresCles />} />
            <Route path="contenu/reseaux-sociaux" element={<ReseauxSociaux />} />
            <Route path="contenu/visites" element={<Visites />} />
            <Route path="investisseurs" element={<InvestisseursList />} />
            <Route path="investisseurs/:uuid" element={<InvestisseurDetail />} />
            <Route path="courtiers" element={<CourtiersList />} />
            <Route path="investissement/phases" element={<Phases />} />
            <Route path="data-room" element={<DataRoom />} />
            <Route path="medias" element={<MediasList />} />
            <Route path="medias/commentaires" element={<Commentaires />} />
            <Route path="medias/collections" element={<Collections />} />
            <Route path="medias/:uuid" element={<MediaDetail />} />
            <Route path="chatbot" element={<Chatbot />} />
            <Route path="journal" element={<Journal />} />
            <Route path="statistiques" element={<Statistiques />} />
          </Route>
        </Route>

        {/* Espace médecin */}
        <Route element={<Protected roles={['medecin']} />}>
          <Route path="/medecin" element={<DoctorLayout />}>
            <Route index element={<DoctorDashboard />} />
            <Route path="consultations" element={<DoctorDashboard />} />
            <Route path="consultations/:numero" element={<DoctorConsultationDetail />} />
            <Route path="profil" element={<DoctorProfil />} />
            <Route path="horaires" element={<DoctorHoraires />} />
            <Route path="absences" element={<DoctorAbsences />} />
          </Route>
        </Route>

        <Route path="/" element={<Home />} />
        <Route path="*" element={<NotFound />} />
      </Routes>
    </BrowserRouter>
  )
}
