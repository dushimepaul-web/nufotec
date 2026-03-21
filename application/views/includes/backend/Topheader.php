<!--start header -->
<?php
// ============================================
// RÉCUPÉRATION DES DONNÉES DE SESSION
// ============================================

// Données de session utilisateur
$idUser = $this->session->userdata('user_id');
$uuid = $this->session->userdata('uuid');
$email = $this->session->userdata('email');
$nom = $this->session->userdata('nom');
$prenom = $this->session->userdata('prenom');
$user_name = $this->session->userdata('username');
$photo = $this->session->userdata('photo');
$type_utilisateur = $this->session->userdata('type_utilisateur');
$user_role = $this->session->userdata('role');
$role_id = $this->session->userdata('role_id');
$role_slug = $this->session->userdata('role_slug');
$logged_in = $this->session->userdata('logged_in');

// ============================================
// CONSTRUCTION DES VARIABLES D'AFFICHAGE
// ============================================

// Nom complet pour l'affichage
$nom_complet = '';
if (!empty($prenom) && !empty($nom)) {
    $nom_complet = trim($prenom . ' ' . $nom);
} elseif (!empty($user_name)) {
    $nom_complet = $user_name;
} else {
    $nom_complet = 'Utilisateur';
}

// Initiales pour l'avatar
$initials = 'UR';
if (!empty($prenom) && !empty($nom)) {
    $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
} elseif (!empty($user_name)) {
    $initials = strtoupper(substr($user_name, 0, 2));
}

// ============================================
// VARIABLES SUPPLÉMENTAIRES (avec valeurs par défaut)
// ============================================

$is_active = 1;
$telephone = '';
$date_naissance = '';
$genre = '';
$nom_entreprise = '';
$secteur_activite = '';
$numero_registre_commerce = '';
$interet_investissement = '';
$last_login_at = '';
$last_login_ip = '';
$created_at = '';
$updated_at = '';

// Si l'utilisateur est connecté, récupérer les données complètes depuis la BDD
if (!empty($logged_in) && !empty($idUser)) {
    // Vérifier si le modèle est chargé, sinon le charger
    if (!isset($this->User_model)) {
        $this->load->model('User_model');
    }
    
    // Récupérer les données utilisateur
    $user_data = $this->User_model->get_user_by_id($idUser);
    
    if ($user_data) {
        // Mettre à jour les variables avec les données de la BDD
        $is_active = $user_data['is_active'] ?? 1;
        $telephone = $user_data['telephone'] ?? '';
        $date_naissance = $user_data['date_naissance'] ?? '';
        $genre = $user_data['genre'] ?? '';
        $nom_entreprise = $user_data['nom_entreprise'] ?? '';
        $secteur_activite = $user_data['secteur_activite'] ?? '';
        $numero_registre_commerce = $user_data['numero_registre_commerce'] ?? '';
        $interet_investissement = $user_data['interet_investissement'] ?? '';
        $last_login_at = $user_data['last_login_at'] ?? '';
        $last_login_ip = $user_data['last_login_ip'] ?? '';
        $created_at = $user_data['created_at'] ?? '';
        $updated_at = $user_data['updated_at'] ?? '';
        
        // Synchroniser les données de session si nécessaire
        if (empty($photo) && !empty($user_data['photo'])) {
            $photo = $user_data['photo'];
        }
    }
}

// ============================================
// DONNÉES POUR NOTIFICATIONS ET MESSAGES
// ============================================

$notif_count = 0;
$recent_notifications = [];
$msg_count = 0;
$recent_messages = [];

if (!empty($logged_in) && !empty($idUser) && isset($this->Model)) {
    // Compter les notifications non lues
    $notif_count = $this->Model->count('notifications', ['user_id' => $idUser, 'is_read' => 0]);
    
    // Récupérer les notifications récentes
    $recent_notifications = $this->db->where('user_id', $idUser)
                                    ->or_where('user_id IS NULL', NULL, FALSE)
                                    ->where('is_read', 0)
                                    ->order_by('created_at', 'DESC')
                                    ->limit(5)
                                    ->get('notifications')
                                    ->result_array();
    
    // Compter les messages non lus
    $msg_count = $this->Model->count('contact_us', ['is_readed' => 0]);
    
    // Récupérer les messages récents
    $recent_messages = $this->db->where('is_readed', 0)
                               ->order_by('created_at', 'DESC')
                               ->limit(3)
                               ->get('contact_us')
                               ->result_array();
}

// Chemin de la photo
$photo_path = 'attachments/users/' . ($photo ?? 'default-avatar.png');
$full_photo_path = FCPATH . $photo_path;
$photo_exists = !empty($photo) && file_exists($full_photo_path);
?>

