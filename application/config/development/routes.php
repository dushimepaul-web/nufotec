<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'home/index';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// ============================================
// ROUTES SANS LANGUE (API, BACKEND, WEBHOOKS)
// ============================================

// API / Ajax
$route['search/ajax_search'] = 'Home/search/ajax_search';
$route['boutique/ajax_get_products'] = 'Home/Boutique/ajax_get_products';
$route['panier/get_cart'] = 'Home/Panier/get_cart';
$route['panier/ajouter'] = 'Home/Panier/ajouter';
$route['panier/update_quantity'] = 'Home/Panier/update_quantity';
$route['panier/delete_line'] = 'Home/Panier/delete_line';
$route['panier/toggle_favori'] = 'Home/Panier/toggle_favori';
$route['commande/verifier_paiement/(:num)'] = 'home/Commande/verifier_paiement/$1';
$route['media/searchAjax'] = 'Home/Media/apiSearch';
$route['media/apiSearch'] = 'Home/Media/apiSearch';
$route['media/trackView'] = 'Home/Media/apiTrackView';
$route['media/trackPlay'] = 'Home/Media/apiTrackPlay';
$route['media/toggleLike'] = 'Home/Media/apiToggleLike';
$route['media/rateMedia'] = 'Home/Media/apiRateMedia';
$route['media/addComment'] = 'Home/Media/apiAddComment';
$route['media/checkUserLike/(:num)'] = 'Home/Media/checkUserLike/$1';
$route['media/getFavorites'] = 'Home/Media/apiGetFavorites';
$route['media/toggleFavorite'] = 'Home/Media/apiToggleFavorite';
$route['media/share'] = 'Home/Media/apiShare';
$route['media/getComments/(:any)'] = 'Home/Media/apiGetComments/$1';
$route['media/getMedia/(:any)'] = 'Home/Media/apiGetMedia/$1';
$route['media/getStats'] = 'Home/Media/apiGetStats';
$route['media/getWaveform/(:num)'] = 'Home/Media/apiGetWaveform/$1';
$route['products/increment_price_request'] = 'Products/increment_price_request';
$route['products/save_order_request'] = 'Products/save_order_request';
$route['api/social'] = 'Social/api_get_active';
$route['webhook'] = 'chatbot/Chatbot/webhook';

// Backend / Admin (sans langue)
$route['Workflow_categories'] = 'Produits/Workflow_categories/index';
$route['Workflow_categories/Create'] = 'Produits/Workflow_categories/Create';
$route['Workflow_categories/Delete'] = 'Produits/Workflow_categories/Delete';
$route['Workflow_categories/Update'] = 'Produits/Workflow_categories/Update';
$route['info'] = 'Configurations/Settings_medecin/index';
$route['update_info'] = 'Configurations/Settings_medecin/update_info';
$route['change-password'] = 'Configurations/Settings_medecin/change_password';
$route['calendrier'] = 'Users/Medecin_Calendrier/index';
$route['calendriersave'] = 'Users/Medecin_Calendrier/save';
$route['users'] = 'Users/Users/index';
$route['users-create'] = 'Users/Users/Create';
$route['users-update'] = 'Users/Users/Update';
$route['users-delete'] = 'Users/Users/Delete';
$route['video'] = 'media/Video/index';
$route['video/index'] = 'media/Video/index';
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
$route['audio/(:any)'] = 'media/Audio/$1';
$route['autre/admin_liste'] = 'media/Autre/admin_liste';
$route['Autre/admin_liste'] = 'media/Autre/admin_liste';
$route['autre/admin_liste/(:num)'] = 'media/Autre/admin_liste/$1';
$route['autre/admin_ajouter'] = 'media/Autre/admin_ajouter';
$route['autre/admin_modifier/(:num)'] = 'media/Autre/admin_modifier/$1';
$route['autre/admin_supprimer/(:num)'] = 'media/Autre/admin_supprimer/$1';
$route['autre/get_json/(:num)'] = 'media/Autre/get_json/$1';
$route['advertise-product'] = 'Advertise_product/index';
$route['advertise-product-create'] = 'Advertise_product/create';
$route['advertise-product-update'] = 'Advertise_product/update';
$route['advertise-product-delete'] = 'Advertise_product/delete';
$route['advertise-product-change-status'] = 'Advertise_product/changeStatus';
$route['advertise-product-change-featured'] = 'Advertise_product/changeFeatured';
$route['product_categories'] = 'Advertise_product/Product_categories/index';
$route['product_categories/index'] = 'Advertise_product/Product_categories/index';
$route['product_categories/create'] = 'Advertise_product/Product_categories/create';
$route['product_categories/update'] = 'Advertise_product/Product_categories/update';
$route['product_categories/delete'] = 'Advertise_product/Product_categories/delete';
$route['product_categories/view/(:num)'] = 'Advertise_product/Product_categories/view/$1';
$route['social'] = 'Social/index';
$route['social-create'] = 'Social/Create';
$route['social-update'] = 'Social/Update';
$route['social-delete'] = 'Social/Delete';
$route['social/ChangeStatus'] = 'Social/ChangeStatus';
$route['chatbot/admin'] = 'chatbot/admin/index';
$route['chatbot/admin/(:any)'] = 'chatbot/admin/$1';




