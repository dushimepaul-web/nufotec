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
            // Récupération des données de session
            $idUser = $this->session->userdata('user_id');
            $user_name = $this->session->userdata('username');
            $user_role = $this->session->userdata('role');
            $user_photo = $this->session->userdata('photo');
            $user_email = $this->session->userdata('email');

            $message_non_lus = $this->Model->count('contact_us', ['is_readed' => 0]);

            // Initiales pour l'avatar
            $initials = 'UR';
            if (!empty($user_name)) {
                $initials = strtoupper(substr($user_name, 0, 2));
            }
            
            ?>

            <?php if (!empty($user_name)): ?>
            <div class="user-box dropdown">
                <a class="d-flex align-items-center nav-link dropdown-toggle gap-3 dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="position-relative">
                        <?php 
                        $photo_path = 'attachments/users/' . $user_photo;
                        if (!empty($user_photo) && file_exists(FCPATH . $photo_path)): 
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
                            <?= htmlspecialchars($user_name) ?>
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
                                    <?php if (!empty($user_photo) && file_exists(FCPATH . $photo_path)): ?>
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
                                    <h6 class="mb-0 text-dark"><?= htmlspecialchars($user_name) ?></h6>
                                    <small class="text-muted"><?= !empty($user_email) ? htmlspecialchars($user_email) : 'Non défini' ?></small>
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
                            <?php 
                            $modal_photo_path = 'attachments/membres/' . ($membre_info['image'] ?? $user_photo ?? '');
                            ?>
                            <?php if (!empty($modal_photo_path) && file_exists(FCPATH . $modal_photo_path)): ?>
                                <img src="<?= base_url($modal_photo_path) ?>" 
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
                        <h4 class="mt-3 fw-bold"><?= htmlspecialchars($user_name) ?></h4>
                        <p class="text-muted"><?= !empty($membre_info['profil']) ? htmlspecialchars($membre_info['profil']) : 'Membre' ?></p>
                        
                        <span class="badge bg-<?= (!empty($membre_info['statut']) && $membre_info['statut'] == 'actif') ? 'success' : 'warning' ?> rounded-pill px-3 py-1">
                            <?= !empty($membre_info['statut']) ? ucfirst($membre_info['statut']) : 'Actif' ?>
                        </span>
                    </div>
                    
                    <!-- Informations détaillées -->
                    <div class="col-md-8">
                        <h5 class="border-bottom pb-2 mb-3">Informations Personnelles</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Nom Complet</label>
                                <p class="fw-bold mb-0"><?= !empty($membre_info['nom_complet']) ? htmlspecialchars($membre_info['nom_complet']) : htmlspecialchars($user_name) ?></p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Email</label>
                                <p class="fw-bold mb-0"><?= !empty($membre_info['email']) ? htmlspecialchars($membre_info['email']) : htmlspecialchars($user_email) ?></p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Téléphone</label>
                                <p class="fw-bold mb-0"><?= !empty($membre_info['telephone']) ? htmlspecialchars($membre_info['telephone']) : 'Non renseigné' ?></p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Rôle</label>
                                <p class="fw-bold mb-0"><?= !empty($user_role) ? htmlspecialchars($user_role) : 'Utilisateur' ?></p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Date d'inscription</label>
                                <p class="fw-bold mb-0">
                                    <?php if (!empty($membre_info['date_inscription'])): ?>
                                        <?= date('d/m/Y', strtotime($membre_info['date_inscription'])) ?>
                                    <?php else: ?>
                                        Non disponible
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Adresse</label>
                                <p class="fw-bold mb-0"><?= !empty($membre_info['adresse']) ? htmlspecialchars($membre_info['adresse']) : 'Non renseignée' ?></p>
                            </div>
                            
                            <!-- Réseaux sociaux -->
                            <?php if (!empty($membre_info['facebook']) || !empty($membre_info['linkedln']) || !empty($membre_info['instagram'])): ?>
                            <div class="col-12 mt-4">
                                <h6 class="border-bottom pb-2 mb-3">Réseaux Sociaux</h6>
                                <div class="d-flex gap-2">
                                    <?php if (!empty($membre_info['facebook'])): ?>
                                        <a href="<?= htmlspecialchars($membre_info['facebook']) ?>" 
                                           class="btn btn-outline-primary btn-sm rounded-circle" 
                                           target="_blank" 
                                           title="Facebook">
                                            <i class="bx bxl-facebook"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($membre_info['linkedln'])): ?>
                                        <a href="<?= htmlspecialchars($membre_info['linkedln']) ?>" 
                                           class="btn btn-outline-info btn-sm rounded-circle" 
                                           target="_blank" 
                                           title="LinkedIn">
                                            <i class="bx bxl-linkedin"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($membre_info['instagram'])): ?>
                                        <a href="<?= htmlspecialchars($membre_info['instagram']) ?>" 
                                           class="btn btn-outline-danger btn-sm rounded-circle" 
                                           target="_blank" 
                                           title="Instagram">
                                            <i class="bx bxl-instagram"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($membre_info['youtube'])): ?>
                                        <a href="<?= htmlspecialchars($membre_info['youtube']) ?>" 
                                           class="btn btn-outline-danger btn-sm rounded-circle" 
                                           target="_blank" 
                                           title="YouTube">
                                            <i class="bx bxl-youtube"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
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