<!--start header -->
<header>
<div class="topbar">
    <nav class="navbar navbar-expand gap-2 align-items-center">
        <div class="mobile-toggle-menu d-flex"><i class='bx bx-menu'></i>
        </div>

          <div class="top-menu ms-auto">
            <ul class="navbar-nav align-items-center gap-1">
                <li class="nav-item mobile-search-icon d-flex d-lg-none" data-bs-toggle="modal" data-bs-target="#SearchModal">
                    <a class="nav-link" href="javascript:;"><i class='bx bx-search'></i>
                    </a>
                </li>
                <li class="nav-item dropdown dropdown-laungauge d-none d-sm-flex">
                    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="javascript:;" data-bs-toggle="dropdown"><img src="<?=base_url()?>assets/backend/images/county/02.png" width="22" alt="">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"><img src="<?=base_url()?>assets/backend/images/county/01.png" width="20" alt=""><span class="ms-2">English</span></a>
                        </li>
                        <li><a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"><img src="<?=base_url()?>assets/backend/images/county/03.png" width="20" alt=""><span class="ms-2">French</span></a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dark-mode d-none d-sm-flex">
                    <a class="nav-link dark-mode-icon" href="javascript:;"><i class='bx bx-moon'></i>
                    </a>
                </li>

                <li class="nav-item dropdown dropdown-app">
                    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown" href="javascript:;"><i class='bx bx-grid-alt'></i></a>
                    <div class="dropdown-menu dropdown-menu-end p-0">
                        <div class="app-container p-2 my-2">
                          <div class="row gx-0 gy-2 row-cols-3 justify-content-center p-2">

                             <div class="col">
                                 <a href="<?= base_url() ?>" target="_blank" class="text-decoration-none" title="Ouvrir Stack Overflow">
                                    <div class="app-box text-center p-3 rounded-3 hover-scale">
                                      <div class="app-icon mb-2">
                                         <img src="<?= base_url() ?>assets/backend/images/app/stack-overflow.png" 
                                     width="36" 
                                     height="36" 
                                     alt="Stack Overflow Logo"
                                     class="rounded-circle"
                                     onerror="this.src='<?= base_url() ?>assets/backend/images/app/default-app.png'">
                                    </div>
                                     <div class="app-name">
                                    <p class="mb-0 text-dark fw-medium small">Home</p>
                                  </div>
                                </div>
                             </a>
                          </div>

                          <div class="col">
                                 <a href="<?= base_url('Home/Actions') ?>" target="_blank" class="text-decoration-none" title="Ouvrir Stack Overflow">
                                    <div class="app-box text-center p-3 rounded-3 hover-scale">
                                      <div class="app-icon mb-2">
                                         <img src="<?= base_url() ?>assets/backend/images/app/stack-overflow.png" 
                                     width="36" 
                                     height="36" 
                                     alt="Stack Overflow Logo"
                                     class="rounded-circle"
                                     onerror="this.src='<?= base_url() ?>assets/backend/images/app/default-app.png'">
                                    </div>
                                     <div class="app-name">
                                    <p class="mb-0 text-dark fw-medium small">Projects</p>
                                  </div>
                                </div>
                             </a>
                          </div>
                          <div class="col">
                                 <a href="<?=base_url('Home/Team')?>" target="_blank" class="text-decoration-none" title="Ouvrir Stack Overflow">
                                    <div class="app-box text-center p-3 rounded-3 hover-scale">
                                      <div class="app-icon mb-2">
                                         <img src="<?= base_url() ?>assets/backend/images/app/stack-overflow.png" 
                                     width="36" 
                                     height="36" 
                                     alt="Stack Overflow Logo"
                                     class="rounded-circle"
                                     onerror="this.src='<?= base_url() ?>assets/backend/images/app/default-app.png'">
                                    </div>
                                     <div class="app-name">
                                    <p class="mb-0 text-dark fw-medium small">Team</p>
                                  </div>
                                </div>
                             </a>
                          </div>
                          <div class="col">
                                 <a href="<?= base_url('Home/Galleries') ?>" target="_blank" class="text-decoration-none" title="Ouvrir Stack Overflow">
                                    <div class="app-box text-center p-3 rounded-3 hover-scale">
                                      <div class="app-icon mb-2">
                                         <img src="<?= base_url() ?>assets/backend/images/app/stack-overflow.png" 
                                     width="36" 
                                     height="36" 
                                     alt="Stack Overflow Logo"
                                     class="rounded-circle"
                                     onerror="this.src='<?= base_url() ?>assets/backend/images/app/default-app.png'">
                                    </div>
                                     <div class="app-name">
                                    <p class="mb-0 text-dark fw-medium small">Galleries</p>
                                  </div>
                                </div>
                             </a>
                          </div>
    
                          </div><!--end row-->
    
                        </div>
                    </div>
                </li>

                <li class="nav-item dropdown dropdown-large">
                    <div class="dropdown-menu dropdown-menu-end">

                        <div class="header-notifications-list">
                        
                        </div>

                    </div>
                </li>
                <li class="nav-item dropdown dropdown-large">
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="header-message-list">
                            
                            
                        </div>
                    </div>
                </li>


            </ul>
        </div>

            <?php
            // Récupération des données de session selon la nouvelle structure
            $idUser = $this->session->userdata('user_id');
            $uuid = $this->session->userdata('uuid');
            $email = $this->session->userdata('email');
            $nom = $this->session->userdata('nom');
            $prenom = $this->session->userdata('prenom');
            $user_name = $this->session->userdata('username');
            $photo = $this->session->userdata('photo');
            $type_utilisateur = $this->session->userdata('type_utilisateur');
            $user_role = $this->session->userdata('role');
            $role_id = $this->session->userdata('role_id');
            $role_slug = $this->session->userdata('role_slug');
            $logged_in = $this->session->userdata('logged_in');
            $last_regenerate = $this->session->userdata('last_regenerate');

            // Construction du nom complet pour l'affichage
            $nom_complet = trim(($prenom ?? '') . ' ' . ($nom ?? ''));
            if (empty($nom_complet)) {
                $nom_complet = $user_name ?? 'Utilisateur';
            }

            // Construction des initiales pour l'avatar
            $initials = 'UR';
            if (!empty($prenom) && !empty($nom)) {
                $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
            } elseif (!empty($user_name)) {
                $initials = strtoupper(substr($user_name, 0, 2));
            }

            $message_non_lus = $this->Model->count('contact_us', ['is_readed' => 0]);
            ?>

            <?php if (!empty($logged_in) && $logged_in === TRUE): ?>
            <div class="user-box dropdown">
                <a class="d-flex align-items-center nav-link dropdown-toggle gap-3 dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="position-relative">
                        <?php 
                        $photo_path = 'attachments/users/' . $photo;
                        if (!empty($photo) && file_exists(FCPATH . $photo_path)): 
                        ?>
                            <img src="<?= base_url($photo_path) ?>" 
                                 class="user-img rounded-circle border border-2 border-primary" 
                                 width="40" 
                                 height="40" 
                                 alt="Avatar"
                                 onerror="this.src='<?= base_url('assets/frontend/img/logo/urumuri.jpeg') ?>'">
                        <?php else: ?>
                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center bg-primary text-white border border-2 border-primary" 
                                 style="width: 40px; height: 40px; font-weight: bold; font-size: 14px;">
                                <?= $initials ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($user_role) && ($user_role == 'Admin' || $user_role == 'Super Admin')): ?>
                            <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white" 
                                  style="width: 12px; height: 12px;"></span>
                        <?php endif; ?>
                    </div>
                    <div class="user-info d-none d-md-block">
                        <p class="user-name mb-0 fw-semibold" style="font-size: 14px;">
                            <?= htmlspecialchars($nom_complet) ?>
                        </p>
                        <p class="designation mb-0 text-muted" style="font-size: 12px;">
                            <i class="bx bx-badge-check me-1"></i>
                            <?= !empty($user_role) ? htmlspecialchars($user_role) : 'Utilisateur' ?>
                        </p>
                    </div>
                </a>
                
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 250px;">
                    <li>
                        <div class="dropdown-header text-dark bg-transparent border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php if (!empty($photo) && file_exists(FCPATH . $photo_path)): ?>
                                        <img src="<?= base_url($photo_path) ?>" 
                                             class="rounded-circle" 
                                             width="45" 
                                             height="45" 
                                             alt="Avatar"
                                             onerror="this.src='<?= base_url('assets/frontend/img/logo/urumuri.jpeg') ?>'">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                             style="width: 45px; height: 45px;">
                                            <?= $initials ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-dark"><?= htmlspecialchars($nom_complet) ?></h6>
                                    <small class="text-muted"><?= !empty($email) ? htmlspecialchars($email) : 'Non défini' ?></small>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="javascript:;" data-bs-toggle="modal" data-bs-target="#profileViewModal">
                            <i class="bx bx-user fs-5 me-2"></i>
                            <span>Mon Profil (Détails)</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="javascript:;" data-bs-toggle="modal" data-bs-target="#profileEditModal">
                            <i class="bx bx-edit fs-5 me-2"></i>
                            <span>Modifier mon profil</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="<?= base_url('Configurations') ?>">
                            <i class="bx bx-cog fs-5 me-2"></i>
                            <span>Paramètres</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="<?= base_url('Contact_Us') ?>">
                            <i class="bx bx-bell fs-5 me-2"></i>
                            <span>Notifications</span>
                            <?php if (!empty($message_non_lus) && $message_non_lus > 0): ?>
                                <span class="badge bg-primary ms-auto">
                                    <?= (int) $message_non_lus ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center text-danger" href="<?= base_url('Admin/logout') ?>">
                            <i class="bx bx-log-out-circle fs-5 me-2"></i>
                            <span>Déconnexion</span>
                        </a>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
        </nav>
    </div>
