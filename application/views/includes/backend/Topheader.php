<!--start header -->
<header>
<div class="topbar">
    <nav class="navbar navbar-expand gap-2 align-items-center">
        <div class="mobile-toggle-menu d-flex"><i class='bx bx-menu'></i>
        </div>

<!--          <div class="search-bar d-lg-block d-none" data-bs-toggle="modal" data-bs-target="#SearchModal">
             <a href="avascript:;" class="btn d-flex align-items-center"><i class="bx bx-search"></i>Search</a>
          </div> -->

          <div class="top-menu ms-auto">
            <ul class="navbar-nav align-items-center gap-1">
                <li class="nav-item mobile-search-icon d-flex d-lg-none" data-bs-toggle="modal" data-bs-target="#SearchModal">
                    <a class="nav-link" href="avascript:;"><i class='bx bx-search'></i>
                    </a>
                </li>
                <li class="nav-item dropdown dropdown-laungauge d-none d-sm-flex">
                    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="avascript:;" data-bs-toggle="dropdown"><img src="<?=base_url()?>assets/backend/images/county/02.png" width="22" alt="">
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
// Récupération des données de session (correspondant au contrôleur Admin)
$user_id = $this->session->userdata('user_id');
$user_name = $this->session->userdata('username'); // 'username' dans le contrôleur
$user_role = $this->session->userdata('role');
$user_photo = $this->session->userdata('photo');
$user_email = $this->session->userdata('email');
$user_prenom = $this->session->userdata('prenom');
$user_nom = $this->session->userdata('nom');
$role_slug = $this->session->userdata('role_slug');
$login_time = $this->session->userdata('login_time');
$last_activity = $this->session->userdata('last_activity');

// Récupérer les notifications non lues (optionnel)
$message_non_lus = 0;
if ($this->db->table_exists('contact_us')) {
    $message_non_lus = $this->Model->count('contact_us', ['is_readed' => 0]);
}

// Initiales pour l'avatar
$initials = 'UR';
if (!empty($user_prenom) && !empty($user_nom)) {
    $initials = strtoupper(substr($user_prenom, 0, 1) . substr($user_nom, 0, 1));
} elseif (!empty($user_name)) {
    // Si 'username' est défini, prendre les 2 premières lettres
    $name_parts = explode(' ', trim($user_name));
    if (count($name_parts) >= 2) {
        $initials = strtoupper(substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1));
    } else {
        $initials = strtoupper(substr($user_name, 0, 2));
    }
}

// Vérifier si l'utilisateur est connecté
$is_logged_in = $this->session->userdata('logged_in') === TRUE;

// Récupérer les informations complètes du membre (optionnel)
$membre_info = [];
if ($user_id && $is_logged_in) {
    $membre_info = $this->Model->readOne('users', ['id' => $user_id]);
}
?>

<?php if ($is_logged_in && !empty($user_id)): ?>
<div class="user-box dropdown">
    <a class="d-flex align-items-center nav-link dropdown-toggle gap-3 dropdown-toggle-nocaret" 
       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        
        <div class="position-relative">
            <?php 
            $photo_path = 'attachments/Users/' . $user_photo;
            if (!empty($user_photo) && file_exists(FCPATH . $photo_path)): 
            ?>
                <img src="<?= base_url($photo_path) ?>" 
                     class="user-img rounded-circle border border-2 border-primary" 
                     width="40" 
                     height="40" 
                     alt="Avatar"
                     onerror="this.src='<?= base_url('assets/frontend') ?>'">
            <?php else: ?>
                <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center bg-primary text-white border border-2 border-primary" 
                     style="width: 40px; height: 40px; font-weight: bold; font-size: 14px;">
                    <?= $initials ?>
                </div>
            <?php endif; ?>
            
            <!-- Indicateur de statut (Admin/Super Admin) -->
            <?php if (!empty($user_role) && in_array($user_role, ['Admin'])): ?>
                <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white" 
                      style="width: 12px; height: 12px;"></span>
            <?php endif; ?>
        </div>
        
        <div class="user-info d-none d-md-block">
            <p class="user-name mb-0 fw-semibold" style="font-size: 14px;">
                <?= htmlspecialchars($user_name ?? ($user_prenom . ' ' . $user_nom)) ?>
            </p>
            <p class="designation mb-0 text-muted" style="font-size: 12px;">
                <i class="bx bx-badge-check me-1"></i>
                <?= !empty($user_role) ? htmlspecialchars($user_role) : 'Utilisateur' ?>
                <?php if (!empty($role_slug)): ?>
                    <span class="badge bg-light text-dark ms-1"><?= $role_slug ?></span>
                <?php endif; ?>
            </p>
        </div>
    </a>
    
    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 280px;">
        <!-- En-tête du menu déroulant -->
        <li>
            <div class="dropdown-header text-dark bg-transparent border-bottom py-3">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <?php if (!empty($user_photo) && file_exists(FCPATH . $photo_path)): ?>
                            <img src="<?= base_url($photo_path) ?>" 
                                 class="rounded-circle" 
                                 width="50" 
                                 height="50" 
                                 alt="Avatar"
                                 style="object-fit: cover;"
                                 onerror="this.src='<?= base_url('assets/frontend/img/logo/default-avatar.png') ?>'">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" 
                                 style="width: 50px; height: 50px; font-size: 18px;">
                                <?= $initials ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h6 class="mb-0 text-dark fw-bold"><?= htmlspecialchars($user_name ?? ($user_prenom . ' ' . $user_nom)) ?></h6>
                        <small class="text-muted d-block"><?= !empty($user_email) ? htmlspecialchars($user_email) : 'Non défini' ?></small>
                        <?php if (!empty($login_time)): ?>
                            <small class="text-muted">
                                <i class="bx bx-time"></i> 
                                Connecté: <?= date('H:i', $login_time) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </li>
        
        <li><hr class="dropdown-divider"></li>
        
 <li>
    <a class="dropdown-item d-flex align-items-center py-2" 
       href="javascript:;" 
       data-bs-toggle="modal" 
       data-bs-target="#profileModal">
        <i class="bx bx-user fs-5 me-2 text-primary"></i>
        <span>Mon Profil</span>
        <small class="text-muted ms-auto">Détails</small>
    </a>
