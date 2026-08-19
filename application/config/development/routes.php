<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ============================================
// CONFIGURATION DES ROUTES
// ============================================

$route['default_controller'] = 'Home/Home/index';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// ============================================
// CHANGEMENT DE LANGUE
// ============================================
// $route['switch_lang/(fr|en|sw)'] = 'Home/Home/switch_lang/$1';

// ============================================
// ROUTES ADMIN
// ============================================
$route['Admin'] = 'Admin/index';
$route['admin'] = 'Admin/index';
$route['Admin/do_login'] = 'Admin/do_login';
$route['admin/do_login'] = 'Admin/do_login';
$route['administration'] = 'Admin/index';

// ============================================
// ROUTES ADVERTISE PRODUCT - COMPLÈTES
// ============================================

// Routes pour le CRON et promo (sans authentification)
$route['advertise-product/cron_daily_promo/(:any)'] = 'Advertise_product/cron_daily_promo/$1';
$route['Advertise-product/cron_daily_promo/(:any)'] = 'Advertise_product/cron_daily_promo/$1';
$route['advertise-product/promo'] = 'Advertise_product/promo';
$route['Advertise-product/promo'] = 'Advertise_product/promo';

// Routes CRUD principales
$route['advertise-product'] = 'Advertise_product/index';
$route['Advertise-product'] = 'Advertise_product/index';
$route['advertise-product/index'] = 'Advertise_product/index';
$route['Advertise-product/index'] = 'Advertise_product/index';

// Routes CRUD - Create, Update, Delete
$route['advertise-product/create'] = 'Advertise_product/create';
$route['Advertise-product/create'] = 'Advertise_product/create';
$route['advertise-product/update'] = 'Advertise_product/update';
$route['advertise-product-update'] = 'Advertise_product/update';

$route['Advertise-product/update'] = 'Advertise_product/update';
$route['advertise-product/delete'] = 'Advertise_product/delete';

$route['Advertise-product/delete'] = 'Advertise_product/delete';

// Routes pour changer le statut
$route['advertise-product/change-status'] = 'Advertise_product/ChangeStatus';
$route['Advertise-product/change-status'] = 'Advertise_product/ChangeStatus';
$route['advertise-product/change-featured'] = 'Advertise_product/ChangeFeatured';
$route['Advertise-product/change-featured'] = 'Advertise_product/ChangeFeatured';

// Route pour voir le détail d'un produit
$route['advertise-product/view/(:any)'] = 'Advertise_product/ProductDetail/$1';
$route['Advertise-product/view/(:any)'] = 'Advertise_product/ProductDetail/$1';

// Routes pour les catégories de produits
$route['product-categories'] = 'Advertise_product/Product_categories/index';
$route['Product-categories'] = 'Advertise_product/Product_categories/index';
$route['product-categories/index'] = 'Advertise_product/Product_categories/index';
$route['product-categories/create'] = 'Advertise_product/Product_categories/create';
$route['product-categories/update'] = 'Advertise_product/Product_categories/update';
$route['product-categories/delete'] = 'Advertise_product/Product_categories/delete';
$route['product-categories/view/(:num)'] = 'Advertise_product/Product_categories/view/$1';

// ============================================
// ROUTES COMMANDES WHATSAPP (order_requests)
// ============================================
$route['commande_whatsapp'] = 'Commande_whatsapp/index';
$route['Commande_whatsapp'] = 'Commande_whatsapp/index';
$route['commande_whatsapp/index'] = 'Commande_whatsapp/index';
$route['commande_whatsapp/search'] = 'Commande_whatsapp/search';
$route['commande_whatsapp/view_order/(:num)'] = 'Commande_whatsapp/view_order/$1';
$route['commande_whatsapp/ChangeStatus'] = 'Commande_whatsapp/ChangeStatus';
$route['commande_whatsapp/change-status'] = 'Commande_whatsapp/ChangeStatus';
$route['commande_whatsapp/Delete'] = 'Commande_whatsapp/Delete';
$route['commande_whatsapp/delete'] = 'Commande_whatsapp/Delete';
$route['commande_whatsapp/export'] = 'Commande_whatsapp/export_csv';
$route['commande_whatsapp/export_csv'] = 'Commande_whatsapp/export_csv';

// ============================================
// ROUTES PRODUITS
// ============================================
$route['Produits'] = 'Products/index';
$route['produits'] = 'Products/index';

