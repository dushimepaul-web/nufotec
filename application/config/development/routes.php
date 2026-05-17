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
$route['switch_lang/(fr|en|sw)'] = 'Home/Home/switch_lang/$1';


$route['Admin'] = 'Admin/index';

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
// ROUTES SECTIONS (Gestion des sections de contenu)
// ============================================
$route['Sections'] = 'Sections/index';
$route['sections'] = 'Sections/index';
$route['Sections/add'] = 'Sections/add';
$route['sections/add'] = 'Sections/add';
$route['Sections/edit/(:num)'] = 'Sections/edit/$1';
$route['sections/edit/(:num)'] = 'Sections/edit/$1';
$route['Sections/delete'] = 'Sections/delete';
$route['sections/delete'] = 'Sections/delete';
$route['Sections/ChangeOrdre'] = 'Sections/ChangeOrdre';
$route['sections/ChangeOrdre'] = 'Sections/ChangeOrdre';
$route['Sections/SectionDetail/(:num)'] = 'Sections/SectionDetail/$1';
$route['sections/SectionDetail/(:num)'] = 'Sections/SectionDetail/$1';
$route['Sections/ToggleStatus'] = 'Sections/ToggleStatus';
$route['sections/ToggleStatus'] = 'Sections/ToggleStatus';
$route['Sections/uploadImage'] = 'Sections/uploadImage';
$route['sections/uploadImage'] = 'Sections/uploadImage';
$route['Sections/browseImages'] = 'Sections/browseImages';
$route['sections/browseImages'] = 'Sections/browseImages';

// ============================================
// ADMIN & AUTHENTIFICATION
// ============================================
$route['admin'] = 'Admin/index';
$route['admin/do_login'] = 'Admin/do_login';

// ============================================
// ROUTES AUTHENTIFICATION (METTRE AVANT LA ROUTE GÉNÉRIQUE)
// ============================================
// ROUTES AUTHENTIFICATION (COMPLÈTES)
// ============================================

// Routes principales
$route['auth'] = 'Auth/index';
$route['auth/index'] = 'Auth/index';
$route['auth/login'] = 'Auth/login';
$route['auth/register'] = 'Auth/register';
$route['auth/logout'] = 'Auth/logout';

// Routes pour mot de passe oublié (OTP)
$route['auth/send_reset_code'] = 'Auth/send_reset_code';
$route['auth/verify_reset_code'] = 'Auth/verify_reset_code';
$route['auth/reset_password'] = 'Auth/reset_password';
$route['auth/resend_otp'] = 'Auth/resend_otp';

// Route pour effacer les messages flash (AJAX)
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
$route['Auth/send_reset_code'] = 'Auth/send_reset_code';
$route['Auth/verify_reset_code'] = 'Auth/verify_reset_code';
$route['Auth/reset_password'] = 'Auth/reset_password';
$route['Auth/resend_otp'] = 'Auth/resend_otp';
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




// Routes pour vérification email
$route['auth/verify_email_page'] = 'Auth/verify_email_page';
$route['auth/verify_email_code'] = 'Auth/verify_email_code';
$route['auth/resend_verification_code'] = 'Auth/resend_verification_code';



// ============================================
// ROUTES AUTHENTIFICATION (À AJOUTER AVANT LA ROUTE GÉNÉRIQUE)
// ============================================
$route['Auth'] = 'Auth/index';
$route['Auth/index'] = 'Auth/index';
$route['Auth/login'] = 'Auth/login';
$route['Auth/register'] = 'Auth/register';
$route['Auth/logout'] = 'Auth/logout';
$route['Auth/send_reset_code'] = 'Auth/send_reset_code';
$route['Auth/reset_password'] = 'Auth/reset_password';

// Pour la vue de connexion/inscription (si vous avez une vue spécifique)
$route['connexion'] = 'Auth/index';
$route['inscription'] = 'Auth/index';
$route['login'] = 'Auth/index';
$route['register'] = 'Auth/index';


// ============================================
// ROUTES PRODUITS
// ============================================
$route['products'] = 'Products/index';
$route['products/get_products_ajax'] = 'Products/get_products_ajax';
$route['products/save_order_request'] = 'Products/save_order_request';
$route['products/increment_price_request'] = 'Products/increment_price_request';
$route['product/(:any)'] = 'Products/detail/$1';
$route['products/(:any)'] = 'Products/detail/$1';
$route['buyers/catalogue'] = 'Products/index';