</li>

        <?php if (medecin_view()): ?>
        
        <li>
            <a class="dropdown-item d-flex align-items-center py-2" 
               href="<?= base_url('Configurations/Settings_medecin') ?>">
                <i class="bx bx-cog fs-5 me-2 text-secondary"></i>
                <span>Paramètres</span>
                <i class="bx bx-chevron-right ms-auto"></i>
            </a>
        </li>
    <?php endif; ?>
       <?php if (admin_view()): ?>
        <li>
            <a class="dropdown-item d-flex align-items-center py-2" 
               href="<?= base_url('Configurations') ?>">
                <i class="bx bx-cog fs-5 me-2 text-secondary"></i>
                <span>Paramètres</span>
                <i class="bx bx-chevron-right ms-auto"></i>
            </a>
        </li>  
          <?php endif; ?>
         <?php if (admin_view()): ?>
        <li>
            <a class="dropdown-item d-flex align-items-center py-2" 
               href="<?= base_url('Contact_Us') ?>">
                <i class="bx bx-bell fs-5 me-2 text-warning"></i>
                <span>Notifications</span>
                <?php if (!empty($message_non_lus) && $message_non_lus > 0): ?>
                    <span class="badge bg-danger rounded-pill ms-auto">
                        <?= (int) $message_non_lus ?>
                    </span>
                <?php endif; ?>
            </a>
        </li>
         <?php endif; ?>

         <?php if (admin_view()): ?>
        <li>
            <a class="dropdown-item d-flex align-items-center py-2" 
               href="<?= base_url('Commandes') ?>">
                <i class="bx bx-cart fs-5 me-2 text-success"></i>
                <span>Mes Commandes</span>
            </a>
        </li>
        <?php endif; ?>
        <li><hr class="dropdown-divider"></li>
        
        <!-- Informations de session -->
        <?php if (admin_view()): ?>
        <li>
            <div class="px-3 py-2">
                <small class="text-muted d-block">
                    <i class="bx bx-chip me-1"></i>Session ID: 
                    <code><?= substr(session_id(), 0, 8) ?>...</code>
                </small>
                <?php if (!empty($last_activity)): ?>
                <small class="text-muted d-block">
                    <i class="bx bx-time me-1"></i>Dernière activité: 
                    <?= date('H:i:s', $last_activity) ?>
                </small>
                <?php endif; ?>
            </div>
        </li>
         <?php endif; ?>
        <li><hr class="dropdown-divider"></li>
        
        <!-- Déconnexion -->
        <li>
            <a class="dropdown-item d-flex align-items-center text-danger py-2" 
               href="<?= base_url('Admin/logout') ?>"
               onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
                <i class="bx bx-log-out-circle fs-5 me-2"></i>
                <span class="fw-bold">Déconnexion</span>
                <i class="bx bx-exit ms-auto"></i>
            </a>
        </li>
    </ul>
</div>
<?php endif; ?>

        </nav>
    </div>

