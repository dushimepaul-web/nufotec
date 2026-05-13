<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ============================================
// ROUTES SANS PRÉFIXE LANGUE
// ============================================

// Route par défaut
$route['default_controller'] = 'Home/Home/index';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// ============================================
// CHANGEMENT DE LANGUE
// ============================================
$route['switch_lang/(fr|en|sw)'] = 'Home/Home/switch_lang/$1';

// ============================================
// ROUTES POUR LES PRODUITS (AJAX et PUBLIC)
// DOIVENT ÊTRE AVANT LA ROUTE GÉNÉRIQUE
// ============================================

// Routes AJAX
$route['products/get_products_ajax'] = 'Products/get_products_ajax';
$route['Products/get_products_ajax'] = 'Products/get_products_ajax';
$route['products/save_order_request'] = 'Products/save_order_request';
$route['Products/save_order_request'] = 'Products/save_order_request';
$route['products/increment_price_request'] = 'Products/increment_price_request';
$route['Products/increment_price_request'] = 'Products/increment_price_request';

// Routes publiques des produits
$route['products'] = 'Products/index';
$route['Products'] = 'Products/index';
$route['buyers/catalogue'] = 'Products/index';
$route['Buyers/catalogue'] = 'Products/index';
$route['product/(:any)'] = 'Products/detail/$1';
$route['Product/(:any)'] = 'Products/detail/$1';
$route['products/(:any)'] = 'Products/detail/$1';
$route['Products/(:any)'] = 'Products/detail/$1';

// ============================================
// ROUTES API, AJAX, BACKEND (sans langue)
// ============================================

// Search & Boutique
$route['search/ajax_search'] = 'Home/search/ajax_search';
$route['Search/ajax_search'] = 'Home/search/ajax_search';
$route['boutique/ajax_get_products'] = 'Home/Boutique/ajax_get_products';
$route['Boutique/ajax_get_products'] = 'Home/Boutique/ajax_get_products';
$route['panier/get_cart'] = 'Home/Panier/get_cart';
$route['Panier/get_cart'] = 'Home/Panier/get_cart';
$route['panier/ajouter'] = 'Home/Panier/ajouter';
$route['Panier/ajouter'] = 'Home/Panier/ajouter';
$route['panier/update_quantity'] = 'Home/Panier/update_quantity';
$route['Panier/update_quantity'] = 'Home/Panier/update_quantity';
$route['panier/delete_line'] = 'Home/Panier/delete_line';
$route['Panier/delete_line'] = 'Home/Panier/delete_line';
$route['panier/toggle_favori'] = 'Home/Panier/toggle_favori';
$route['Panier/toggle_favori'] = 'Home/Panier/toggle_favori';
$route['commande/verifier_paiement/(:num)'] = 'Home/Commande/verifier_paiement/$1';
$route['Commande/verifier_paiement/(:num)'] = 'Home/Commande/verifier_paiement/$1';

// Media API
$route['media/searchAjax'] = 'Home/Media/apiSearch';
$route['Media/searchAjax'] = 'Home/Media/apiSearch';
$route['media/apiSearch'] = 'Home/Media/apiSearch';
$route['Media/apiSearch'] = 'Home/Media/apiSearch';
$route['media/trackView'] = 'Home/Media/apiTrackView';
$route['Media/trackView'] = 'Home/Media/apiTrackView';
$route['media/trackPlay'] = 'Home/Media/apiTrackPlay';
$route['Media/trackPlay'] = 'Home/Media/apiTrackPlay';
$route['media/toggleLike'] = 'Home/Media/apiToggleLike';
$route['Media/toggleLike'] = 'Home/Media/apiToggleLike';
$route['media/rateMedia'] = 'Home/Media/apiRateMedia';
$route['Media/rateMedia'] = 'Home/Media/apiRateMedia';
$route['media/addComment'] = 'Home/Media/apiAddComment';
$route['Media/addComment'] = 'Home/Media/apiAddComment';
$route['media/checkUserLike/(:num)'] = 'Home/Media/checkUserLike/$1';
$route['Media/checkUserLike/(:num)'] = 'Home/Media/checkUserLike/$1';
$route['media/getFavorites'] = 'Home/Media/apiGetFavorites';
$route['Media/getFavorites'] = 'Home/Media/apiGetFavorites';
$route['media/toggleFavorite'] = 'Home/Media/apiToggleFavorite';
$route['Media/toggleFavorite'] = 'Home/Media/apiToggleFavorite';
$route['media/share'] = 'Home/Media/apiShare';
$route['Media/share'] = 'Home/Media/apiShare';
$route['media/getComments/(:any)'] = 'Home/Media/apiGetComments/$1';
$route['Media/getComments/(:any)'] = 'Home/Media/apiGetComments/$1';
$route['media/getMedia/(:any)'] = 'Home/Media/apiGetMedia/$1';
$route['Media/getMedia/(:any)'] = 'Home/Media/apiGetMedia/$1';
$route['media/getStats'] = 'Home/Media/apiGetStats';
$route['Media/getStats'] = 'Home/Media/apiGetStats';
$route['media/getWaveform/(:num)'] = 'Home/Media/apiGetWaveform/$1';
$route['Media/getWaveform/(:num)'] = 'Home/Media/apiGetWaveform/$1';

