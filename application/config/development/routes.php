<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ============================================
// ROUTES AVEC PRÉFIXE LANGUE - VERSION CORRIGÉE
// ============================================

// Route par défaut (quand pas de langue dans l'URL)
$route['default_controller'] = 'home/index';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// ============================================
// ROUTES SANS LANGUE (API, BACKEND, WEBHOOKS)
// ============================================
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
$route['consultations/get_countries'] = 'Consultations/PatientForm/get_countries';
$route['Api/investors/Save'] = 'Investors/Save';

// Backend
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







// Page d'accueil
$route['(fr|en|ar|sw)'] = 'home/index';

// Blog & Actualités
$route['(fr|en|ar|sw)/blog'] = 'blog/index';
$route['(fr|en|ar|sw)/actualites'] = 'home/actualites/index';
$route['(fr|en|ar|sw)/actualite/(:any)'] = 'home/blog/article/$2';
$route['(fr|en|ar|sw)/blog/categorie/(:any)'] = 'home/blog/categorie/$2';
$route['(fr|en|ar|sw)/blog/recherche'] = 'home/blog/recherche';

// FAQ & Contact
$route['(fr|en|ar|sw)/question'] = 'home/faq';
$route['(fr|en|ar|sw)/contact'] = 'home/contact';

// Boutique & Panier
$route['(fr|en|ar|sw)/boutique'] = 'home/boutique/index';
$route['(fr|en|ar|sw)/boutique/categorie/(:num)'] = 'home/boutique/categorie/$2';
$route['(fr|en|ar|sw)/boutique/categorie/(:num)/(:num)'] = 'home/boutique/categorie/$2/$3';
$route['(fr|en|ar|sw)/boutique/detail/(:any)'] = 'home/boutique/detail/$2';
$route['(fr|en|ar|sw)/boutique/recherche'] = 'home/boutique/recherche';
$route['(fr|en|ar|sw)/panier'] = 'home/panier/index';
$route['(fr|en|ar|sw)/commande'] = 'home/commande/index';
$route['(fr|en|ar|sw)/commande/valider'] = 'home/commande/valider';
$route['(fr|en|ar|sw)/commande/confirmation/(:num)'] = 'home/commande/confirmation/$2';
$route['(fr|en|ar|sw)/commande/paiement/(:num)'] = 'home/commande/paiement/$2';

// Products
$route['(fr|en|ar|sw)/products'] = 'products/index';
$route['(fr|en|ar|sw)/buyers/catalogue'] = 'products/index';
$route['(fr|en|ar|sw)/products/(:any)'] = 'products/detail/$2';
$route['(fr|en|ar|sw)/product/(:any)'] = 'products/detail/$2';

// Médecins & Consultations
$route['(fr|en|ar|sw)/medicins'] = 'consultations/patientform/medicin';
$route['(fr|en|ar|sw)/patient-form'] = 'consultations/patientform';
$route['(fr|en|ar|sw)/patient-form/create'] = 'consultations/patientform/create';
$route['(fr|en|ar|sw)/consultation/payment/(:any)'] = 'consultations/payment/index/$2';
$route['(fr|en|ar|sw)/swap-medecin'] = 'consultations/patientform/changedoctor';
$route['(fr|en|ar|sw)/home-patient'] = 'dashboard/patientdashboard/index';
$route['(fr|en|ar|sw)/update-profile'] = 'dashboard/patientdashboard/update_home';
$route['(fr|en|ar|sw)/patient-fallowed'] = 'consultations/entente/confirme';

// Auth (authentification)
$route['(fr|en|ar|sw)/auth'] = 'auth/index';
$route['(fr|en|ar|sw)/auth/login'] = 'auth/login';
$route['(fr|en|ar|sw)/auth/logout'] = 'auth/logout';
$route['(fr|en|ar|sw)/auth/register'] = 'auth/register';
$route['(fr|en|ar|sw)/auth/forgot_password'] = 'auth/forgot_password';
$route['(fr|en|ar|sw)/auth/google'] = 'auth/google';
$route['(fr|en|ar|sw)/auth/facebook'] = 'auth/facebook';

