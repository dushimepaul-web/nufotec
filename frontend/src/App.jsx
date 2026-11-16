import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import PublicLayout from './layout/PublicLayout'
import { ROUTES } from './lib/routes'
import { SiteProvider } from './lib/site'
import Account from './pages/Account'
import Article from './pages/Article'
import Blog from './pages/Blog'
import Booking from './pages/Booking'
import Contact from './pages/Contact'
import Faq from './pages/Faq'
import Broker from './pages/Broker'
import Doctors from './pages/Doctors'
import Home from './pages/Home'
import Investor from './pages/Investor'
import Login from './pages/Login'
import Media from './pages/Media'
import MediaDetail from './pages/MediaDetail'
import Presentation from './pages/Presentation'
import Product from './pages/Product'
import Shop from './pages/Shop'
import StaticPage from './pages/StaticPage'

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