// API & Webhook
$route['api/social'] = 'Social/api_get_active';
$route['Api/social'] = 'Social/api_get_active';
$route['webhook'] = 'chatbot/Chatbot/webhook';
$route['Webhook'] = 'chatbot/Chatbot/webhook';
$route['consultations/get_countries'] = 'Consultations/PatientForm/get_countries';
$route['Consultations/get_countries'] = 'Consultations/PatientForm/get_countries';
$route['Api/investors/Save'] = 'Investors/Save';
$route['api/investors/Save'] = 'Investors/Save';

// ============================================
// BACKEND ROUTES
// ============================================
$route['Workflow_categories'] = 'Produits/Workflow_categories/index';
$route['workflow_categories'] = 'Produits/Workflow_categories/index';
$route['Workflow_categories/Create'] = 'Produits/Workflow_categories/Create';
$route['workflow_categories/create'] = 'Produits/Workflow_categories/Create';
$route['Workflow_categories/Delete'] = 'Produits/Workflow_categories/Delete';
$route['workflow_categories/delete'] = 'Produits/Workflow_categories/Delete';
$route['Workflow_categories/Update'] = 'Produits/Workflow_categories/Update';
$route['workflow_categories/update'] = 'Produits/Workflow_categories/Update';
$route['info'] = 'Configurations/Settings_medecin/index';
$route['Info'] = 'Configurations/Settings_medecin/index';
$route['update_info'] = 'Configurations/Settings_medecin/update_info';
$route['Update_info'] = 'Configurations/Settings_medecin/update_info';
$route['change-password'] = 'Configurations/Settings_medecin/change_password';
$route['Change-password'] = 'Configurations/Settings_medecin/change_password';
$route['calendrier'] = 'Users/Medecin_Calendrier/index';
$route['Calendrier'] = 'Users/Medecin_Calendrier/index';
$route['calendriersave'] = 'Users/Medecin_Calendrier/save';
$route['Calendriersave'] = 'Users/Medecin_Calendrier/save';
$route['users'] = 'Users/Users/index';
$route['Users'] = 'Users/Users/index';
$route['users-create'] = 'Users/Users/Create';
$route['Users-create'] = 'Users/Users/Create';
$route['users-update'] = 'Users/Users/Update';
$route['Users-update'] = 'Users/Users/Update';
$route['users-delete'] = 'Users/Users/Delete';
$route['Users-delete'] = 'Users/Users/Delete';