</header>

<!-- ============================================ -->
<!-- MODAL DE VISUALISATION DU PROFIL (DÉTAILS) -->
<!-- ============================================ -->
<div class="modal fade" id="profileViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg, #1a8c78, #062C54);">
                <h5 class="modal-title"><i class="bx bx-user me-2"></i>Détails du Profil</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <!-- Photo de profil -->
                    <div class="col-md-4 text-center">
                        <div class="position-relative">
                            <?php if ($photo_exists): ?>
                                <img src="<?= base_url($photo_path) ?>" 
                                     class="rounded-circle border border-4 border-white shadow-lg" 
                                     style="width: 150px; height: 150px; object-fit: cover;"
                                     alt="Photo de profil"
                                     onerror="this.src='<?= base_url('assets/frontend/img/logo/urumuri.jpeg') ?>'">
                            <?php else: ?>
                                <div class="rounded-circle border border-4 border-white shadow-lg bg-primary d-flex align-items-center justify-content-center mx-auto"
                                     style="width: 150px; height: 150px;">
                                    <span class="text-white fw-bold" style="font-size: 40px;"><?= $initials ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h4 class="mt-3 fw-bold"><?= htmlspecialchars($nom_complet) ?></h4>
                        <p class="text-muted"><?= !empty($type_utilisateur) ? htmlspecialchars($type_utilisateur) : 'Membre' ?></p>
                        
                        <span class="badge bg-success rounded-pill px-3 py-1">
                            <?= !empty($user_role) ? htmlspecialchars($user_role) : 'Actif' ?>
                        </span>
                    </div>
                    
                    <!-- Informations détaillées -->
                    <div class="col-md-8">
                        <h5 class="border-bottom pb-2 mb-3">Informations Personnelles</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Nom Complet</label>
                                <p class="fw-bold mb-0"><?= htmlspecialchars($nom_complet) ?></p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Email</label>
                                <p class="fw-bold mb-0"><?= !empty($email) ? htmlspecialchars($email) : 'Non renseigné' ?></p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Nom</label>
                                <p class="fw-bold mb-0"><?= !empty($nom) ? htmlspecialchars($nom) : 'Non renseigné' ?></p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Prénom</label>
                                <p class="fw-bold mb-0"><?= !empty($prenom) ? htmlspecialchars($prenom) : 'Non renseigné' ?></p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Nom d'utilisateur</label>
                                <p class="fw-bold mb-0"><?= !empty($user_name) ? htmlspecialchars($user_name) : 'Non renseigné' ?></p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Rôle</label>
                                <p class="fw-bold mb-0"><?= !empty($user_role) ? htmlspecialchars($user_role) : 'Utilisateur' ?></p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Type d'utilisateur</label>
                                <p class="fw-bold mb-0"><?= !empty($type_utilisateur) ? htmlspecialchars($type_utilisateur) : 'Standard' ?></p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Téléphone</label>
                                <p class="fw-bold mb-0"><?= !empty($telephone) ? htmlspecialchars($telephone) : 'Non renseigné' ?></p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Genre</label>
                                <p class="fw-bold mb-0">
                                    <?php 
                                    if (!empty($genre)) {
                                        if ($genre == 'M') echo 'Masculin';
                                        elseif ($genre == 'F') echo 'Féminin';
                                        else echo htmlspecialchars($genre);
                                    } else {
                                        echo 'Non renseigné';
                                    }
                                    ?>
                                </p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Date de naissance</label>
                                <p class="fw-bold mb-0"><?= !empty($date_naissance) ? date('d/m/Y', strtotime($date_naissance)) : 'Non renseigné' ?></p>
                            </div>
                            
                            <?php if (!empty($nom_entreprise)): ?>
                            <div class="col-md-12">
                                <label class="form-label text-muted small mb-1">Entreprise</label>
                                <p class="fw-bold mb-0"><?= htmlspecialchars($nom_entreprise) ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($secteur_activite)): ?>
                            <div class="col-md-12">
                                <label class="form-label text-muted small mb-1">Secteur d'activité</label>
                                <p class="fw-bold mb-0"><?= htmlspecialchars($secteur_activite) ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">ID Utilisateur</label>
                                <p class="fw-bold mb-0"><?= !empty($idUser) ? (int)$idUser : 'N/A' ?></p>
                            </div>
                            
                            <?php if (!empty($uuid)): ?>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">UUID</label>
                                <p class="fw-bold mb-0 text-truncate" title="<?= htmlspecialchars($uuid) ?>"><?= htmlspecialchars(substr($uuid, 0, 8)) ?>...</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#profileEditModal" data-bs-dismiss="modal">
                    <i class="bx bx-edit me-1"></i>Modifier mon profil
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL D'ÉDITION DU PROFIL (MODIFICATION) -->
<!-- ============================================ -->
<div class="modal fade" id="profileEditModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            
            <!-- Header -->
            <div class="modal-header border-0 text-white py-3" style="background: linear-gradient(135deg, #1a8c78, #062C54);">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                        <i class="bx bx-user-circle fs-3"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold">Modifier mon Profil</h5>
                        <small class="text-white-50">Gérez vos informations personnelles</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <div class="modal-body p-0">
                <form id="profileUpdateForm" action="<?= base_url('Profil_user/update_profile') ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($idUser ?? '') ?>">
                    <input type="hidden" name="uuid" value="<?= htmlspecialchars($uuid ?? '') ?>">
                    
                    <div class="row g-0">
                        <!-- Sidebar avec photo et navigation -->
                        <div class="col-md-3 bg-light border-end">
                            <div class="p-4 text-center border-bottom">
                                <div class="position-relative d-inline-block mb-3">
                                    <div class="profile-photo-container" style="width: 120px; height: 120px; margin: 0 auto;">
                                        <img src="<?= $photo_exists ? base_url($photo_path) : base_url('assets/frontend/img/logo/urumuri.jpeg') ?>" 
                                             id="profilePhotoPreview"
                                             class="rounded-circle border border-4 border-white shadow-sm" 
                                             style="width: 120px; height: 120px; object-fit: cover;"
                                             alt="Photo de profil"
                                             onerror="this.src='<?= base_url('assets/frontend/img/logo/urumuri.jpeg') ?>'">
                                        
                                        <!-- Overlay pour changement de photo -->
                                        <div class="position-absolute bottom-0 end-0">
                                            <label for="photoInput" class="btn btn-primary btn-sm rounded-circle shadow-sm" 
                                                   style="width: 36px; height: 36px; cursor: pointer;"
                                                   title="Changer la photo">
                                                <i class="bx bx-camera"></i>
                                            </label>
                                            <input type="file" id="photoInput" name="photo" class="d-none" accept="image/jpeg,image/png,image/gif,image/webp">
                                        </div>
                                    </div>
                                </div>
                                
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($nom_complet ?? 'Utilisateur') ?></h6>
                                <span class="badge bg-<?= ($is_active ?? 1) ? 'success' : 'warning' ?> rounded-pill">
                                    <?= ($is_active ?? 1) ? 'Compte actif' : 'En attente' ?>
                                </span>
                                
                                <div class="mt-3 small text-muted">
                                    <div class="mb-1"><i class="bx bx-envelope me-1"></i><?= htmlspecialchars($email ?? 'Non défini') ?></div>
                                    <div><i class="bx bx-shield-alt me-1"></i><?= htmlspecialchars($user_role ?? 'Utilisateur') ?></div>
                                </div>
                            </div>

                            <!-- Navigation des onglets -->
                            <div class="nav flex-column nav-pills" id="profileTabs" role="tablist" aria-orientation="vertical">
                                <button class="nav-link active text-start px-4 py-3 border-0 rounded-0" 
                                        id="personal-tab" data-bs-toggle="pill" data-bs-target="#personal" type="button" role="tab">
                                    <i class="bx bx-user me-2"></i>Informations personnelles
                                </button>
                                <button class="nav-link text-start px-4 py-3 border-0 rounded-0" 
                                        id="contact-tab" data-bs-toggle="pill" data-bs-target="#contact" type="button" role="tab">
                                    <i class="bx bx-phone me-2"></i>Contact & Localisation
                                </button>
                                <button class="nav-link text-start px-4 py-3 border-0 rounded-0" 
                                        id="professional-tab" data-bs-toggle="pill" data-bs-target="#professional" type="button" role="tab">
                                    <i class="bx bx-briefcase me-2"></i>Informations professionnelles
                                </button>
                                <button class="nav-link text-start px-4 py-3 border-0 rounded-0" 
                                        id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab">
                                    <i class="bx bx-lock-alt me-2"></i>Sécurité & Connexion
                                </button>
                            </div>
                        </div>

                        <!-- Contenu des onglets -->
                        <div class="col-md-9">
                            <div class="tab-content p-4" id="profileTabContent">
                                
                                <!-- Onglet: Informations personnelles -->
                                <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                    <h6 class="text-primary fw-bold mb-4 border-bottom pb-2">
                                        <i class="bx bx-user-circle me-2"></i>Informations personnelles
                                    </h6>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-muted">Prénom <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bx bx-user"></i></span>
                                                <input type="text" class="form-control" name="prenom" 
                                                       value="<?= htmlspecialchars($prenom ?? '') ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-muted">Nom <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bx bx-user"></i></span>
                                                <input type="text" class="form-control" name="nom" 
                                                       value="<?= htmlspecialchars($nom ?? '') ?>" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-muted">Nom d'utilisateur</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bx bx-at"></i></span>
                                                <input type="text" class="form-control" name="username" 
                                                       value="<?= htmlspecialchars($user_name ?? '') ?>">
                                            </div>
                                            <div class="form-text">Nom utilisé pour la connexion</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-muted">Genre</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bx bx-male-female"></i></span>
                                                <select class="form-select" name="genre">
                                                    <option value="">-- Sélectionner --</option>
                                                    <option value="M" <?= (isset($genre) && $genre == 'M') ? 'selected' : '' ?>>Masculin</option>
                                                    <option value="F" <?= (isset($genre) && $genre == 'F') ? 'selected' : '' ?>>Féminin</option>
                                                    <option value="Autre" <?= (isset($genre) && $genre == 'Autre') ? 'selected' : '' ?>>Autre</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-muted">Date de naissance</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bx bx-calendar"></i></span>
                                                <input type="date" class="form-control" name="date_naissance" 
                                                       value="<?= !empty($date_naissance) ? date('Y-m-d', strtotime($date_naissance)) : '' ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-muted">Type d'utilisateur</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bx bx-id-card"></i></span>
                                                <select class="form-select" name="type_utilisateur" disabled>
                                                    <option value="admin" <?= ($type_utilisateur == 'admin') ? 'selected' : '' ?>>Administrateur</option>
                                                    <option value="medecin" <?= ($type_utilisateur == 'medecin') ? 'selected' : '' ?>>Médecin</option>
                                                    <option value="patient" <?= ($type_utilisateur == 'patient') ? 'selected' : '' ?>>Patient</option>
                                                    <option value="entreprise" <?= ($type_utilisateur == 'entreprise') ? 'selected' : '' ?>>Entreprise</option>
                                                    <option value="investisseur" <?= ($type_utilisateur == 'investisseur') ? 'selected' : '' ?>>Investisseur</option>
                                                    <option value="partenaire" <?= ($type_utilisateur == 'partenaire') ? 'selected' : '' ?>>Partenaire</option>
                                                    <option value="broker" <?= ($type_utilisateur == 'broker') ? 'selected' : '' ?>>Broker</option>
                                                </select>
                                            </div>
                                            <input type="hidden" name="type_utilisateur" value="<?= htmlspecialchars($type_utilisateur ?? 'patient') ?>">
                                            <div class="form-text">Contactez l'administrateur pour changer</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Onglet: Contact -->
                                <div class="tab-pane fade" id="contact" role="tabpanel">
                                    <h6 class="text-primary fw-bold mb-4 border-bottom pb-2">
                                        <i class="bx bx-phone-call me-2"></i>Contact & Localisation
                                    </h6>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-muted">Email <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bx bx-envelope"></i></span>
                                                <input type="email" class="form-control" name="email" 
                                                       value="<?= htmlspecialchars($email ?? '') ?>" required>
                                            </div>
                                            <div class="form-text text-warning">
                                                <i class="bx bx-info-circle me-1"></i>Changer l'email nécessite une vérification
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-muted">Téléphone</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bx bx-phone"></i></span>
                                                <input type="tel" class="form-control" name="telephone" 
                                                       value="<?= htmlspecialchars($telephone ?? '') ?>"
                                                       placeholder="+257 XX XXX XXX">
                                            </div>
                                        </div>

                                        <!-- Champs conditionnels selon le type -->
                                        <?php if (in_array($type_utilisateur ?? '', ['entreprise', 'investisseur', 'broker'])): ?>
                                        <div class="col-12 mt-4">
                                            <div class="alert alert-info d-flex align-items-center">
                                                <i class="bx bx-building-house fs-4 me-3"></i>
                                                <div>
                                                    <strong>Informations <?= ucfirst($type_utilisateur ?? '') ?></strong>
                                                    <div class="small">Ces champs sont spécifiques à votre profil</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-muted">Nom de l'entreprise</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bx bx-building"></i></span>
                                                <input type="text" class="form-control" name="nom_entreprise" 
                                                       value="<?= htmlspecialchars($nom_entreprise ?? '') ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-muted">Secteur d'activité</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bx bx-category"></i></span>
                                                <input type="text" class="form-control" name="secteur_activite" 
                                                       value="<?= htmlspecialchars($secteur_activite ?? '') ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-muted">Numéro de registre de commerce</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bx bx-file"></i></span>
                                                <input type="text" class="form-control" name="numero_registre_commerce" 
                                                       value="<?= htmlspecialchars($numero_registre_commerce ?? '') ?>">
                                            </div>
                                        </div>

                                        <?php if ($type_utilisateur == 'investisseur'): ?>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small text-muted">Intérêt d'investissement (USD)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" step="0.01" class="form-control" name="interet_investissement" 
                                                       value="<?= htmlspecialchars($interet_investissement ?? '') ?>">
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Onglet: Professionnel -->
                                <div class="tab-pane fade" id="professional" role="tabpanel">
                                    <?php if (!in_array($type_utilisateur ?? '', ['entreprise', 'investisseur', 'broker'])): ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="bx bx-briefcase fs-1 mb-3"></i>
                                        <p>Ces informations ne s'appliquent pas à votre type de compte.</p>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        Les informations professionnelles sont gérées dans l'onglet "Contact & Localisation"
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Onglet: Sécurité -->
                                <div class="tab-pane fade" id="security" role="tabpanel">
                                    <h6 class="text-primary fw-bold mb-4 border-bottom pb-2">
                                        <i class="bx bx-shield-quarter me-2"></i>Sécurité & Connexion
                                    </h6>

                                    <!-- Changement de mot de passe -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-white border-bottom">
                                            <h6 class="mb-0 fw-bold text-dark">
                                                <i class="bx bx-lock-alt me-2 text-primary"></i>Changer le mot de passe
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label fw-semibold small text-muted">Mot de passe actuel</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="bx bx-key"></i></span>
                                                        <input type="password" class="form-control" name="current_password" id="currentPassword">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('currentPassword')">
                                                            <i class="bx bx-show"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small text-muted">Nouveau mot de passe</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="bx bx-lock"></i></span>
                                                        <input type="password" class="form-control" name="new_password" id="newPassword" minlength="8">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPassword')">
                                                            <i class="bx bx-show"></i>
                                                        </button>
                                                    </div>
                                                    <div class="form-text">Minimum 8 caractères</div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small text-muted">Confirmer le nouveau mot de passe</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="bx bx-check-double"></i></span>
                                                        <input type="password" class="form-control" name="confirm_password" id="confirmPassword">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPassword')">
                                                            <i class="bx bx-show"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div id="passwordStrength" class="progress" style="height: 5px; display: none;">
                                                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                                    </div>
                                                    <div id="passwordFeedback" class="form-text mt-1"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Informations de connexion -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-white border-bottom">
                                            <h6 class="mb-0 fw-bold text-dark">
                                                <i class="bx bx-history me-2 text-primary"></i>Historique de connexion
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small text-muted">Dernière connexion</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="bx bx-time"></i></span>
                                                        <input type="text" class="form-control bg-light" 
                                                               value="<?= !empty($last_login_at) ? date('d/m/Y H:i', strtotime($last_login_at)) : 'Jamais connecté' ?>" 
                                                               readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small text-muted">IP de dernière connexion</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="bx bx-wifi"></i></span>
                                                        <input type="text" class="form-control bg-light" 
                                                               value="<?= htmlspecialchars($last_login_ip ?? 'N/A') ?>" 
                                                               readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small text-muted">Compte créé le</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="bx bx-calendar-check"></i></span>
                                                        <input type="text" class="form-control bg-light" 
                                                               value="<?= !empty($created_at) ? date('d/m/Y', strtotime($created_at)) : 'N/A' ?>" 
                                                               readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small text-muted">Dernière mise à jour</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="bx bx-refresh"></i></span>
                                                        <input type="text" class="form-control bg-light" 
                                                               value="<?= !empty($updated_at) ? date('d/m/Y H:i', strtotime($updated_at)) : 'N/A' ?>" 
                                                               readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer avec boutons d'action -->
                    <div class="modal-footer bg-light border-top py-3">
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div class="text-muted small">
                                <i class="bx bx-info-circle me-1"></i>
                                Les champs marqués de <span class="text-danger">*</span> sont obligatoires
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    <i class="bx bx-x me-1"></i>Annuler
                                </button>
                                <button type="submit" class="btn btn-primary px-4" id="saveProfileBtn">
                                    <i class="bx bx-save me-1"></i>Enregistrer les modifications
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toast pour notifications -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="profileToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bx bx-check-circle me-2"></i>
                <span id="toastMessage">Profil mis à jour avec succès</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<style>