// ============================================
// PREFIXE LANGUE (unique)
// ============================================
$lang_prefix = '(fr|en|ar|sw)';

// ============================================
// 1. ROUTES SPÉCIFIQUES (module Home)
// ============================================
$route[$lang_prefix] = 'home/index';
$route[$lang_prefix . '/Home/(:any)'] = 'home/$2';
$route[$lang_prefix . '/search'] = 'search/index';
$route[$lang_prefix . '/blog'] = 'blog/index';
$route[$lang_prefix . '/actualites'] = 'Home/Actualites/index';
$route[$lang_prefix . '/actualite/(:any)'] = 'Home/Blog/article/$2';
$route[$lang_prefix . '/blog/categorie/(:any)'] = 'Home/Blog/categorie/$2';
$route[$lang_prefix . '/blog/recherche'] = 'Home/Blog/recherche';
$route[$lang_prefix . '/question'] = 'Home/faq';
$route[$lang_prefix . '/Home/Contact'] = 'Home/Contact';  // si besoin

// ============================================
// 2. ROUTES SPÉCIFIQUES (module Boutique / Commande) 
// ============================================
$route[$lang_prefix . '/boutique'] = 'Home/Boutique/index';
$route[$lang_prefix . '/boutique/categorie/(:num)'] = 'Home/Boutique/categorie/$2';
$route[$lang_prefix . '/boutique/categorie/(:num)/(:num)'] = 'Home/Boutique/categorie/$2/$3';
$route[$lang_prefix . '/boutique/detail/(:any)'] = 'Home/Boutique/detail/$2';
$route[$lang_prefix . '/boutique/recherche'] = 'Home/Boutique/recherche';
$route[$lang_prefix . '/panier'] = 'Home/Panier/index';
$route[$lang_prefix . '/Commande'] = 'Home/Commande/index';
$route[$lang_prefix . '/commande/valider'] = 'Home/Commande/valider';
$route[$lang_prefix . '/commande/confirmation/(:num)'] = 'Home/Commande/confirmation/$2';
$route[$lang_prefix . '/commande/paiement/(:num)'] = 'Home/Commande/paiement/$2';

// ============================================
// 3. ROUTES SPÉCIFIQUES (module Products, Medicins, Auth, etc.)
// ============================================
$route[$lang_prefix . '/Products'] = 'Products/index';
$route[$lang_prefix . '/buyers/catalogue'] = 'Products/index';
$route[$lang_prefix . '/Products/(:any)'] = 'Products/detail/$2';
$route[$lang_prefix . '/product/(:any)'] = 'Products/detail/$2';
$route[$lang_prefix . '/Product/(:any)'] = 'Products/detail/$2';


$route[$lang_prefix . '/Medicins'] = 'Consultations/PatientForm/Medicin';
 
$route[$lang_prefix . '/Auth'] = 'Auth/index';
$route[$lang_prefix . '/Auth/login'] = 'Auth/login';
$route[$lang_prefix . '/Auth/logout'] = 'Auth/logout';
$route[$lang_prefix . '/Auth/register'] = 'Auth/register';
$route[$lang_prefix . '/Auth/forgot_password'] = 'Auth/forgot_password';
$route[$lang_prefix . '/Auth/google'] = 'Auth/google';
$route[$lang_prefix . '/Auth/facebook'] = 'Auth/facebook';
$route[$lang_prefix . '/advertise-product/view/(:any)'] = 'Advertise_product/productDetail/$2';
$route[$lang_prefix . '/products/get_products_ajax'] = 'Products/get_products_ajax';

$route[$lang_prefix . '/products/increment_price_request'] = 'Products/increment_price_request';
$route[$lang_prefix . '/products/save_order_request'] = 'Products/save_order_request';

// ============================================
// 4. ROUTES SPÉCIFIQUES (module Consultations, Dashboard, Api)

// ============================================
// ROUTES POUR PATIENTFORM (AJOUTS NÉCESSAIRES)
// ============================================

// Soumission du formulaire (POST)
$route[$lang_prefix . '/patient-form/create'] = 'Consultations/PatientForm/create';

// Paiement d'une consultation (après création)
$route[$lang_prefix . '/consultation/payment/(:any)'] = 'Consultations/Payment/index/$2';

// API AJAX (sans préfixe langue – recommandé)
$route['consultations/get_countries'] = 'Consultations/PatientForm/get_countries';
// ============================================
$route[$lang_prefix . '/Swap-medecin'] = 'Consultations/PatientForm/changeDoctor';
$route[$lang_prefix . '/patient-form'] = 'Consultations/PatientForm';
$route[$lang_prefix . '/home-patient'] = 'Dashboard/PatientDashboard/index';
$route[$lang_prefix . '/update-profile'] = 'Dashboard/PatientDashboard/update_home';
$route[$lang_prefix . '/PatientForm'] = 'Consultations/PatientForm';
$route[$lang_prefix . '/patient-fallowed'] = 'Consultations/Entente/confirme/';
$route[$lang_prefix . '/Investors-form'] = 'Api/Investors'; 
$route[$lang_prefix . '/Api/Brokers'] = 'Api/Brokers';  
$route[$lang_prefix . '/Brokers-form'] = 'Api/Brokers';


