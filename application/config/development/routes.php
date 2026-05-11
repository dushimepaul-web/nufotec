<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'Home';
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
$route['(fr|en|ar|sw)/actualites'] = 'Home/Actualites/index';
$route['(fr|en|ar|sw)/actualite/(:any)'] = 'Home/Blog/article/$2';
$route['(fr|en|ar|sw)/blog/categorie/(:any)'] = 'Home/Blog/categorie/$2';
$route['(fr|en|ar|sw)/blog/recherche'] = 'Home/Blog/recherche';

// FAQ & Contact
$route['(fr|en|ar|sw)/question'] = 'Home/faq';
$route['(fr|en|ar|sw)/Home/Contact'] = 'Home/Contact';

// Boutique & Panier
$route['(fr|en|ar|sw)/boutique'] = 'Home/Boutique/index';
$route['(fr|en|ar|sw)/boutique/categorie/(:num)'] = 'Home/Boutique/categorie/$2';
$route['(fr|en|ar|sw)/boutique/categorie/(:num)/(:num)'] = 'Home/Boutique/categorie/$2/$3';
$route['(fr|en|ar|sw)/boutique/detail/(:any)'] = 'Home/Boutique/detail/$2';
$route['(fr|en|ar|sw)/boutique/recherche'] = 'Home/Boutique/recherche';
$route['(fr|en|ar|sw)/panier'] = 'Home/Panier/index';
$route['(fr|en|ar|sw)/Commande'] = 'Home/Commande/index';
$route['(fr|en|ar|sw)/commande/valider'] = 'Home/Commande/valider';
$route['(fr|en|ar|sw)/commande/confirmation/(:num)'] = 'Home/Commande/confirmation/$2';
$route['(fr|en|ar|sw)/commande/paiement/(:num)'] = 'Home/Commande/paiement/$2';

// Products
$route['(fr|en|ar|sw)/Products'] = 'Products/index';
$route['(fr|en|ar|sw)/buyers/catalogue'] = 'Products/index';
$route['(fr|en|ar|sw)/Products/(:any)'] = 'Products/detail/$2';
$route['(fr|en|ar|sw)/product/(:any)'] = 'Products/detail/$2';
$route['(fr|en|ar|sw)/Product/(:any)'] = 'Products/detail/$2';

// Medicins & Consultations
$route['(fr|en|ar|sw)/Medicins'] = 'Consultations/PatientForm/Medicin';
$route['(fr|en|ar|sw)/patient-form'] = 'Consultations/PatientForm';
$route['(fr|en|ar|sw)/patient-form/create'] = 'Consultations/PatientForm/create';
$route['(fr|en|ar|sw)/consultation/payment/(:any)'] = 'Consultations/Payment/index/$2';
$route['(fr|en|ar|sw)/Swap-medecin'] = 'Consultations/PatientForm/changeDoctor';
$route['(fr|en|ar|sw)/home-patient'] = 'Dashboard/PatientDashboard/index';
$route['(fr|en|ar|sw)/update-profile'] = 'Dashboard/PatientDashboard/update_home';
$route['(fr|en|ar|sw)/PatientForm'] = 'Consultations/PatientForm';
$route['(fr|en|ar|sw)/patient-fallowed'] = 'Consultations/Entente/confirme';

// Auth
$route['(fr|en|ar|sw)/Auth'] = 'Auth/index';
$route['(fr|en|ar|sw)/Auth/login'] = 'Auth/login';
$route['(fr|en|ar|sw)/Auth/logout'] = 'Auth/logout';
$route['(fr|en|ar|sw)/Auth/register'] = 'Auth/register';
$route['(fr|en|ar|sw)/Auth/forgot_password'] = 'Auth/forgot_password';
$route['(fr|en|ar|sw)/Auth/google'] = 'Auth/google';
$route['(fr|en|ar|sw)/Auth/facebook'] = 'Auth/facebook';

