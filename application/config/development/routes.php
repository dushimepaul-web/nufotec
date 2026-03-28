<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

$route['default_controller'] = 'Home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;



//recherche
$route['search/index'] = 'Home/search/index';
$route['search/ajax_search'] = 'Home/search/ajax_search';


// Routes de la boutique
$route['boutique'] = 'Home/Boutique/index';
$route['boutique/categorie/(:num)'] = 'Home/Boutique/categorie/$1';
$route['boutique/categorie/(:num)/(:num)'] = 'Home/Boutique/categorie/$1/$2';
$route['boutique/detail/(:any)'] = 'Home/Boutique/detail/$1';
$route['boutique/recherche'] = 'Home/Boutique/recherche';
$route['boutique/ajax_get_products'] = 'Home/Boutique/ajax_get_products';


//panierpanier
$route['panier'] = 'Home/Panier/index';
$route['panier/get_cart'] = 'Home/Panier/get_cart';
$route['panier/ajouter'] = 'Home/Panier/ajouter';
$route['panier/update_quantity'] = 'Home/Panier/update_quantity';
$route['panier/delete_line'] = 'Home/Panier/delete_line';
$route['panier/toggle_favori'] = 'Home/Panier/toggle_favori';


//COMMANDE
$route['Commande'] = 'Home/Commande/index';
$route['commande/valider'] = 'Home/Commande/valider';
$route['commande/confirmation/(:num)'] = 'Home/Commande/confirmation/$1'; 
$route['commande/paiement/(:num)'] = 'Home/Commande/paiement/$1';
$route['commande/verifier_paiement/(:num)'] = 'home/Commande/verifier_paiement/$1';  




//pages about
$route['Profile-Entreprise'] = 'Frontend/Corporate_profile';


$route['Medicins'] = 'Consultations/PatientForm/Medicin';
$route['Swap-medecin'] = 'Consultations/PatientForm/changeDoctor'; 
$route['patient-form'] = 'Consultations/PatientForm'; 
$route['home-patient'] = 'Dashboard/PatientDashboard'; 
$route['update-profile'] = 'Dashboard/PatientDashboard/update_home';
//$route['update-profile'] = 'Dashboard/PatientDashboard/update_profile';



$route['Investors-form'] = 'Api/Investors';  
$route['Brokers-form'] = 'Api/Brokers'; 


$route['actualites'] = 'Home/Actualites/index';  

$route['commande'] = 'Home/Commande/index'; 


$route['nufotec-phytomed-facility'] = 'Frontend/NUFOTEC_PHYTOMED_INDUSTRIES_Facility';
$route['risk-analysis'] = 'Frontend/Risk_Analysis_Mitigation_Strategies';  
$route['strategic-partnerships'] = 'Frontend/Strategic_Partnerships'; 
$route['broker-commission'] = 'Frontend/Commission_Fee_Payment_to_Brokers';    
$route['investor-commitment'] = 'Frontend/Our_Investor_Partner_Commitment';  
$route['investment-projection'] = 'Frontend/Phased_Investment_Projection';   
$route['market-outlook'] = 'Frontend/Market_Industry_Outlook';
$route['digital-growth'] = 'Frontend/Market_Expansion_Platform';
$route['vision-mission'] = 'Frontend/Vision_Mission';
$route['corporate-structure-governance'] = 'Frontend/Corporate_Structure_Governance';
$route['background-strategic-rationale'] = 'Frontend/Background_Strategic_Rationale';







//backend
$route['Workflow_categories'] = 'Produits/Workflow_categories/index';
$route['Workflow_categories/Create'] = 'Produits/Workflow_categories/Create';
$route['Workflow_categories/Delete'] = 'Produits/Workflow_categories/Delete';
$route['Workflow_categories/Update'] = 'Produits/Workflow_categories/Update';




 

 //vison mission cmpany statement 



$route['info'] = 'Configurations/Settings_medecin/index';
$route['update_info'] = 'Configurations/Settings_medecin/update_info';
$route['change-password'] = 'Configurations/Settings_medecin/change_password';

$route['calendrier'] = 'Users/Medecin_Calendrier/index';
$route['calendriersave'] = 'Users/Medecin_Calendrier/save';
$route['calendrier'] = 'Users/Medecin_Calendrier/index';
 