// Video
$route['video'] = 'media/Video/index';
$route['Video'] = 'media/Video/index';
$route['video/index'] = 'media/Video/index';
$route['Video/index'] = 'media/Video/index';
$route['video/Create'] = 'media/Video/Create';
$route['Video/Create'] = 'media/Video/Create';
$route['video/Update'] = 'media/Video/Update';
$route['Video/Update'] = 'media/Video/Update';
$route['video/Delete'] = 'media/Video/Delete';
$route['Video/Delete'] = 'media/Video/Delete';
$route['video/ChangeStatus'] = 'media/Video/ChangeStatus';
$route['Video/ChangeStatus'] = 'media/Video/ChangeStatus';
$route['video/toggleField'] = 'media/Video/toggleField';
$route['Video/toggleField'] = 'media/Video/toggleField';
$route['video/initUpload'] = 'media/Video/initUpload';
$route['Video/initUpload'] = 'media/Video/initUpload';
$route['video/uploadChunk'] = 'media/Video/uploadChunk';
$route['Video/uploadChunk'] = 'media/Video/uploadChunk';
$route['video/checkStatus'] = 'media/Video/checkStatus';
$route['Video/checkStatus'] = 'media/Video/checkStatus';
$route['video/completeUpload'] = 'media/Video/completeUpload';
$route['Video/completeUpload'] = 'media/Video/completeUpload';
$route['video/cancelUpload'] = 'media/Video/cancelUpload';
$route['Video/cancelUpload'] = 'media/Video/cancelUpload';
$route['video/stream/progressive/(:num)'] = 'media/Video/stream/progressive/$1';
$route['Video/stream/progressive/(:num)'] = 'media/Video/stream/progressive/$1';
$route['video/uploadThumbnail'] = 'media/Video/uploadThumbnail';
$route['Video/uploadThumbnail'] = 'media/Video/uploadThumbnail';

// Audio
$route['audio'] = 'media/Audio/index';
$route['Audio'] = 'media/Audio/index';
$route['audio/Create'] = 'media/Audio/Create';
$route['Audio/Create'] = 'media/Audio/Create';
$route['audio/Update'] = 'media/Audio/Update';
$route['Audio/Update'] = 'media/Audio/Update';
$route['audio/Delete'] = 'media/Audio/Delete';
$route['Audio/Delete'] = 'media/Audio/Delete';
$route['audio/ChangeStatus'] = 'media/Audio/ChangeStatus';
$route['Audio/ChangeStatus'] = 'media/Audio/ChangeStatus';
$route['audio/toggleField'] = 'media/Audio/toggleField';
$route['Audio/toggleField'] = 'media/Audio/toggleField';
$route['audio/initUpload'] = 'media/Audio/initUpload';
$route['Audio/initUpload'] = 'media/Audio/initUpload';
$route['audio/uploadChunk'] = 'media/Audio/uploadChunk';
$route['Audio/uploadChunk'] = 'media/Audio/uploadChunk';
$route['audio/completeUpload'] = 'media/Audio/completeUpload';
$route['Audio/completeUpload'] = 'media/Audio/completeUpload';
$route['audio/uploadThumbnail'] = 'media/Audio/uploadThumbnail';
$route['Audio/uploadThumbnail'] = 'media/Audio/uploadThumbnail';
$route['audio/stream/(:num)'] = 'media/Audio/stream/$1';
$route['Audio/stream/(:num)'] = 'media/Audio/stream/$1';
$route['audio/(:any)'] = 'media/Audio/$1';
$route['Audio/(:any)'] = 'media/Audio/$1';

// Autre media
$route['autre/admin_liste'] = 'media/Autre/admin_liste';
$route['Autre/admin_liste'] = 'media/Autre/admin_liste';
$route['autre/admin_liste/(:num)'] = 'media/Autre/admin_liste/$1';
$route['Autre/admin_liste/(:num)'] = 'media/Autre/admin_liste/$1';
$route['autre/admin_ajouter'] = 'media/Autre/admin_ajouter';
$route['Autre/admin_ajouter'] = 'media/Autre/admin_ajouter';
$route['autre/admin_modifier/(:num)'] = 'media/Autre/admin_modifier/$1';
$route['Autre/admin_modifier/(:num)'] = 'media/Autre/admin_modifier/$1';
$route['autre/admin_supprimer/(:num)'] = 'media/Autre/admin_supprimer/$1';
$route['Autre/admin_supprimer/(:num)'] = 'media/Autre/admin_supprimer/$1';
$route['autre/get_json/(:num)'] = 'media/Autre/get_json/$1';
$route['Autre/get_json/(:num)'] = 'media/Autre/get_json/$1';