// ============================================
// ROUTES DASHBOARD
// ============================================
$route['dashboard'] = 'Dashboard/index';
$route['Dashboard'] = 'Dashboard/index';
$route['dashboard/admin'] = 'Dashboard/admin_dashboard';
$route['dashboard/medecin'] = 'Dashboard/medecin_dashboard';
$route['dashboard/moderator'] = 'Dashboard/moderator_dashboard';
$route['dashboard/investisseur'] = 'Dashboard/investisseur_dashboard';
$route['dashboard/broker'] = 'Dashboard/broker_dashboard';
$route['dashboard/entreprise'] = 'Dashboard/entreprise_dashboard';
$route['dashboard/patient'] = 'Dashboard/patient_dashboard';
$route['dashboard/user'] = 'Dashboard/user_dashboard';

// ============================================
// ROUTES AUTHENTIFICATION (COMPLÈTES)
// ============================================

// Routes principales
$route['auth'] = 'Auth/index';
$route['auth/index'] = 'Auth/index';
$route['auth/login'] = 'Auth/login';
$route['auth/register'] = 'Auth/register';
$route['auth/logout'] = 'Auth/logout';

// Routes pour mot de passe oublié (vérification d'identité - sans OTP)
$route['auth/verify_identity'] = 'Auth/verify_identity';
$route['auth/reset_password'] = 'Auth/reset_password';

// Routes pour effacer les messages flash (AJAX)
$route['auth/clear_flash'] = 'Auth/clear_flash';
$route['auth/clear_flash_data'] = 'Auth/clear_flash_data';

// Routes pour les tests
$route['auth/test_email'] = 'Auth/test_email';

// ============================================
// ALIAS AVEC MAJUSCULE (Compatibilité)
// ============================================
$route['Auth'] = 'Auth/index';
$route['Auth/index'] = 'Auth/index';
$route['Auth/login'] = 'Auth/login';
$route['Auth/register'] = 'Auth/register';
$route['Auth/logout'] = 'Auth/logout';
$route['Auth/verify_identity'] = 'Auth/verify_identity';
$route['Auth/reset_password'] = 'Auth/reset_password';
$route['Auth/clear_flash'] = 'Auth/clear_flash';

// ============================================
// ALIAS SIMPLES (Sans préfixe auth)
// ============================================
$route['connexion'] = 'Auth/index';
$route['inscription'] = 'Auth/index';
$route['login'] = 'Auth/index';
$route['register'] = 'Auth/index';
$route['logout'] = 'Auth/logout';
$route['mot-de-passe-oublie'] = 'Auth/index';
$route['forgot-password'] = 'Auth/index';

// ============================================
// ROUTES PRODUITS & BOUTIQUE
// ============================================
$route['products'] = 'Products/index';
$route['products/get_products_ajax'] = 'Products/get_products_ajax';
$route['products/save_order_request'] = 'Products/save_order_request';
$route['products/increment_price_request'] = 'Products/increment_price_request';
$route['product/(:any)'] = 'Products/detail/$1';
$route['products/(:any)'] = 'Products/detail/$1';
$route['buyers/catalogue'] = 'Products/index';

// ROUTES BOUTIQUE & PANIER (redirigées vers le contrôleur Products fonctionnel)
$route['boutique'] = 'Products/index';
$route['boutique/categorie/(:num)'] = 'Products/index';
$route['boutique/categorie/(:num)/(:num)'] = 'Products/index';
$route['boutique/detail/(:any)'] = 'Products/detail/$1';
$route['boutique/recherche'] = 'Products/index';
$route['boutique/ajax_get_products'] = 'Products/get_products_ajax';

// ============================================
// ROUTES MÉDECINS & CONSULTATIONS
// ============================================
$route['doctor'] = 'Consultations/PatientForm/medicin';
$route['doctor/(:any)'] = 'Consultations/PatientForm/medicin';
$route['patient-form'] = 'Consultations/PatientForm';
$route['patient-form/index'] = 'Consultations/PatientForm/index';
$route['patient-form/create'] = 'Consultations/PatientForm/create';
$route['patient-form/whatsapp'] = 'Consultations/PatientForm/whatsapp';
$route['swap-medecin'] = 'Consultations/PatientForm/changeDoctor';
$route['Swap-medecin'] = 'Consultations/PatientForm/changeDoctor';
$route['consultations/get_countries'] = 'Consultations/PatientForm/get_countries';
$route['home-patient'] = 'Dashboard/PatientDashboard/index';
$route['update-profile'] = 'Dashboard/PatientDashboard/update_home';
$route['patient-fallowed'] = 'Consultations/Entente/confirme';
$route['dashboard/patientdashboard/update_profile'] = 'Dashboard/PatientDashboard/update_profile';