// ============================================
// ROUTES BOUTIQUE & PANIER
// ============================================
$route['boutique'] = 'Home/boutique/index';
$route['boutique/categorie/(:num)'] = 'Home/boutique/categorie/$1';
$route['boutique/categorie/(:num)/(:num)'] = 'Home/boutique/categorie/$1/$2';
$route['boutique/detail/(:any)'] = 'Home/boutique/detail/$1';
$route['boutique/recherche'] = 'Home/boutique/recherche';
$route['boutique/ajax_get_products'] = 'Home/Boutique/ajax_get_products';
$route['panier'] = 'Home/panier/index';
$route['panier/get_cart'] = 'Home/Panier/get_cart';
$route['panier/ajouter'] = 'Home/Panier/ajouter';
$route['panier/update_quantity'] = 'Home/Panier/update_quantity';
$route['panier/delete_line'] = 'Home/Panier/delete_line';
$route['panier/toggle_favori'] = 'Home/Panier/toggle_favori';
$route['commande'] = 'Home/commande/index';
$route['commande/valider'] = 'Home/commande/valider';
$route['commande/confirmation/(:num)'] = 'Home/commande/confirmation/$1';
$route['commande/paiement/(:num)'] = 'Home/commande/paiement/$1';
$route['commande/verifier_paiement/(:num)'] = 'Home/Commande/verifier_paiement/$1';

// ============================================
// ROUTES MÉDECINS & CONSULTATIONS
// ============================================
$route['Medicins'] = 'Consultations/PatientForm/medicin';
$route['patient-form'] = 'Consultations/PatientForm';
$route['patient-form/create'] = 'Consultations/PatientForm/create';
$route['consultation/payment/(:any)'] = 'Consultations/Payment/index/$1';
$route['swap-medecin'] = 'Consultations/PatientForm/changeDoctor';
$route['consultations/get_countries'] = 'Consultations/PatientForm/get_countries';
$route['home-patient'] = 'Dashboard/PatientDashboard/index';
$route['update-profile'] = 'Dashboard/PatientDashboard/update_home';
$route['patient-fallowed'] = 'Consultations/Entente/confirme';
$route['dashboard/patientdashboard/update_profile'] = 'Dashboard/PatientDashboard/update_profile';





// ============================================
// ROUTES CONFIGURATIONS (DOIT ÊTRE AVANT LA ROUTE GÉNÉRIQUE)
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
$route['media'] = 'Home/media/index';
$route['media/search'] = 'Home/media/search';
$route['media/search/(:any)'] = 'Home/media/search/$1';
$route['media/view/(:any)'] = 'Home/media/view/$1';
$route['media/category/(:any)'] = 'Home/media/category/$1';
$route['media/detail/(:any)'] = 'Home/media/detail/$1';
$route['media/type/(:any)'] = 'Home/media/type/$1';
$route['media/favorites'] = 'Home/media/favorites';
$route['media/player/(:any)'] = 'Home/media/player/$1';
$route['media/downloader'] = 'Home/media/downloader';
$route['media/downloader/(:any)'] = 'Home/media/downloader/$1';

// ============================================
// ROUTES API MÉDIAS CORRIGÉES (AVEC PRÉFIXE 'api')
// ============================================
$route['media/apiTrackView'] = 'Home/Media/apiTrackView';
$route['media/apiToggleLike'] = 'Home/Media/apiToggleLike';
$route['media/apiToggleFavorite'] = 'Home/Media/apiToggleFavorite';
$route['media/apiAddComment'] = 'Home/Media/apiAddComment';
$route['media/apiGetComments/(:any)'] = 'Home/Media/apiGetComments/$1';
$route['media/apiGetMedia/(:any)'] = 'Home/Media/apiGetMedia/$1';
$route['media/apiGetStats'] = 'Home/Media/apiGetStats';
$route['media/apiSearch'] = 'Home/Media/apiSearch';
$route['media/apiGetWaveform/(:num)'] = 'Home/Media/apiGetWaveform/$1';

// Garder aussi les anciennes routes pour compatibilité
$route['media/trackView'] = 'Home/Media/apiTrackView';
$route['media/toggleLike'] = 'Home/Media/apiToggleLike';
$route['media/toggleFavorite'] = 'Home/Media/apiToggleFavorite';
$route['media/addComment'] = 'Home/Media/apiAddComment';
$route['media/getComments/(:any)'] = 'Home/Media/apiGetComments/$1';
$route['media/getMedia/(:any)'] = 'Home/Media/apiGetMedia/$1';
$route['media/getStats'] = 'Home/Media/apiGetStats';
$route['media/searchAjax'] = 'Home/Media/apiSearch';