// Advertise product
$route['advertise-product'] = 'Advertise_product/index';
$route['Advertise-product'] = 'Advertise_product/index';
$route['advertise-product-create'] = 'Advertise_product/create';
$route['Advertise-product-create'] = 'Advertise_product/create';
$route['advertise-product-update'] = 'Advertise_product/update';
$route['Advertise-product-update'] = 'Advertise_product/update';
$route['advertise-product-delete'] = 'Advertise_product/delete';
$route['Advertise-product-delete'] = 'Advertise_product/delete';
$route['advertise-product-change-status'] = 'Advertise_product/changeStatus';
$route['Advertise-product-change-status'] = 'Advertise_product/changeStatus';
$route['advertise-product-change-featured'] = 'Advertise_product/changeFeatured';
$route['Advertise-product-change-featured'] = 'Advertise_product/changeFeatured';
$route['product_categories'] = 'Advertise_product/Product_categories/index';
$route['Product_categories'] = 'Advertise_product/Product_categories/index';
$route['product_categories/index'] = 'Advertise_product/Product_categories/index';
$route['Product_categories/index'] = 'Advertise_product/Product_categories/index';
$route['product_categories/create'] = 'Advertise_product/Product_categories/create';
$route['Product_categories/create'] = 'Advertise_product/Product_categories/create';
$route['product_categories/update'] = 'Advertise_product/Product_categories/update';
$route['Product_categories/update'] = 'Advertise_product/Product_categories/update';
$route['product_categories/delete'] = 'Advertise_product/Product_categories/delete';
$route['Product_categories/delete'] = 'Advertise_product/Product_categories/delete';
$route['product_categories/view/(:num)'] = 'Advertise_product/Product_categories/view/$1';
$route['Product_categories/view/(:num)'] = 'Advertise_product/Product_categories/view/$1';

// Social
$route['social'] = 'Social/index';
$route['Social'] = 'Social/index';
$route['social-create'] = 'Social/Create';
$route['Social-create'] = 'Social/Create';
$route['social-update'] = 'Social/Update';
$route['Social-update'] = 'Social/Update';
$route['social-delete'] = 'Social/Delete';
$route['Social-delete'] = 'Social/Delete';
$route['social/ChangeStatus'] = 'Social/ChangeStatus';
$route['Social/ChangeStatus'] = 'Social/ChangeStatus';

// Chatbot
$route['chatbot/admin'] = 'chatbot/admin/index';
$route['Chatbot/admin'] = 'chatbot/admin/index';
$route['chatbot/admin/(:any)'] = 'chatbot/admin/$1';
$route['Chatbot/admin/(:any)'] = 'chatbot/admin/$1';

// ============================================
// ROUTES PUBLIQUES (sans préfixe langue)
// ============================================

// Blog & Actualités
$route['blog'] = 'Home/blog/index';
$route['Blog'] = 'Home/blog/index';
$route['actualites'] = 'Home/actualites/index';
$route['Actualites'] = 'Home/actualites/index';
$route['actualite/(:any)'] = 'Home/blog/article/$1';
$route['Actualite/(:any)'] = 'Home/blog/article/$1';
$route['blog/categorie/(:any)'] = 'Home/blog/categorie/$1';
$route['Blog/categorie/(:any)'] = 'Home/blog/categorie/$1';
$route['blog/recherche'] = 'Home/blog/recherche';
$route['Blog/recherche'] = 'Home/blog/recherche';

// FAQ & Contact
$route['question'] = 'Home/faq';
$route['Question'] = 'Home/faq';
$route['contact'] = 'Home/contact';
$route['Contact'] = 'Home/contact';