// ============================================
// ROUTES CONFIGURATIONS
// ============================================
$route['Configurations'] = 'Configurations/index';
$route['configurations'] = 'Configurations/index';
$route['Configurations/index'] = 'Configurations/index';
$route['configurations/index'] = 'Configurations/index';
$route['Configurations/update'] = 'Configurations/update';
$route['configurations/update'] = 'Configurations/update';
$route['Configurations/upload_image'] = 'Configurations/upload_image';
$route['configurations/upload_image'] = 'Configurations/upload_image';
$route['Configurations/create'] = 'Configurations/create';
$route['configurations/create'] = 'Configurations/create';
$route['Configurations/delete'] = 'Configurations/delete';
$route['configurations/delete'] = 'Configurations/delete';
$route['Configurations/test_upload_path'] = 'Configurations/test_upload_path';
$route['configurations/test_upload_path'] = 'Configurations/test_upload_path';








// ============================================
// ROUTES MÉDIAS (VIDEO, AUDIO, AUTRE)
// ============================================
$route['media'] = 'Home/Media/index';
$route['media/temoignages'] = 'Home/Media/temoignages';
$route['media/search'] = 'Home/Media/search';
$route['media/search/(:any)'] = 'Home/Media/search/$1';
$route['media/view/(:any)'] = 'Home/Media/view/$1';
$route['media/category/(:any)'] = 'Home/Media/category/$1';
$route['media/detail/(:any)'] = 'Home/Media/detail/$1';
$route['media/type/(:any)'] = 'Home/Media/type/$1';
$route['media/favorites'] = 'Home/Media/favorites';
$route['media/player/(:any)'] = 'Home/Media/player/$1';
$route['media/downloader'] = 'Home/Media/downloader';
$route['media/downloader/(:any)'] = 'Home/Media/downloader/$1';
$route['media/trending'] = 'Home/Media/trending';
$route['Media/trending'] = 'Home/Media/trending';
$route['media/news'] = 'Home/Media/news';

// ROUTES API MÉDIAS
$route['media/apiTrackView'] = 'Home/Media/apiTrackView';
$route['media/apiToggleLike'] = 'Home/Media/apiToggleLike';
$route['media/apiToggleFavorite'] = 'Home/Media/apiToggleFavorite';
$route['media/apiAddComment'] = 'Home/Media/apiAddComment';
$route['media/apiGetComments/(:any)'] = 'Home/Media/apiGetComments/$1';
$route['media/apiGetMedia/(:any)'] = 'Home/Media/apiGetMedia/$1';
$route['media/apiGetStats'] = 'Home/Media/apiGetStats';
$route['media/apiSearch'] = 'Home/Media/apiSearch';
$route['media/apiGetWaveform/(:num)'] = 'Home/Media/apiGetWaveform/$1';

// Anciennes routes pour compatibilité
$route['media/trackView'] = 'Home/Media/apiTrackView';
$route['media/toggleLike'] = 'Home/Media/apiToggleLike';
$route['media/toggleFavorite'] = 'Home/Media/apiToggleFavorite';
$route['media/addComment'] = 'Home/Media/apiAddComment';
$route['media/getComments/(:any)'] = 'Home/Media/apiGetComments/$1';
$route['media/getMedia/(:any)'] = 'Home/Media/apiGetMedia/$1';
$route['media/getStats'] = 'Home/Media/apiGetStats';
$route['media/searchAjax'] = 'Home/Media/apiSearch';