/* Styles personnalisés */
.user-img {
    object-fit: cover;
}
.avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
}
.dropdown-menu {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
.dropdown-item:hover {
    background-color: #f8f9fa;
}
.dropdown-header {
    padding: 1rem;
}
.search-bar .input-group {
    max-width: 400px;
}
@media (max-width: 768px) {
    .user-info {
        display: none !important;
    }
    .modal-dialog {
        margin: 0.5rem;
    }
}

/* Styles personnalisés pour le modal profil */
.profile-photo-container {
    position: relative;
    transition: transform 0.3s ease;
}

.profile-photo-container:hover {
    transform: scale(1.05);
}

.nav-pills .nav-link {
    color: #6c757d;
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}

.nav-pills .nav-link:hover {
    background-color: rgba(26, 140, 120, 0.1);
    color: #1a8c78;
}

.nav-pills .nav-link.active {
    background-color: rgba(26, 140, 120, 0.15);
    color: #1a8c78;
    border-left-color: #1a8c78;
    font-weight: 600;
}

.form-control:focus, .form-select:focus {
    border-color: #1a8c78;
    box-shadow: 0 0 0 0.25rem rgba(26, 140, 120, 0.25);
}

/* Dark mode support */
body.dark-mode .dropdown-menu {
    background-color: #343a40;
    border-color: #495057;
}
body.dark-mode .dropdown-item {
    color: #e9ecef;
}
body.dark-mode .dropdown-item:hover {
    background-color: #495057;
}
body.dark-mode .text-dark {
    color: #e9ecef !important;
}
body.dark-mode .text-muted {
    color: #adb5bd !important;
}
body.dark-mode .bg-light {
    background-color: #1a202c !important;
}
body.dark-mode .card-header.bg-white {
    background-color: #1a202c !important;
    border-color: #2d3748;
}
body.dark-mode .border-bottom {
    border-color: #2d3748 !important;
}
</style>

<script>
// Toggle mode sombre
const darkModeToggle = document.querySelector('.dark-mode-icon');
if (darkModeToggle) {
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const icon = this.querySelector('i');
        
        if (document.body.classList.contains('dark-mode')) {
            icon.classList.remove('bx-moon');
            icon.classList.add('bx-sun');
            localStorage.setItem('darkMode', 'enabled');
        } else {
            icon.classList.remove('bx-sun');
            icon.classList.add('bx-moon');
            localStorage.setItem('darkMode', 'disabled');
        }
    });

    // Charger le mode préféré
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
        const icon = document.querySelector('.dark-mode-icon i');
        if (icon) {
            icon.classList.remove('bx-moon');
            icon.classList.add('bx-sun');
        }
    }
}