// Backend Médias
$route['video'] = 'media/Video/index';
$route['video/Create'] = 'media/Video/Create';
$route['video/Update'] = 'media/Video/Update';
$route['video/Delete'] = 'media/Video/Delete';
$route['video/ChangeStatus'] = 'media/Video/ChangeStatus';
$route['video/toggleField'] = 'media/Video/toggleField';
$route['video/initUpload'] = 'media/Video/initUpload';
$route['video/uploadChunk'] = 'media/Video/uploadChunk';
$route['video/checkStatus'] = 'media/Video/checkStatus';
$route['video/completeUpload'] = 'media/Video/completeUpload';
$route['video/cancelUpload'] = 'media/Video/cancelUpload';
$route['video/stream/progressive/(:num)'] = 'media/Video/stream/progressive/$1';
$route['video/uploadThumbnail'] = 'media/Video/uploadThumbnail';

$route['audio'] = 'media/Audio/index';
$route['audio/Create'] = 'media/Audio/Create';
$route['audio/Update'] = 'media/Audio/Update';
$route['audio/Delete'] = 'media/Audio/Delete';
$route['audio/ChangeStatus'] = 'media/Audio/ChangeStatus';
$route['audio/toggleField'] = 'media/Audio/toggleField';
$route['audio/initUpload'] = 'media/Audio/initUpload';
$route['audio/uploadChunk'] = 'media/Audio/uploadChunk';
$route['audio/completeUpload'] = 'media/Audio/completeUpload';
$route['audio/uploadThumbnail'] = 'media/Audio/uploadThumbnail';
$route['audio/stream/(:num)'] = 'media/Audio/stream/$1';

$route['autre/admin_liste'] = 'media/Autre/admin_liste';
$route['autre/admin_ajouter'] = 'media/Autre/admin_ajouter';
$route['autre/admin_modifier/(:num)'] = 'media/Autre/admin_modifier/$1';
$route['autre/admin_supprimer/(:num)'] = 'media/Autre/admin_supprimer/$1';
$route['autre/get_json/(:num)'] = 'media/Autre/get_json/$1';

// ============================================
// ROUTES BLOG & ACTUALITÉS
// ============================================
$route['blog'] = 'Home/blog/index';
$route['actualites'] = 'Home/actualites/index';
$route['actualite/(:any)'] = 'Home/blog/article/$1';
$route['blog/categorie/(:any)'] = 'Home/blog/categorie/$1';
$route['blog/recherche'] = 'Home/blog/recherche';

// ============================================
// ROUTES FAQ & CONTACT
// ============================================
$route['question'] = 'Home/faq';
$route['contact'] = 'Home/contact';

// ============================================
// ROUTES INVESTISSEMENT (Frontend)
// ============================================
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




// Investisseurs directs
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
$route['users-create'] = 'Users/Users/Create';
$route['users-update'] = 'Users/Users/Update';
$route['users-delete'] = 'Users/Users/Delete';

// ============================================
// ROUTES WORKFLOW & ADVERTISE PRODUCT
// ============================================
$route['Workflow_categories'] = 'Produits/Workflow_categories/index';
$route['Workflow_categories/Create'] = 'Produits/Workflow_categories/Create';
$route['Workflow_categories/Delete'] = 'Produits/Workflow_categories/Delete';
$route['Workflow_categories/Update'] = 'Produits/Workflow_categories/Update';

$route['advertise-product'] = 'Advertise_product/index';
$route['advertise-product-create'] = 'Advertise_product/create';
$route['advertise-product-update'] = 'Advertise_product/update';
$route['advertise-product-delete'] = 'Advertise_product/delete';
$route['advertise-product-change-status'] = 'Advertise_product/changeStatus';
$route['advertise-product-change-featured'] = 'Advertise_product/changeFeatured';
$route['advertise-product/view/(:any)'] = 'Advertise_product/productDetail/$1';

$route['product_categories'] = 'Advertise_product/Product_categories/index';
$route['product_categories/create'] = 'Advertise_product/Product_categories/create';
$route['product_categories/update'] = 'Advertise_product/Product_categories/update';
$route['product_categories/delete'] = 'Advertise_product/Product_categories/delete';
$route['product_categories/view/(:num)'] = 'Advertise_product/Product_categories/view/$1';



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

// ============================================
// RECHERCHE
// ============================================
$route['search/ajax_search'] = 'Home/search/ajax_search';

// ============================================
// ROUTE GÉNÉRIQUE POUR PAGES STATIQUES (DOIT ÊTRE LA DERNIÈRE)
// ============================================
$route['(:any)'] = 'Home/Home/view/$1';