// Backend Médias (Unifié) - préfixé admin/ pour ne pas écraser le frontend
$route['admin/media'] = 'admin_galerie/Media/index';
$route['admin/media/index'] = 'admin_galerie/Media/index';
$route['admin/media/index/(:any)'] = 'admin_galerie/Media/index/$1';
$route['admin/media/Create'] = 'admin_galerie/Media/Create';
$route['admin/media/Update'] = 'admin_galerie/Media/Update';
$route['admin/media/Delete'] = 'admin_galerie/Media/Delete';
$route['admin/media/ChangeStatus'] = 'admin_galerie/Media/ChangeStatus';
$route['admin/media/toggleField'] = 'admin_galerie/Media/toggleField';
$route['admin/media/initUpload'] = 'admin_galerie/Media/initUpload';
$route['admin/media/uploadChunk'] = 'admin_galerie/Media/uploadChunk';
$route['admin/media/uploadStatus'] = 'admin_galerie/Media/uploadStatus';
$route['admin/media/completeUpload'] = 'admin_galerie/Media/completeUpload';
$route['admin/media/uploadThumbnail'] = 'admin_galerie/Media/uploadThumbnail';
$route['admin/media/checkServerLimits'] = 'admin_galerie/Media/checkServerLimits';
$route['admin/media/stream/(:any)/(:num)'] = 'admin_galerie/Media/stream/$1/$2';
$route['admin/media/getJson/(:num)'] = 'admin_galerie/Media/getJson/$1';
$route['admin/media/searchAjax'] = 'admin_galerie/Media/searchAjax';
$route['cli/jobs/run'] = 'admin_galerie/Media/jobs';


// Anciennes routes vidéo/audio/autre - redirection vers le controller frontend public
$route['video'] = 'Home/Media/type/video';
$route['audio'] = 'Home/Media/type/audio';
$route['autre'] = 'Home/Media/type/autre';








// ============================================
// ROUTES BLOG & ACTUALITÉS
// ============================================
$route['blog'] = 'Home/blog/index';
$route['actualites'] = 'Home/blog/index';
$route['actualite/(:any)'] = 'Home/blog/article/$1';
$route['blog/categorie/(:any)'] = 'Home/blog/categorie/$1';
$route['blog/recherche'] = 'Home/blog/recherche';

$route['Actualites'] = 'Actualites/index';

$route['Home/Abonner'] = 'Home/Home/Abonner';
$route['abonner-newsletter'] = 'Home/Home/Abonner';
$route['newsletter/subscribe'] = 'Home/Home/Abonner';


// ============================================
// ROUTES FAQ & CONTACT
// ============================================
$route['question'] = 'Home/faq';
$route['contact'] = 'Home/contact';

// ============================================
// ROUTES INVESTISSEMENT (Frontend)
// ============================================
$route['About/presentation'] = 'Frontend/About/presentation';
$route['about/presentation'] = 'Frontend/About/presentation';
$route['profile-entreprise'] = 'Frontend/Profile_Entreprise/index';
$route['background-strategic-rationale'] = 'Frontend/Background_Strategic_Rationale/index';
$route['nufotec-phytomed-facility'] = 'Frontend/NUFOTEC_PHYTOMED_INDUSTRIES_Facility/index';
$route['risk-analysis'] = 'Frontend/Risk_Analysis_Mitigation_Strategies/index';
$route['strategic-partnerships'] = 'Frontend/Strategic_Partnerships/index';
$route['broker-commission'] = 'Frontend/Commission_Fee_Payment_to_Brokers/index';
$route['investor-commitment'] = 'Frontend/Our_Investor_Partner_Commitment/index';
$route['investment-projection'] = 'Frontend/Phased_Investment_Projection/index';
$route['market-outlook'] = 'Frontend/Market_Industry_Outlook/index';
$route['digital-growth'] = 'Frontend/Market_Expansion_Platform/index';
$route['vision-mission'] = 'Frontend/Vision_Mission/index';
$route['corporate-structure-governance'] = 'Frontend/Corporate_Structure_Governance/index';
$route['esg_Sustainability'] = 'Frontend/Esg_Sustainability/index';
$route['Research_Innovation'] = 'Frontend/Research_Innovation/index';
$route['manufacturing-facility'] = 'Frontend/Manufacturing_facility/index';

// ============================================
// ROUTES BROKERS
// ============================================
$route['broker'] = 'Frontend/Broker/create';
$route['broker/create'] = 'Frontend/Broker/create';
$route['broker/store'] = 'Frontend/Broker/store';
$route['broker/login'] = 'Frontend/Broker/login';
$route['broker/authenticate'] = 'Frontend/Broker/authenticate';
$route['broker/dashboard'] = 'Frontend/Broker/dashboard';
$route['broker/logout'] = 'Frontend/Broker/logout';
$route['broker/add_investor'] = 'Frontend/Broker/add_investor';
$route['broker/update_investor/(:num)'] = 'Frontend/Broker/update_investor/$1';
$route['broker/delete_investor/(:num)'] = 'Frontend/Broker/delete_investor/$1';
$route['broker/get_investor/(:num)'] = 'Frontend/Broker/get_investor/$1';
$route['broker/set_password_view'] = 'Frontend/Broker/set_password_view';
$route['broker/save_password'] = 'Frontend/Broker/save_password';