// Media
$route['(fr|en|ar|sw)/media'] = 'Home/Media/index';
$route['(fr|en|ar|sw)/media/search'] = 'Home/Media/search';
$route['(fr|en|ar|sw)/media/search/(:any)'] = 'Home/Media/search/$2';
$route['(fr|en|ar|sw)/media/view/(:any)'] = 'Home/Media/view/$2';
$route['(fr|en|ar|sw)/media/category/(:any)'] = 'Home/Media/category/$2';
$route['(fr|en|ar|sw)/media/detail/(:any)'] = 'Home/Media/detail/$2';
$route['(fr|en|ar|sw)/media/type/(:any)'] = 'Home/Media/type/$2';
$route['(fr|en|ar|sw)/media/favorites'] = 'Home/Media/favorites';
$route['(fr|en|ar|sw)/media/player/(:any)'] = 'Home/Media/player/$2';
$route['(fr|en|ar|sw)/media/downloader'] = 'Home/Media/downloader';
$route['(fr|en|ar|sw)/media/downloader/(:any)'] = 'Home/Media/downloader/$2';

// Pages d'investissement
$route['(fr|en|ar|sw)/Profile-Entreprise'] = 'Frontend/Profile_Entreprise/index';
$route['(fr|en|ar|sw)/background-strategic-rationale'] = 'Frontend/Background_Strategic_Rationale/index';
$route['(fr|en|ar|sw)/nufotec-phytomed-facility'] = 'Frontend/NUFOTEC_PHYTOMED_INDUSTRIES_Facility/index';
$route['(fr|en|ar|sw)/risk-analysis'] = 'Frontend/Risk_Analysis_Mitigation_Strategies/index';
$route['(fr|en|ar|sw)/strategic-partnerships'] = 'Frontend/Strategic_Partnerships/index';
$route['(fr|en|ar|sw)/broker-commission'] = 'Frontend/Commission_Fee_Payment_to_Brokers/index';
$route['(fr|en|ar|sw)/investor-commitment'] = 'Frontend/Our_Investor_Partner_Commitment/index';
$route['(fr|en|ar|sw)/investment-projection'] = 'Frontend/Phased_Investment_Projection/index';
$route['(fr|en|ar|sw)/market-outlook'] = 'Frontend/Market_Industry_Outlook/index';
$route['(fr|en|ar|sw)/digital-growth'] = 'Frontend/Market_Expansion_Platform/index';
$route['(fr|en|ar|sw)/vision-mission'] = 'Frontend/Vision_Mission/index';
$route['(fr|en|ar|sw)/corporate-structure-governance'] = 'Frontend/Corporate_Structure_Governance/index';
$route['(fr|en|ar|sw)/esg_Sustainability'] = 'Frontend/Esg_Sustainability/index';
$route['(fr|en|ar|sw)/Research_Innovation'] = 'Frontend/Research_Innovation/index';
$route['(fr|en|ar|sw)/manufacturing-facility'] = 'Frontend/Manufacturing_Facility/index';

// Investisseurs
$route['(fr|en|ar|sw)/Investors-form'] = 'Investors/index';
$route['(fr|en|ar|sw)/Brokers-form'] = 'Api/Brokers';

// Admin & Dashboard
$route['(fr|en|ar|sw)/Admin'] = 'Admin/index';
$route['(fr|en|ar|sw)/Admin/do_login'] = 'Admin/do_login';
$route['(fr|en|ar|sw)/Dashboard/PatientDashboard/update_profile'] = 'Dashboard/PatientDashboard/update_profile';

// Advertise product
$route['(fr|en|ar|sw)/advertise-product/view/(:any)'] = 'Advertise_product/productDetail/$2';
$route['(fr|en|ar|sw)/products/get_products_ajax'] = 'Products/get_products_ajax';
$route['(fr|en|ar|sw)/products/increment_price_request'] = 'Products/increment_price_request';
$route['(fr|en|ar|sw)/products/save_order_request'] = 'Products/save_order_request';

// ============================================
// ROUTE GÉNÉRIQUE POUR PAGES STATIQUES (À GARDER EN DERNIER)
// ============================================
$route['(fr|en|ar|sw)/(:any)'] = 'home/view/$2';