$route['users'] = 'users/Users/index';
$route['users-create'] = 'users/Users/Create';
$route['users-update'] = 'users/Users/Update';
$route['users-delete'] = 'users/Users/Delete';
$route['users-create'] = 'users/Users/Create';
 

 
// Routes vidéo
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




// Routes pour le contrôleur Autre (media/Autre.php)
$route['autre'] = 'media/Autre/index';
$route['autre/(:any)'] = 'media/Autre/$1';
$route['autre/index'] = 'media/Autre/index';
$route['autre/Create'] = 'media/Autre/Create';
$route['autre/Update'] = 'media/Autre/Update';
$route['autre/Delete'] = 'media/Autre/Delete';
$route['autre/ChangeStatus'] = 'media/Autre/ChangeStatus';
$route['autre/toggleField'] = 'media/Autre/toggleField';

// ==========================================
// AUDIO ROUTES - CORRIGÉES ET OPTIMISÉES
// ==========================================

// Route principale (DOIT ÊTRE AVANT les routes avec paramètres)
$route['audio'] = 'media/Audio/index';

// ==================== CRUD ROUTES ====================
$route['audio/Create'] = 'media/Audio/Create';
$route['audio/Update'] = 'media/Audio/Update';
$route['audio/Delete'] = 'media/Audio/Delete';

// ==================== API/AJAX ROUTES ====================
$route['audio/ChangeStatus'] = 'media/Audio/ChangeStatus';
$route['audio/toggleField'] = 'media/Audio/toggleField';

// ==================== CHUNKED UPLOAD ROUTES ====================
$route['audio/initUpload'] = 'media/Audio/initUpload';
$route['audio/uploadChunk'] = 'media/Audio/uploadChunk';
$route['audio/completeUpload'] = 'media/Audio/completeUpload';

// ==================== THUMBNAIL ROUTES ====================
$route['audio/uploadThumbnail'] = 'media/Audio/uploadThumbnail';

// ==================== STREAMING ROUTES ====================
$route['audio/stream/(:num)'] = 'media/Audio/stream/$1';

// ==================== CATCH-ALL (DOIT ÊTRE DERNIER) ====================
// Pour toute autre méthode du contrôleur non définie ci-dessus
$route['audio/(:any)'] = 'media/Audio/$1';


$route['patient-fallowed'] = 'Consultations/Entente/confirme/';


// ==================== ROUTES MEDIA ====================

// ==================== ROUTES MEDIA ====================

// Route principale
$route['media'] = 'Home/Media/index';
$route['Media'] = 'Home/Media/index';

// Route pour la recherche HTML (AFFICHAGE DES RÉSULTATS)
$route['media/search'] = 'Home/Media/search';

$route['media/search/(:any)'] = 'Home/Media/search/$1';

// Route pour la recherche AJAX (JSON)
$route['media/searchAjax'] = 'Home/Media/apiSearch';
$route['media/apiSearch'] = 'Home/Media/apiSearch'; // Ajout pour compatibilité

// Route avec filtre par type
$route['media/view/(:any)'] = 'Home/Media/view/$1';
$route['Media/view/(:any)'] = 'Home/Media/view/$1';

// Route pour les catégories
$route['media/category/(:any)'] = 'Home/Media/category/$1';
$route['Media/category/(:any)'] = 'Home/Media/category/$1';

// Route pour le détail d'un média - SUPPORTE SLUG ET ID
$route['media/detail/(:any)'] = 'Home/Media/detail/$1';
$route['Media/detail/(:any)'] = 'Home/Media/detail/$1';

// Route pour le type de média
$route['media/type/(:any)'] = 'Home/Media/type/$1';
$route['Media/type/(:any)'] = 'Home/Media/type/$1';

// ==================== API ROUTES (AJAX) ====================

// Tracking
$route['media/trackView'] = 'Home/Media/apiTrackView';
$route['media/apiTrackView'] = 'Home/Media/apiTrackView'; // Ajout pour compatibilité

$route['media/trackPlay'] = 'Home/Media/apiTrackPlay';
$route['media/apiTrackPlay'] = 'Home/Media/apiTrackPlay'; // Ajout pour compatibilité