// ============================================
// ROUTES INVESTISSEURS
// ============================================
$route['investor'] = 'Frontend/Investors/create';
$route['investors/create'] = 'Frontend/Investors/create';
$route['investors/store'] = 'Frontend/Investors/store';

// ============================================
// ROUTES SOCIAL & CHATBOT
// ============================================
$route['social'] = 'Social/index';
$route['social-create'] = 'Social/Create';
$route['social-update'] = 'Social/Update';
$route['social-delete'] = 'Social/Delete';
$route['social/ChangeStatus'] = 'Social/ChangeStatus';
$route['api/social'] = 'Social/api_get_active';
$route['webhook'] = 'chatbot/Chatbot/webhook';
$route['chatbot/admin'] = 'chatbot/admin/index';

// ============================================
// ROUTES CONFIGURATIONS & UTILISATEURS
// ============================================
$route['info'] = 'Configurations/Settings_medecin/index';
$route['update_info'] = 'Configurations/Settings_medecin/update_info';
$route['change-password'] = 'Configurations/Settings_medecin/change_password';
$route['calendrier'] = 'Users/Medecin_Calendrier/index';
$route['calendriersave'] = 'Users/Medecin_Calendrier/save';
$route['users'] = 'Users/Users/index';
$route['users/Medecins'] = 'Users/Medecins/index';
$route['users-create'] = 'Users/Users/Create';
$route['users-update'] = 'Users/Users/Update';
$route['users-delete'] = 'Users/Users/Delete';
$route['users-change-status'] = 'Users/Users/ChangeStatus';
$route['Users/ChangeStatus'] = 'Users/Users/ChangeStatus';

// ============================================
// ROUTES WORKFLOW CATEGORIES
// ============================================
$route['Workflow_categories'] = 'Produits/Workflow_categories/index';
$route['Workflow_categories/Create'] = 'Produits/Workflow_categories/Create';
$route['Workflow_categories/Delete'] = 'Produits/Workflow_categories/Delete';
$route['Workflow_categories/Update'] = 'Produits/Workflow_categories/Update';

// ============================================
// ROUTES PRODUITS (DOIT ÊTRE AVANT LA ROUTE GÉNÉRIQUE)
// ============================================
$route['Products'] = 'Products/index';
$route['products'] = 'Products/index';
$route['Products/index'] = 'Products/index';
$route['products/index'] = 'Products/index';
$route['Products/detail/(:any)'] = 'Products/detail/$1';
$route['products/detail/(:any)'] = 'Products/detail/$1';
$route['Products/get_products_ajax'] = 'Products/get_products_ajax';
$route['products/get_products_ajax'] = 'Products/get_products_ajax';
$route['Products/save_order_request'] = 'Products/save_order_request';
$route['products/save_order_request'] = 'Products/save_order_request';
$route['Products/increment_price_request'] = 'Products/increment_price_request';
$route['products/increment_price_request'] = 'Products/increment_price_request';
$route['Products/admin_orders'] = 'Products/admin_orders';
$route['products/admin_orders'] = 'Products/admin_orders';
$route['Products/update_order_status'] = 'Products/update_order_status';
$route['products/update_order_status'] = 'Products/update_order_status';
$route['Products/admin_stats'] = 'Products/admin_stats';
$route['products/admin_stats'] = 'Products/admin_stats';
$route['Products/delete_order'] = 'Products/delete_order';
$route['products/delete_order'] = 'Products/delete_order';
$route['Products/export_orders_csv'] = 'Products/export_orders_csv';
$route['products/export_orders_csv'] = 'Products/export_orders_csv';

// ============================================
// ROUTES API
// ============================================
$route['Api/investors/Save'] = 'Investors/Save';
$route['api/investors/Save'] = 'Investors/Save';





// Routes API Mobile React Native
$route['api/mobile/medias'] = 'Api/Mobile/medias';
$route['api/mobile/media/(:any)'] = 'Api/Mobile/media/$1';
$route['api/mobile/categories'] = 'Api/Mobile/categories';
$route['api/mobile/search'] = 'Api/Mobile/search';
$route['api/mobile/popular'] = 'Api/Mobile/popular';
$route['api/mobile/recent'] = 'Api/Mobile/recent';
$route['api/mobile/playlists'] = 'Api/Mobile/playlists';
// application/config/routes.php
$route['api/mobile/record-view'] = 'Api/Mobile/recordViewApi';
$route['api/mobile/settings'] = 'Api/Mobile/settings';