// Boutique & Panier
$route['boutique'] = 'Home/boutique/index';
$route['Boutique'] = 'Home/boutique/index';
$route['boutique/categorie/(:num)'] = 'Home/boutique/categorie/$1';
$route['Boutique/categorie/(:num)'] = 'Home/boutique/categorie/$1';
$route['boutique/categorie/(:num)/(:num)'] = 'Home/boutique/categorie/$1/$2';
$route['Boutique/categorie/(:num)/(:num)'] = 'Home/boutique/categorie/$1/$2';
$route['boutique/detail/(:any)'] = 'Home/boutique/detail/$1';
$route['Boutique/detail/(:any)'] = 'Home/boutique/detail/$1';
$route['boutique/recherche'] = 'Home/boutique/recherche';
$route['Boutique/recherche'] = 'Home/boutique/recherche';
$route['panier'] = 'Home/panier/index';
$route['Panier'] = 'Home/panier/index';
$route['commande'] = 'Home/commande/index';
$route['Commande'] = 'Home/commande/index';
$route['commande/valider'] = 'Home/commande/valider';
$route['Commande/valider'] = 'Home/commande/valider';
$route['commande/confirmation/(:num)'] = 'Home/commande/confirmation/$1';
$route['Commande/confirmation/(:num)'] = 'Home/commande/confirmation/$1';
$route['commande/paiement/(:num)'] = 'Home/commande/paiement/$1';
$route['Commande/paiement/(:num)'] = 'Home/commande/paiement/$1';

// Médecins & Consultations
$route['medicins'] = 'Consultations/PatientForm/medicin';
$route['Medicins'] = 'Consultations/PatientForm/medicin';
$route['patient-form'] = 'Consultations/PatientForm';
$route['Patient-form'] = 'Consultations/PatientForm';
$route['patient-form/create'] = 'Consultations/PatientForm/create';
$route['Patient-form/create'] = 'Consultations/PatientForm/create';
$route['consultation/payment/(:any)'] = 'Consultations/Payment/index/$1';
$route['Consultation/payment/(:any)'] = 'Consultations/Payment/index/$1';
$route['swap-medecin'] = 'Consultations/PatientForm/changeDoctor';
$route['Swap-medecin'] = 'Consultations/PatientForm/changeDoctor';
$route['home-patient'] = 'Dashboard/PatientDashboard/index';
$route['Home-patient'] = 'Dashboard/PatientDashboard/index';
$route['update-profile'] = 'Dashboard/PatientDashboard/update_home';
$route['Update-profile'] = 'Dashboard/PatientDashboard/update_home';
$route['patient-fallowed'] = 'Consultations/Entente/confirme';
$route['Patient-fallowed'] = 'Consultations/Entente/confirme';

// Auth
$route['auth'] = 'Auth/index';
$route['Auth'] = 'Auth/index';
$route['auth/login'] = 'Auth/login';
$route['Auth/login'] = 'Auth/login';
$route['auth/logout'] = 'Auth/logout';
$route['Auth/logout'] = 'Auth/logout';
$route['auth/register'] = 'Auth/register';
$route['Auth/register'] = 'Auth/register';
$route['auth/forgot_password'] = 'Auth/forgot_password';
$route['Auth/forgot_password'] = 'Auth/forgot_password';
$route['auth/google'] = 'Auth/google';
$route['Auth/google'] = 'Auth/google';
$route['auth/facebook'] = 'Auth/facebook';
$route['Auth/facebook'] = 'Auth/facebook';

// Media public
$route['media'] = 'Home/media/index';
$route['Media'] = 'Home/media/index';
$route['media/search'] = 'Home/media/search';
$route['Media/search'] = 'Home/media/search';
$route['media/search/(:any)'] = 'Home/media/search/$1';
$route['Media/search/(:any)'] = 'Home/media/search/$1';
$route['media/view/(:any)'] = 'Home/media/view/$1';
$route['Media/view/(:any)'] = 'Home/media/view/$1';
$route['media/category/(:any)'] = 'Home/media/category/$1';
$route['Media/category/(:any)'] = 'Home/media/category/$1';
$route['media/detail/(:any)'] = 'Home/media/detail/$1';
$route['Media/detail/(:any)'] = 'Home/media/detail/$1';
$route['media/type/(:any)'] = 'Home/media/type/$1';
$route['Media/type/(:any)'] = 'Home/media/type/$1';
$route['media/favorites'] = 'Home/media/favorites';
$route['Media/favorites'] = 'Home/media/favorites';
$route['media/player/(:any)'] = 'Home/media/player/$1';
$route['Media/player/(:any)'] = 'Home/media/player/$1';
$route['media/downloader'] = 'Home/media/downloader';
$route['Media/downloader'] = 'Home/media/downloader';
$route['media/downloader/(:any)'] = 'Home/media/downloader/$1';
$route['Media/downloader/(:any)'] = 'Home/media/downloader/$1';