// Prévisualisation de la photo de profil
const photoInput = document.getElementById('photoInput');
if (photoInput) {
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validation du type de fichier
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Veuillez sélectionner une image valide (JPEG, PNG, GIF, WebP)');
                return;
            }
            
            // Validation de la taille (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('La taille de l\'image ne doit pas dépasser 2MB');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('profilePhotoPreview');
                if (preview) {
                    preview.src = e.target.result;
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bx-show');
        icon.classList.add('bx-hide');
    } else {
        input.type = 'password';
        icon.classList.remove('bx-hide');
        icon.classList.add('bx-show');
    }
}

// Validation de la force du mot de passe
const newPasswordInput = document.getElementById('newPassword');
if (newPasswordInput) {
    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.querySelector('#passwordStrength .progress-bar');
        const feedback = document.getElementById('passwordFeedback');
        const strengthDiv = document.getElementById('passwordStrength');
        
        if (!strengthDiv || !strengthBar || !feedback) return;
        
        if (password.length === 0) {
            strengthDiv.style.display = 'none';
            return;
        }
        
        strengthDiv.style.display = 'flex';
        
        let strength = 0;
        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]+/)) strength += 25;
        if (password.match(/[A-Z]+/)) strength += 25;
        if (password.match(/[0-9!@#$%^&*]+/)) strength += 25;
        
        strengthBar.style.width = strength + '%';
        
        if (strength <= 25) {
            strengthBar.className = 'progress-bar bg-danger';
            feedback.textContent = 'Faible';
            feedback.className = 'form-text mt-1 text-danger';
        } else if (strength <= 50) {
            strengthBar.className = 'progress-bar bg-warning';
            feedback.textContent = 'Moyen';
            feedback.className = 'form-text mt-1 text-warning';
        } else if (strength <= 75) {
            strengthBar.className = 'progress-bar bg-info';
            feedback.textContent = 'Bon';
            feedback.className = 'form-text mt-1 text-info';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            feedback.textContent = 'Excellent';
            feedback.className = 'form-text mt-1 text-success';
        }
    });
}