// Interactions utilisateur
$route['media/toggleLike'] = 'Home/Media/apiToggleLike';
$route['media/apiToggleLike'] = 'Home/Media/apiToggleLike'; // Ajout pour compatibilité

$route['media/rateMedia'] = 'Home/Media/apiRateMedia';
$route['media/apiRateMedia'] = 'Home/Media/apiRateMedia'; // Ajout pour compatibilité

$route['media/addComment'] = 'Home/Media/apiAddComment';
$route['media/apiAddComment'] = 'Home/Media/apiAddComment'; // AJOUT IMPORTANT

$route['media/checkUserLike/(:num)'] = 'Home/Media/checkUserLike/$1';
$route['media/checkUserLike'] = 'Home/Media/checkUserLike';

// Favoris
$route['media/getFavorites'] = 'Home/Media/apiGetFavorites';
$route['media/apiGetFavorites'] = 'Home/Media/apiGetFavorites'; // Ajout pour compatibilité

$route['media/toggleFavorite'] = 'Home/Media/apiToggleFavorite';
$route['media/apiToggleFavorite'] = 'Home/Media/apiToggleFavorite'; // Ajout pour compatibilité

// Partage
$route['media/share'] = 'Home/Media/apiShare';
$route['media/apiShare'] = 'Home/Media/apiShare'; // Ajout pour compatibilité

// Récupération de données
$route['media/getComments/(:any)'] = 'Home/Media/apiGetComments/$1';
$route['media/apiGetComments/(:any)'] = 'Home/Media/apiGetComments/$1';
$route['media/getComments'] = 'Home/Media/apiGetComments';
$route['media/apiGetComments'] = 'Home/Media/apiGetComments';

$route['media/getRecommended/(:any)'] = 'Home/Media/getRecommended/$1';
$route['media/getMedia/(:any)'] = 'Home/Media/apiGetMedia/$1';
$route['media/apiGetMedia/(:any)'] = 'Home/Media/apiGetMedia/$1';
$route['media/getCategories'] = 'Home/Media/getCategories';
$route['media/getStats'] = 'Home/Media/apiGetStats';
$route['media/apiGetStats'] = 'Home/Media/apiGetStats';

// Waveform
$route['media/getWaveform/(:num)'] = 'Home/Media/apiGetWaveform/$1';
$route['media/apiGetWaveform/(:num)'] = 'Home/Media/apiGetWaveform/$1';

// Streaming audio/vidéo
$route['media/stream/(:any)/(:num)'] = 'Home/Media/stream/$1/$2';

// Favoris et lecteur
$route['media/favorites'] = 'Home/Media/favorites';
$route['media/player/(:any)'] = 'Home/Media/player/$1';

// Blog routes
$route['blog'] = 'Home/Blog';
$route['blog/index'] = 'Home/Blog/index';
$route['blog/categorie/(:any)'] = 'Home/Blog/categorie/$1';
$route['blog/recherche'] = 'Home/Blog/recherche';
$route['actualite/(:any)'] = 'Home/Blog/article/$1';



$route['question'] = 'Home/faq';



$route['PatientForm'] = 'Consultations/PatientForm';



// Routes pour Advertise Product
$route['advertise-product'] = 'Advertise_product/index';
$route['advertise-product-create'] = 'Advertise_product/create';
$route['advertise-product-update'] = 'Advertise_product/update';
$route['advertise-product-delete'] = 'Advertise_product/delete';
$route['advertise-product-change-status'] = 'Advertise_product/changeStatus';
$route['advertise-product-change-featured'] = 'Advertise_product/changeFeatured';
$route['advertise-product/view/(:any)'] = 'Advertise_product/productDetail/$1';

$route['product/(:any)'] = 'Products/detail/$1';



$route['product_categories'] = 'Advertise_product/Product_categories/index';
$route['product_categories/index'] = 'Advertise_product/Product_categories/index';

// CRUD Operations
$route['product_categories/create'] = 'Advertise_product/Product_categories/create';
$route['product_categories/update'] = 'Advertise_product/Product_categories/update';
$route['product_categories/delete'] = 'Advertise_product/Product_categories/delete';
$route['product_categories/view/(:num)'] = 'Advertise_product/Product_categories/view/$1';

