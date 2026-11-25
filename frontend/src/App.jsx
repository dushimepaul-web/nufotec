import { lazy } from 'react'
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import PublicLayout from './layout/PublicLayout'
import { ROUTES } from './lib/routes'
import { SiteProvider } from './lib/site'
import Home from './pages/Home'

// Pages chargées à la demande : la page d'accueil s'ouvre sans télécharger tout le site (connexions lentes).
// Le chargement s'affiche dans PublicLayout (Suspense autour de <Outlet />).
const Account = lazy(() => import('./pages/Account'))
const Article = lazy(() => import('./pages/Article'))
const Blog = lazy(() => import('./pages/Blog'))
const Booking = lazy(() => import('./pages/Booking'))
const Contact = lazy(() => import('./pages/Contact'))
const Faq = lazy(() => import('./pages/Faq'))
const Broker = lazy(() => import('./pages/Broker'))
const Doctors = lazy(() => import('./pages/Doctors'))
const Investor = lazy(() => import('./pages/Investor'))
const Login = lazy(() => import('./pages/Login'))
const Media = lazy(() => import('./pages/Media'))
const MediaDetail = lazy(() => import('./pages/MediaDetail'))
const Presentation = lazy(() => import('./pages/Presentation'))
const Product = lazy(() => import('./pages/Product'))
const Shop = lazy(() => import('./pages/Shop'))
const StaticPage = lazy(() => import('./pages/StaticPage'))

export default function App() {
  return (
    <SiteProvider>
      <BrowserRouter>
        <Routes>
          <Route element={<PublicLayout />}>
            <Route index element={<Home />} />
            {/* Pages publiques à convertir une par une depuis les vues PHP */}
            <Route path="produits" element={<Shop />} />
            <Route path="produits/:slug" element={<Product />} />
            <Route path="teleconsultation" element={<Doctors />} />
            <Route path="teleconsultation/rendez-vous/:uuid" element={<Booking />} />
            <Route path="medias" element={<Media />} />
            <Route path="medias/:slug" element={<MediaDetail />} />
            <Route path="contact" element={<Contact />} />
            <Route path="actualites" element={<Blog />} />
            <Route path="actualites/:slug" element={<Article />} />
            <Route path="faq" element={<Faq />} />
            <Route path="investisseur" element={<Investor />} />
            <Route path="courtier" element={<Broker />} />
            <Route path="presentation" element={<Presentation />} />
            <Route path="connexion" element={<Login />} />
            <Route path="mon-compte" element={<Account />} />
            {/* anciennes pages de l'administration qui doublonnent des pages du site */}
            <Route path="home" element={<Navigate to={ROUTES.home} replace />} />
            <Route path="investors-form" element={<Navigate to={ROUTES.investor} replace />} />
            <Route path="brokers-form" element={<Navigate to={ROUTES.broker} replace />} />
            <Route path=":slug" element={<StaticPage />} />
          </Route>
        </Routes>
      </BrowserRouter>
    </SiteProvider>
  )
}