// Validation du formulaire avant soumission
const profileForm = document.getElementById('profileUpdateForm');
if (profileForm) {
    profileForm.addEventListener('submit', function(e) {
        const newPassword = document.getElementById('newPassword')?.value || '';
        const confirmPassword = document.getElementById('confirmPassword')?.value || '';
        const currentPassword = document.getElementById('currentPassword')?.value || '';
        
        // Si un nouveau mot de passe est saisi
        if (newPassword || confirmPassword || currentPassword) {
            if (!currentPassword) {
                e.preventDefault();
                alert('Veuillez saisir votre mot de passe actuel pour confirmer les changements.');
                document.getElementById('currentPassword')?.focus();
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Les nouveaux mots de passe ne correspondent pas.');
                document.getElementById('confirmPassword')?.focus();
                return false;
            }
            
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Le nouveau mot de passe doit contenir au moins 8 caractères.');
                document.getElementById('newPassword')?.focus();
                return false;
            }
        }
        
        // Désactiver le bouton pendant la soumission
        const submitBtn = document.getElementById('saveProfileBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Enregistrement...';
        }
    });
}

// Réinitialiser le formulaire à la fermeture du modal
const profileEditModalEl = document.getElementById('profileEditModal');
if (profileEditModalEl) {
    profileEditModalEl.addEventListener('hidden.bs.modal', function () {
        // Réactiver le bouton
        const submitBtn = document.getElementById('saveProfileBtn');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bx bx-save me-1"></i>Enregistrer les modifications';
        }
    });
}