// Media
$route['(fr|en|ar|sw)/media'] = 'home/media/index';
$route['(fr|en|ar|sw)/media/search'] = 'home/media/search';
$route['(fr|en|ar|sw)/media/search/(:any)'] = 'home/media/search/$2';
$route['(fr|en|ar|sw)/media/view/(:any)'] = 'home/media/view/$2';
$route['(fr|en|ar|sw)/media/category/(:any)'] = 'home/media/category/$2';
$route['(fr|en|ar|sw)/media/detail/(:any)'] = 'home/media/detail/$2';
$route['(fr|en|ar|sw)/media/type/(:any)'] = 'home/media/type/$2';
$route['(fr|en|ar|sw)/media/favorites'] = 'home/media/favorites';
$route['(fr|en|ar|sw)/media/player/(:any)'] = 'home/media/player/$2';
$route['(fr|en|ar|sw)/media/downloader'] = 'home/media/downloader';
$route['(fr|en|ar|sw)/media/downloader/(:any)'] = 'home/media/downloader/$2';

// Pages d'investissement (Frontend)
$route['(fr|en|ar|sw)/profile-entreprise'] = 'frontend/profile_entreprise/index';
$route['(fr|en|ar|sw)/background-strategic-rationale'] = 'frontend/background_strategic_rationale/index';
$route['(fr|en|ar|sw)/nufotec-phytomed-facility'] = 'frontend/nufotec_phytomed_industries_facility/index';
$route['(fr|en|ar|sw)/risk-analysis'] = 'frontend/risk_analysis_mitigation_strategies/index';
$route['(fr|en|ar|sw)/strategic-partnerships'] = 'frontend/strategic_partnerships/index';
$route['(fr|en|ar|sw)/broker-commission'] = 'frontend/commission_fee_payment_to_brokers/index';
$route['(fr|en|ar|sw)/investor-commitment'] = 'frontend/our_investor_partner_commitment/index';
$route['(fr|en|ar|sw)/investment-projection'] = 'frontend/phased_investment_projection/index';
$route['(fr|en|ar|sw)/market-outlook'] = 'frontend/market_industry_outlook/index';
$route['(fr|en|ar|sw)/digital-growth'] = 'frontend/market_expansion_platform/index';
$route['(fr|en|ar|sw)/vision-mission'] = 'frontend/vision_mission/index';
$route['(fr|en|ar|sw)/corporate-structure-governance'] = 'frontend/corporate_structure_governance/index';
$route['(fr|en|ar|sw)/esg_sustainability'] = 'frontend/esg_sustainability/index';
$route['(fr|en|ar|sw)/research_innovation'] = 'frontend/research_innovation/index';
$route['(fr|en|ar|sw)/manufacturing-facility'] = 'frontend/manufacturing_facility/index';

// Investisseurs
$route['(fr|en|ar|sw)/investors-form'] = 'investors/index';
$route['(fr|en|ar|sw)/brokers-form'] = 'api/brokers';

// Admin & Dashboard
$route['(fr|en|ar|sw)/admin'] = 'admin/index';
$route['(fr|en|ar|sw)/admin/do_login'] = 'admin/do_login';
$route['(fr|en|ar|sw)/dashboard/patientdashboard/update_profile'] = 'dashboard/patientdashboard/update_profile';

// Advertise product
$route['(fr|en|ar|sw)/advertise-product/view/(:any)'] = 'advertise_product/productdetail/$2';
$route['(fr|en|ar|sw)/products/get_products_ajax'] = 'products/get_products_ajax';
$route['(fr|en|ar|sw)/products/increment_price_request'] = 'products/increment_price_request';
$route['(fr|en|ar|sw)/products/save_order_request'] = 'products/save_order_request';

// ============================================
// ROUTE GÉNÉRIQUE POUR PAGES STATIQUES (DERNIÈRE)
// ============================================
$route['(fr|en|ar|sw)/(:any)'] = 'home/view/$2';