// API Investors
$route['Api/investors/Save'] = 'Investors/Save';

// Routes avec préfixe langue
$route[$lang_prefix . '/Investors-form'] = 'Investors/index';

$route[$lang_prefix . '/Consultations/Payment/index/(:any)'] = 'Consultations/Payment/index/$2';

// ============================================
// 5. ROUTES SPÉCIFIQUES (module Media)
// ============================================
$route[$lang_prefix . '/media'] = 'Home/Media/index';
$route[$lang_prefix . '/media/search'] = 'Home/Media/search';
$route[$lang_prefix . '/media/search/(:any)'] = 'Home/Media/search/$2';
$route[$lang_prefix . '/media/view/(:any)'] = 'Home/Media/view/$2';
$route[$lang_prefix . '/media/category/(:any)'] = 'Home/Media/category/$2';
$route[$lang_prefix . '/media/detail/(:any)'] = 'Home/Media/detail/$2';
$route[$lang_prefix . '/media/type/(:any)'] = 'Home/Media/type/$2';
$route[$lang_prefix . '/media/favorites'] = 'Home/Media/favorites';
$route[$lang_prefix . '/media/player/(:any)'] = 'Home/Media/player/$2';
$route[$lang_prefix . '/media/downloader'] = 'Home/Media/downloader';
$route[$lang_prefix . '/media/downloader/(:any)'] = 'Home/Media/downloader/$2';

$route[$lang_prefix . '/media/apiSearch'] = 'Home/Media/apiSearch';
$route[$lang_prefix . '/media/apiTrackView'] = 'Home/Media/apiTrackView';
$route[$lang_prefix . '/media/apiToggleLike'] = 'Home/Media/apiToggleLike';
$route[$lang_prefix . '/media/apiToggleFavorite'] = 'Home/Media/apiToggleFavorite';
$route[$lang_prefix . '/media/apiAddComment'] = 'Home/Media/apiAddComment';
$route[$lang_prefix . '/media/apiGetComments/(:any)'] = 'Home/Media/apiGetComments/$2';
// ... le reste

// ============================================
// 6. ROUTES SPÉCIFIQUES (module Frontend – vos pages dynamiques)
// ============================================
$route[$lang_prefix . '/Profile-Entreprise'] = 'Frontend/Profile_Entreprise/index/$1';
$route[$lang_prefix . '/background-strategic-rationale'] = 'Frontend/Background_Strategic_Rationale/index/$1';
$route[$lang_prefix . '/nufotec-phytomed-facility'] = 'Frontend/NUFOTEC_PHYTOMED_INDUSTRIES_Facility/index/$1';
$route[$lang_prefix . '/risk-analysis'] = 'Frontend/Risk_Analysis_Mitigation_Strategies/index/$1';
$route[$lang_prefix . '/strategic-partnerships'] = 'Frontend/Strategic_Partnerships/index/$1';
$route[$lang_prefix . '/broker-commission'] = 'Frontend/Commission_Fee_Payment_to_Brokers/index/$1';
$route[$lang_prefix . '/investor-commitment'] = 'Frontend/Our_Investor_Partner_Commitment/index/$1';
$route[$lang_prefix . '/investment-projection'] = 'Frontend/Phased_Investment_Projection/index/$1';
$route[$lang_prefix . '/market-outlook'] = 'Frontend/Market_Industry_Outlook/index/$1';
$route[$lang_prefix . '/digital-growth'] = 'Frontend/Market_Expansion_Platform/index/$1';
$route[$lang_prefix . '/vision-mission'] = 'Frontend/Vision_Mission/index/$1';
$route[$lang_prefix . '/corporate-structure-governance'] = 'Frontend/Corporate_Structure_Governance/index/$1';
$route[$lang_prefix . '/esg_Sustainability'] = 'Frontend/Esg_Sustainability/index/$1';   
$route[$lang_prefix . '/Research_Innovation'] = 'Frontend/Research_Innovation/index/$1';  
$route[$lang_prefix . '/manufacturing-facility'] = 'Frontend/Manufacturing_Facility/index/$1';


$route[$lang_prefix . '/Admin'] = 'Admin/index';   
$route[$lang_prefix . '/Admin/do_login'] = 'Admin/do_login';
// ============================================
// 7. ROUTE GÉNÉRIQUE (pour les pages statiques de la table 'pages')
//    ⚠️ Elle ne doit capturer que ce qui n'a pas matché au-dessus
// ============================================
$route[$lang_prefix . '/(:any)'] = 'home/view/$2';


$route[$lang_prefix . '/Dashboard/PatientDashboard/update_profile'] = 'Dashboard/PatientDashboard/update_profile';