// Afficher le toast si message de succès dans l'URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('profile_updated') === '1') {
        const toastEl = document.getElementById('profileToast');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
        
        // Nettoyer l'URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    // Gestion des erreurs d'images
    const images = document.querySelectorAll('img[onerror]');
    images.forEach(img => {
        img.onerror = function() {
            this.src = '<?= base_url("assets/frontend/img/logo/urumuri.jpeg") ?>';
        };
    });
    
    // Amélioration du dropdown utilisateur
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.user-box')) {
            const userDropdown = document.querySelector('.user-box .dropdown-menu');
            if (userDropdown && userDropdown.classList.contains('show')) {
                const dropdownToggle = document.querySelector('.user-box .dropdown-toggle');
                if (dropdownToggle) {
                    const dropdownInstance = bootstrap.Dropdown.getInstance(dropdownToggle);
                    if (dropdownInstance) {
                        dropdownInstance.hide();
                    }
                }
            }
        }
    });
});
</script>

<?php
// ============================================
// FONCTION HELPER: TEMPS RELATIF
// ============================================

if (!function_exists('time_ago')) {
    function time_ago($datetime) {
        if (empty($datetime)) {
            return 'À l\'instant';
        }
        
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 0) {
            return 'À venir';
        }
        if ($diff < 60) {
            return 'À l\'instant';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . ' min';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . ' h';
        }
        if ($diff < 604800) {
            return floor($diff / 86400) . ' j';
        }
        if ($diff < 2592000) {
            return floor($diff / 604800) . ' sem';
        }
        if ($diff < 31536000) {
            return floor($diff / 2592000) . ' mois';
        }
        
        $years = floor($diff / 31536000);
        return $years . ' an' . ($years > 1 ? 's' : '');
    }
}
?>