<!-- Modal Profil Utilisateur (avec données de session) -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="profileModalLabel">
                    <i class="bx bx-user-circle me-2"></i>Mon Profil
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?php if (!empty($membre_info)): ?>
                <div class="row">
                    <div class="col-md-4 text-center border-end">
                        <?php 
                        $photo_path = !empty($user_photo) ? 'attachments/Users/'.$user_photo : 'attachments/Users/default-avatar.png';
                        ?>
                        <img src="<?= base_url($photo_path) ?>" 
                             class="rounded-circle img-thumbnail mb-3" 
                             style="width: 150px; height: 150px; object-fit: cover;"
                             onerror="this.src='<?= base_url('attachments/Users/default-avatar.png') ?>'"
                             alt="Photo de profil">
                        <h5 class="mb-1"><?= htmlspecialchars(($user_prenom ?? '') . ' ' . ($user_nom ?? $user_name)) ?></h5>
                        <p class="text-muted mb-2"><?= htmlspecialchars($user_email ?? '-') ?></p>
                        <?php if (!empty($user_role)): ?>
                            <span class="badge bg-info"><?= htmlspecialchars($user_role) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($role_slug)): ?>
                            <span class="badge bg-secondary mt-2"><?= htmlspecialchars($role_slug) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <h6 class="text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Informations personnelles</h6>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <p class="mb-1 text-muted small">Nom complet</p>
                                <p class="fw-bold"><?= htmlspecialchars(($user_prenom ?? '') . ' ' . ($user_nom ?? '')) ?></p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1 text-muted small">Email</p>
                                <p class="fw-bold"><?= htmlspecialchars($user_email ?? '-') ?></p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1 text-muted small">Téléphone</p>
                                <p class="fw-bold"><?= htmlspecialchars($membre_info['telephone'] ?? '-') ?></p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1 text-muted small">Date de naissance</p>
                                <p class="fw-bold">
                                    <?= !empty($membre_info['date_naissance']) ? date('d/m/Y', strtotime($membre_info['date_naissance'])) : '-' ?>
                                </p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1 text-muted small">Genre</p>
                                <p class="fw-bold">
                                    <?php 
                                    $genres = ['M' => 'Masculin', 'F' => 'Féminin', 'Autre' => 'Autre'];
                                    echo $genres[$membre_info['genre'] ?? ''] ?? '-';
                                    ?>
                                </p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1 text-muted small">Type d'utilisateur</p>
                                <p class="fw-bold text-capitalize"><?= htmlspecialchars($membre_info['type_utilisateur'] ?? '-') ?></p>
                            </div>
                            <?php if (!empty($membre_info['nom_entreprise'])): ?>
                            <div class="col-12">
                                <hr>
                                <h6 class="text-primary mb-3"><i class="bx bx-buildings me-2"></i>Informations professionnelles</h6>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1 text-muted small">Entreprise</p>
                                <p class="fw-bold"><?= htmlspecialchars($membre_info['nom_entreprise']) ?></p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1 text-muted small">Secteur d'activité</p>
                                <p class="fw-bold"><?= htmlspecialchars($membre_info['secteur_activite'] ?? '-') ?></p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1 text-muted small">N° Registre Commerce</p>
                                <p class="fw-bold"><?= htmlspecialchars($membre_info['numero_registre_commerce'] ?? '-') ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-6">
                                <p class="mb-1 text-muted small">Compte créé le</p>
                                <p class="fw-bold">
                                    <?= !empty($membre_info['created_at']) ? date('d/m/Y H:i', strtotime($membre_info['created_at'])) : '-' ?>
                                </p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1 text-muted small">Dernière connexion</p>
                                <p class="fw-bold">
                                    <?php 
                                    if (!empty($membre_info['last_login_at'])) {
                                        echo date('d/m/Y H:i', strtotime($membre_info['last_login_at']));
                                    } elseif (!empty($login_time)) {
                                        echo date('d/m/Y H:i', $login_time);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <p class="text-center text-muted">Aucune information disponible.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

</header>







view_medecin  et view_admin








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

/* Dark mode support */
.dark-mode .dropdown-menu {
    background-color: #343a40;
    border-color: #495057;
}
.dark-mode .dropdown-item {
    color: #e9ecef;
}
.dark-mode .dropdown-item:hover {
    background-color: #495057;
}
.dark-mode .text-dark {
    color: #e9ecef !important;
}
.dark-mode .text-muted {
    color: #adb5bd !important;
}
</style>

<script>
// Toggle mode sombre - VERSION CORRIGÉE
const darkModeToggle = document.getElementById('darkModeToggle');
if (darkModeToggle) {
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const icon = this.querySelector('i');
        
        if (document.body.classList.contains('dark-mode')) {
            // Mode sombre activé - icône devient soleil
            icon.classList.remove('bx-moon');
            icon.classList.add('bx-sun');
            localStorage.setItem('darkMode', 'enabled');
        } else {
            // Mode clair activé - icône devient lune
            icon.classList.remove('bx-sun');
            icon.classList.add('bx-moon');
            localStorage.setItem('darkMode', 'disabled');
        }
    });

    // Charger le mode préféré
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
        const icon = document.querySelector('#darkModeToggle i');
        if (icon) {
            icon.classList.remove('bx-moon');
            icon.classList.add('bx-sun');
        }
    } else {
        // Light mode par défaut - s'assurer que l'icône est lune
        const icon = document.querySelector('#darkModeToggle i');
        if (icon) {
            icon.classList.remove('bx-sun');
            icon.classList.add('bx-moon');
        }
        // S'assurer que localStorage est défini
        localStorage.setItem('darkMode', 'disabled');
    }
}

// Gestion des erreurs d'images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[onerror]');
    images.forEach(img => {
        img.onerror = function() {
            this.src = '<?= base_url("assets/frontend/img/logo/urumuri.jpeg") ?>';
        };
    });
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
</script>