// API Routes pour les produits
$route['api/mobile/products'] = 'Api/Mobile/products';
$route['api/mobile/products/detail/(:any)'] = 'Api/Mobile/detail/$1';
$route['api/mobile/products/categories'] = 'Api/Mobile/categoriepro';
$route['api/mobile/products/featured'] = 'Api/Mobile/featured';
$route['api/mobile/products/save_order'] = 'Api/Mobile/save_order';
$route['api/mobile/products/send_whatsapp'] = 'Api/Mobile/send_whatsapp';
$route['api/mobile/products/increment_price_request'] = 'Api/Mobile/increment_price_request';










// ==============================================
// API ROUTES (Pour appels externes)
// ==============================================
// API Status
$route['api/status'] = 'api/status/index';
$route['api/health'] = 'api/status/health';

// API pour envoyer des messages (authentifié)
$route['api/send'] = 'api/messages/send';
$route['api/send/text'] = 'api/messages/send_text';
$route['api/send/media'] = 'api/messages/send_media';

// API pour la queue
$route['api/queue/stats'] = 'api/queue/stats';
$route['api/queue/clear'] = 'api/queue/clear';

// ==============================================
// ROUTES DE TEST (À désactiver en production)
// ==============================================
$route['test/webhook'] = 'test/webhook/index';
$route['test/connection'] = 'test/connection/index';
$route['test/sync'] = 'test/sync/index';






// ============================================
// RECHERCHE
// ============================================
$route['search/index'] = 'Home/search/index';
$route['search/ajax_search'] = 'Home/search/ajax_search';

// ============================================
// MODULES ADMIN (PAGES DU SIDEBAR BACKEND)
// ============================================
$route['Equipe'] = 'Equipe/index';
$route['Partenaires'] = 'Partenaires/index';
$route['Temoignages'] = 'Temoignages/index';
$route['Appels_action'] = 'Appels_action/index';
$route['Chiffres_cles'] = 'Chiffres_cles/index';
$route['Statistiques_reseaux'] = 'Statistiques_reseaux/index';
$route['Galerie_medias'] = 'admin_galerie/Media/index';
$route['Ressources_telechargeables'] = 'Ressources_telechargeables/index';
$route['Faq'] = 'faq/Faq/index';
$route['faq'] = 'faq/Faq/index';
$route['Licences_certifications'] = 'Licences_certifications/index';
$route['Visionmission'] = 'Visionmission/index';
$route['Consultations'] = 'Consultations/index';
$route['Categories'] = 'Categories/index';
$route['Commandes'] = 'Commandes/index';
$route['Investissement_phases'] = 'Investissement_phases/index';
$route['Investissement_phases/index'] = 'Investissement_phases/index';
$route['Investissement_phases/Create'] = 'Investissement_phases/Create';
$route['Investissement_phases/Update'] = 'Investissement_phases/Update';
$route['Investissement_phases/Delete'] = 'Investissement_phases/Delete';
$route['Investissement_phases/PhaseDetail/(:any)'] = 'Investissement_phases/PhaseDetail/$1';
$route['investissement_phases/create'] = 'Investissement_phases/Create';
$route['investissement_phases/update'] = 'Investissement_phases/Update';
$route['investissement_phases/delete'] = 'Investissement_phases/Delete';
$route['investissement_phases/phasedetail/(:any)'] = 'Investissement_phases/PhaseDetail/$1';
$route['Etapes_projet'] = 'Etapes_projet/index';
$route['Risques_mitigations'] = 'Risques_mitigations/index';
$route['Brokers'] = 'Brokers/index';
$route['Investors'] = 'Investors/index';
$route['Roles'] = 'Roles/index';
$route['Slides'] = 'Slides/index';
$route['Newsletter'] = 'Newsletter/index';
$route['Email_templates'] = 'Email_templates/index';
$route['Publication'] = 'Publication/index';
$route['Mode_payement'] = 'Mode_payement/index';
$route['contact_us/Contact_Us'] = 'contact_us/Contact_Us/index';

// ============================================
// ROUTE GÉNÉRIQUE POUR PAGES STATIQUES (DOIT ÊTRE LA DERNIÈRE)
// ============================================
$route['(:any)'] = 'Home/Home/view/$1';
