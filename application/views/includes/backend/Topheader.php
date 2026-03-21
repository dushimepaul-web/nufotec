<!--start header -->
<header>
<div class="topbar">
    <nav class="navbar navbar-expand gap-2 align-items-center">
        <div class="mobile-toggle-menu d-flex"><i class='bx bx-menu'></i>
        </div>

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
                        <a class="dropdown-item d-flex align-items-center" href="javascript:;" data-bs-toggle="modal" data-bs-target="#profileModal">
                            <i class="bx bx-user fs-5 me-2"></i>
                            <span>Mon Profil (Détails)</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="<?= base_url('Settings') ?>">
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

<!-- Modal Profil -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
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
                            <?php if (!empty($photo) && file_exists(FCPATH . $photo_path)): ?>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
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