// Pages d'investissement
$route['profile-entreprise'] = 'Frontend/Profile_Entreprise/index';
$route['Profile-entreprise'] = 'Frontend/Profile_Entreprise/index';
$route['background-strategic-rationale'] = 'Frontend/Background_Strategic_Rationale/index';
$route['Background-strategic-rationale'] = 'Frontend/Background_Strategic_Rationale/index';
$route['nufotec-phytomed-facility'] = 'Frontend/NUFOTEC_PHYTOMED_INDUSTRIES_Facility/index';
$route['Nufotec-phytomed-facility'] = 'Frontend/NUFOTEC_PHYTOMED_INDUSTRIES_Facility/index';
$route['risk-analysis'] = 'Frontend/Risk_Analysis_Mitigation_Strategies/index';
$route['Risk-analysis'] = 'Frontend/Risk_Analysis_Mitigation_Strategies/index';
$route['strategic-partnerships'] = 'Frontend/Strategic_Partnerships/index';
$route['Strategic-partnerships'] = 'Frontend/Strategic_Partnerships/index';
$route['broker-commission'] = 'Frontend/Commission_Fee_Payment_to_Brokers/index';
$route['Broker-commission'] = 'Frontend/Commission_Fee_Payment_to_Brokers/index';
$route['investor-commitment'] = 'Frontend/Our_Investor_Partner_Commitment/index';
$route['Investor-commitment'] = 'Frontend/Our_Investor_Partner_Commitment/index';
$route['investment-projection'] = 'Frontend/Phased_Investment_Projection/index';
$route['Investment-projection'] = 'Frontend/Phased_Investment_Projection/index';
$route['market-outlook'] = 'Frontend/Market_Industry_Outlook/index';
$route['Market-outlook'] = 'Frontend/Market_Industry_Outlook/index';
$route['digital-growth'] = 'Frontend/Market_Expansion_Platform/index';
$route['Digital-growth'] = 'Frontend/Market_Expansion_Platform/index';
$route['vision-mission'] = 'Frontend/Vision_Mission/index';
$route['Vision-mission'] = 'Frontend/Vision_Mission/index';
$route['corporate-structure-governance'] = 'Frontend/Corporate_Structure_Governance/index';
$route['Corporate-structure-governance'] = 'Frontend/Corporate_Structure_Governance/index';
$route['esg_sustainability'] = 'Frontend/Esg_Sustainability/index';
$route['Esg_sustainability'] = 'Frontend/Esg_Sustainability/index';
$route['research_innovation'] = 'Frontend/Research_Innovation/index';
$route['Research_innovation'] = 'Frontend/Research_Innovation/index';
$route['manufacturing-facility'] = 'Frontend/Manufacturing_Facility/index';
$route['Manufacturing-facility'] = 'Frontend/Manufacturing_Facility/index';

// Investisseurs
$route['investors-form'] = 'Investors/index';
$route['Investors-form'] = 'Investors/index';
$route['brokers-form'] = 'Api/Brokers';
$route['Brokers-form'] = 'Api/Brokers';

// Admin & Dashboard
$route['admin'] = 'Admin/index';
$route['Admin'] = 'Admin/index';
$route['admin/do_login'] = 'Admin/do_login';
$route['Admin/do_login'] = 'Admin/do_login';
$route['dashboard/patientdashboard/update_profile'] = 'Dashboard/PatientDashboard/update_profile';
$route['Dashboard/patientdashboard/update_profile'] = 'Dashboard/PatientDashboard/update_profile';

// Advertise product public
$route['advertise-product/view/(:any)'] = 'Advertise_product/productDetail/$1';
$route['Advertise-product/view/(:any)'] = 'Advertise_product/productDetail/$1';

// ============================================
// ROUTE GÉNÉRIQUE POUR PAGES STATIQUES (DOIT ÊTRE LA DERNIÈRE)
// ============================================
$route['(:any)'] = 'Home/Home/view/$1';