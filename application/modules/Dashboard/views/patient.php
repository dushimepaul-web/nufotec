<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0ea5e9">
    <title>Tableau de Bord Patient - Nufotec</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            900: '#0c4a6e',
                        },
                        medical: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        :root {
            --safe-area-inset-top: env(safe-area-inset-top);
            --safe-area-inset-bottom: env(safe-area-inset-bottom);
            --safe-area-inset-left: env(safe-area-inset-left);
            --safe-area-inset-right: env(safe-area-inset-right);
        }
        
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }
        
        .section-transition {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #0ea5e9 0%, #22c55e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .bottom-nav {
            transition: transform 0.3s ease;
        }
        
        .bottom-nav.hide {
            transform: translateY(100%);
        }
        
        @media (max-width: 768px) {
            .card-hover {
                transition: all 0.2s ease;
            }
            .card-hover:active {
                transform: scale(0.98);
                background-color: #f8fafc;
            }
            button, 
            .nav-item,
            .action-btn {
                min-height: 44px;
            }
            .overflow-y-auto {
                -webkit-overflow-scrolling: touch;
            }
        }
        
        @media (min-width: 1024px) {
            .mobile-only {
                display: none !important;
            }
        }
        
        @media (max-width: 1023px) {
            .desktop-only {
                display: none !important;
            }
        }
        
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">
    
    <!-- Mobile Bottom Navigation -->
    <div class="bottom-nav fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-50 shadow-lg lg:hidden mobile-only" id="bottomNav">
        <div class="flex justify-around items-center px-2 py-2" style="padding-bottom: max(env(safe-area-inset-bottom), 0.5rem);">
            <button onclick="showSection('dashboard')" data-nav="dashboard" class="bottom-nav-item flex flex-col items-center gap-1 px-3 py-2 rounded-xl transition-all text-primary-600">
                <i class="fas fa-home text-xl"></i>
                <span class="text-xs font-medium">Accueil</span>
            </button>
            <button onclick="showSection('consultations')" data-nav="consultations" class="bottom-nav-item flex flex-col items-center gap-1 px-3 py-2 rounded-xl transition-all text-slate-500">
                <i class="fas fa-calendar-check text-xl"></i>
                <span class="text-xs font-medium">RDV</span>
            </button>
            <button onclick="showSection('ordonnances')" data-nav="ordonnances" class="bottom-nav-item flex flex-col items-center gap-1 px-3 py-2 rounded-xl transition-all text-slate-500">
                <i class="fas fa-pills text-xl"></i>
                <span class="text-xs font-medium">Ordo</span>
            </button>
            <button onclick="showSection('messages')" data-nav="messages" class="bottom-nav-item flex flex-col items-center gap-1 px-3 py-2 rounded-xl transition-all text-slate-500 relative">
                <i class="fas fa-comments text-xl"></i>
                <span class="text-xs font-medium">Messages</span>
                <?php if(isset($unread_messages) && count($unread_messages) > 0): ?>
                    <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                <?php endif; ?>
            </button>
            <button onclick="showSection('profil')" data-nav="profil" class="bottom-nav-item flex flex-col items-center gap-1 px-3 py-2 rounded-xl transition-all text-slate-500">
                <i class="fas fa-user text-xl"></i>
                <span class="text-xs font-medium">Profil</span>
            </button>
        </div>
    </div>

    <!-- Layout Principal -->
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar Desktop -->
        <aside class="desktop-only w-72 bg-white border-r border-slate-200 flex flex-col shadow-lg z-20">
            <div class="p-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-medical-500 flex items-center justify-center text-white font-bold text-sm overflow-hidden">
                        <?php 
                        $logo_path = isset($settings['site_logo']) ? $settings['site_logo'] : 'logo.png';
                        $logo_full_path = FCPATH . 'attachments/Configurations/' . $logo_path;
                        
                        if (file_exists($logo_full_path) && !empty($logo_path)): 
                        ?>
                            <img src="<?= base_url('attachments/Configurations/' . $logo_path) ?>" 
                                 alt="Logo Nufotec" 
                                 class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fas fa-microchip"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="font-bold text-xl text-slate-800"><?= htmlspecialchars(isset($settings['site_name']) ? $settings['site_name'] : 'NUFOTEC BURUNDI') ?></h1>
                        <p class="text-xs text-slate-500">Espace Patient</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-gradient-to-r from-primary-50 to-medical-50 m-4 rounded-2xl">
                <div class="flex items-center gap-3">
                    <img src="<?= base_url('attachments/Users/' . (isset($user->photo) && $user->photo ? $user->photo : 'default-avatar.png')) ?>" 
                         alt="Profil" 
                         class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm"
                         onerror="this.src='<?= base_url('assets/images/default-avatar.png') ?>'">
                    <div class="overflow-hidden">
                        <p class="font-semibold text-sm text-slate-800 truncate"><?= htmlspecialchars(isset($user->prenom) ? $user->prenom . ' ' . ($user->nom ?? '') : 'Utilisateur') ?></p>
                        <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($user->email ?? '') ?></p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 space-y-1">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 px-3 mt-4">Menu Principal</p>
                
                <button onclick="showSection('dashboard')" id="nav-dashboard" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 bg-primary-50 text-primary-700">
                    <i class="fas fa-home w-5"></i>
                    <span>Tableau de Bord</span>
                </button>

                <button onclick="showSection('consultations')" id="nav-consultations" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all duration-200">
                    <i class="fas fa-calendar-check w-5"></i>
                    <span>Mes Consultations</span>
                    <?php if(isset($upcoming_consultations) && count($upcoming_consultations) > 0): ?>
                        <span class="ml-auto bg-medical-500 text-white text-xs px-2 py-0.5 rounded-full"><?= count($upcoming_consultations) ?></span>
                    <?php endif; ?>
                </button>

                <button onclick="showSection('ordonnances')" id="nav-ordonnances" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all duration-200">
                    <i class="fas fa-pills w-5"></i>
                    <span>Ordonnances</span>
                    <?php if(isset($stats['active_prescriptions']) && $stats['active_prescriptions'] > 0): ?>
                        <span class="ml-auto bg-amber-500 text-white text-xs px-2 py-0.5 rounded-full"><?= $stats['active_prescriptions'] ?></span>
                    <?php endif; ?>
                </button>

                <button onclick="showSection('documents')" id="nav-documents" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all duration-200">
                    <i class="fas fa-file-medical w-5"></i>
                    <span>Documents</span>
                </button>

                <button onclick="showSection('paiements')" id="nav-paiements" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all duration-200">
                    <i class="fas fa-credit-card w-5"></i>
                    <span>Paiements</span>
                </button>

                <button onclick="showSection('messages')" id="nav-messages" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all duration-200">
                    <i class="fas fa-comments w-5"></i>
                    <span>Messages</span>
                    <?php if(isset($unread_messages) && count($unread_messages) > 0): ?>
                        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?= count($unread_messages) ?></span>
                    <?php endif; ?>
                </button>

                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 px-3 mt-6">Paramètres</p>

                <button onclick="showSection('profil')" id="nav-profil" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all duration-200">
                    <i class="fas fa-user-cog w-5"></i>
                    <span>Mon Profil</span>
                </button>

                <a href="<?= base_url('Auth/logout') ?>" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 transition-all duration-200 mt-2">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span>Déconnexion</span>
                </a>
            </nav>

            <div class="p-4 border-t border-slate-100">
                <div class="bg-gradient-to-r from-primary-500 to-medical-500 rounded-xl p-4 text-white">
                    <p class="text-xs font-medium opacity-90">Besoin d'aide ?</p>
                    <p class="text-sm font-semibold mt-1">Support 24/7</p>
                    <a href="<?= base_url('Home/Contact') ?>" class="inline-block mt-2 text-xs bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-lg transition-colors">
                        Contacter
                    </a>
                </div>
            </div>
        </aside>

        <!-- Contenu Principal -->
        <main class="flex-1 overflow-hidden flex flex-col bg-slate-50">
            
            <!-- Header Mobile -->
            <header class="mobile-only bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm" style="padding-top: env(safe-area-inset-top);">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-medical-500 flex items-center justify-center text-white font-bold text-sm overflow-hidden">
                            <?php if (file_exists($logo_full_path) && !empty($logo_path)): ?>
                                <img src="<?= base_url('attachments/Configurations/' . $logo_path) ?>" alt="Logo" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-microchip"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h1 class="font-bold text-sm text-slate-800"><?= htmlspecialchars(isset($settings['site_name']) ? $settings['site_name'] : 'NUFOTEC BURUNDI') ?></h1>
                            <p class="text-[10px] text-slate-500">Espace Patient</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button class="relative p-2" onclick="toggleNotifications()">
                            <i class="fas fa-bell text-slate-600"></i>
                            <?php if(isset($notifications) && count($notifications) > 0): ?>
                                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                            <?php endif; ?>
                        </button>
                        <button onclick="toggleMobileMenu()" class="p-2">
                            <i class="fas fa-bars text-slate-600 text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <div class="px-4 pb-3">
                    <h2 id="page-title-mobile" class="text-xl font-bold text-slate-800">Tableau de Bord</h2>
                    <p id="current-date-mobile" class="text-xs text-slate-500 mt-1"></p>
                </div>
            </header>
            
            <!-- Header Desktop -->
            <header class="desktop-only h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shadow-sm">
                <div class="flex items-center gap-4">
                    <h2 id="page-title" class="text-xl font-bold text-slate-800">Tableau de Bord</h2>
                    <span class="text-slate-400">|</span>
                    <p class="text-sm text-slate-500" id="current-date"></p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative">
                        <input type="text" placeholder="Rechercher..." 
                               class="pl-10 pr-4 py-2 bg-slate-100 border-0 rounded-xl text-sm w-64 focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>

                    <button class="relative p-2 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors" onclick="toggleNotifications()">
                        <i class="fas fa-bell text-lg"></i>
                        <?php if(isset($notifications) && count($notifications) > 0): ?>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        <?php endif; ?>
                    </button>

                    <a href="<?= base_url('Medicins') ?>" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        <span>Nouvelle Consultation</span>
                    </a>
                </div>
            </header>

            <!-- Zone de Contenu Scrollable -->
            <div class="flex-1 overflow-y-auto" id="main-content" style="padding-bottom: max(70px, env(safe-area-inset-bottom));">
                <div class="p-4 lg:p-8">
                    
                    <!-- SECTION: DASHBOARD -->
                    <section id="section-dashboard" class="section-content section-transition">
                        <!-- Stats Cards -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6 mb-6 lg:mb-8">
                            <div class="bg-white rounded-xl lg:rounded-2xl p-4 lg:p-6 shadow-sm border border-slate-100 card-hover">
                                <div class="flex items-center justify-between mb-2 lg:mb-4">
                                    <div class="w-8 h-8 lg:w-12 lg:h-12 bg-primary-50 rounded-lg lg:rounded-xl flex items-center justify-center text-primary-600">
                                        <i class="fas fa-calendar-check text-sm lg:text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-xl lg:text-2xl font-bold text-slate-800"><?= isset($stats['total_consultations']) ? $stats['total_consultations'] : 0 ?></p>
                                <p class="text-xs lg:text-sm text-slate-500">Consultations</p>
                            </div>

                            <div class="bg-white rounded-xl lg:rounded-2xl p-4 lg:p-6 shadow-sm border border-slate-100 card-hover">
                                <div class="flex items-center justify-between mb-2 lg:mb-4">
                                    <div class="w-8 h-8 lg:w-12 lg:h-12 bg-amber-50 rounded-lg lg:rounded-xl flex items-center justify-center text-amber-600">
                                        <i class="fas fa-clock text-sm lg:text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-xl lg:text-2xl font-bold text-slate-800"><?= isset($stats['upcoming_appointments']) ? $stats['upcoming_appointments'] : 0 ?></p>
                                <p class="text-xs lg:text-sm text-slate-500">Rendez-vous</p>
                            </div>

                            <div class="bg-white rounded-xl lg:rounded-2xl p-4 lg:p-6 shadow-sm border border-slate-100 card-hover">
                                <div class="flex items-center justify-between mb-2 lg:mb-4">
                                    <div class="w-8 h-8 lg:w-12 lg:h-12 bg-medical-50 rounded-lg lg:rounded-xl flex items-center justify-center text-medical-600">
                                        <i class="fas fa-pills text-sm lg:text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-xl lg:text-2xl font-bold text-slate-800"><?= isset($stats['active_prescriptions']) ? $stats['active_prescriptions'] : 0 ?></p>
                                <p class="text-xs lg:text-sm text-slate-500">Ordonnances</p>
                            </div>

                            <div class="bg-white rounded-xl lg:rounded-2xl p-4 lg:p-6 shadow-sm border border-slate-100 card-hover">
                                <div class="flex items-center justify-between mb-2 lg:mb-4">
                                    <div class="w-8 h-8 lg:w-12 lg:h-12 bg-purple-50 rounded-lg lg:rounded-xl flex items-center justify-center text-purple-600">
                                        <i class="fas fa-heartbeat text-sm lg:text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-xl lg:text-2xl font-bold text-slate-800"><?= isset($stats['health_score']) ? $stats['health_score'] : 85 ?>/100</p>
                                <p class="text-xs lg:text-sm text-slate-500">Score santé</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                            <!-- Prochains RDV -->
                            <div class="lg:col-span-2 bg-white rounded-xl lg:rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                                <div class="p-4 lg:p-6 border-b border-slate-100 flex items-center justify-between">
                                    <h3 class="font-bold text-base lg:text-lg text-slate-800">Prochains Rendez-vous</h3>
                                    <button onclick="showSection('consultations')" class="text-primary-600 text-xs lg:text-sm font-medium hover:underline">Voir tout</button>
                                </div>
                                <div class="p-4 lg:p-6">
                                    <?php if(empty($upcoming_consultations)): ?>
                                        <div class="text-center py-8 text-slate-400">
                                            <i class="fas fa-calendar-plus text-3xl lg:text-4xl mb-3"></i>
                                            <p class="text-sm">Aucun rendez-vous à venir</p>
                                            <a href="<?= base_url('Medicins') ?>" class="text-primary-600 font-medium mt-2 inline-block text-sm">Prendre rendez-vous</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="space-y-3 lg:space-y-4">
                                            <?php foreach(array_slice($upcoming_consultations, 0, 3) as $consultation): ?>
                                                <div class="flex items-center gap-3 lg:gap-4 p-3 lg:p-4 bg-slate-50 rounded-xl card-hover">
                                                    <div class="w-12 h-12 lg:w-14 lg:h-14 bg-primary-100 rounded-xl flex flex-col items-center justify-center text-primary-700">
                                                        <span class="text-[10px] lg:text-xs font-bold uppercase"><?= date('M', strtotime($consultation->date_souhaitee ?? $consultation->created_at ?? 'now')) ?></span>
                                                        <span class="text-base lg:text-lg font-bold"><?= date('d', strtotime($consultation->date_souhaitee ?? $consultation->created_at ?? 'now')) ?></span>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="font-semibold text-sm lg:text-base text-slate-800">
                                                            <?= htmlspecialchars(($consultation->medecin_prenom ?? '') . ' ' . ($consultation->medecin_nom ?? 'Docteur')) ?>
                                                        </p>
                                                        <p class="text-xs lg:text-sm text-slate-500">
                                                            <?= htmlspecialchars($consultation->specialite ?? 'Généraliste') ?> • 
                                                            <?= date('H:i', strtotime($consultation->date_souhaitee ?? $consultation->created_at ?? 'now')) ?>
                                                        </p>
                                                    </div>
                                                    <span class="px-2 py-1 lg:px-3 lg:py-1 rounded-full text-[10px] lg:text-xs font-medium 
                                                        <?= ($consultation->statut ?? 'confirmee') == 'confirmee' ? 'bg-medical-100 text-medical-700' : 'bg-amber-100 text-amber-700' ?>">
                                                        <?= ($consultation->statut ?? 'confirmee') == 'confirmee' ? 'Confirmé' : 'En attente' ?>
                                                    </span>
                                                    <?php if(($consultation->statut ?? '') == 'confirmee' && !empty($consultation->room_id ?? '')): ?>
                                                        <a href="<?= base_url('joinconsultation/index?room=' . ($consultation->room_id ?? '') . '&user=' . ($this->session->userdata('user_id') ?? '')) ?>" 
                                                           target="_blank" 
                                                           class="bg-primary-600 text-white px-3 py-1.5 lg:px-4 lg:py-2 rounded-lg text-xs lg:text-sm font-medium hover:bg-primary-700 transition-colors whitespace-nowrap">
                                                            <i class="fas fa-video mr-1"></i> Rejoindre
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Messages non lus -->
                            <div class="space-y-6">
                                <div class="bg-white rounded-xl lg:rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                                    <div class="p-4 lg:p-6 border-b border-slate-100">
                                        <h3 class="font-bold text-base lg:text-lg text-slate-800 flex items-center gap-2">
                                            Messages
                                            <?php if(isset($unread_messages) && count($unread_messages) > 0): ?>
                                                <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?= count($unread_messages) ?></span>
                                            <?php endif; ?>
                                        </h3>
                                    </div>
                                    <div class="p-4">
                                        <?php if(empty($unread_messages)): ?>
                                            <p class="text-sm text-slate-400 text-center py-4">Aucun nouveau message</p>
                                        <?php else: ?>
                                            <div class="space-y-3">
                                                <?php foreach(array_slice($unread_messages, 0, 3) as $msg): ?>
                                                    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl card-hover cursor-pointer" onclick="markAsRead(<?= $msg->id ?? 0 ?>)">
                                                        <img src="<?= base_url('attachments/Users/' . (($msg->sender_photo ?? 'default-avatar.png'))) ?>" 
                                                             class="w-10 h-10 rounded-full object-cover"
                                                             onerror="this.src='<?= base_url('assets/images/default-avatar.png') ?>'">
                                                        <div class="flex-1 min-w-0">
                                                            <p class="font-medium text-sm text-slate-800 truncate">
                                                                <?= htmlspecialchars(($msg->sender_prenom ?? '') . ' ' . ($msg->sender_nom ?? '')) ?>
                                                            </p>
                                                            <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($msg->message ?? '') ?></p>
                                                            <p class="text-xs text-slate-400 mt-1"><?= timeAgo($msg->created_at ?? date('Y-m-d H:i:s')) ?></p>
                                                        </div>
                                                        <span class="w-2 h-2 bg-primary-500 rounded-full mt-2"></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION: CONSULTATIONS -->
                    <section id="section-consultations" class="section-content hidden section-transition">
                        <div class="bg-white rounded-xl lg:rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="p-4 lg:p-6 border-b border-slate-100">
                                <h3 class="font-bold text-xl text-slate-800">Historique des Consultations</h3>
                                <p class="text-sm text-slate-500 mt-1">Toutes vos consultations passées et à venir</p>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[600px] lg:min-w-full">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-semibold text-slate-500 uppercase">Médecin</th>
                                            <th class="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                                            <th class="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
                                            <th class="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-semibold text-slate-500 uppercase">Statut</th>
                                            <th class="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-semibold text-slate-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php if (!empty($all_consultations)): ?>
                                            <?php foreach($all_consultations as $consultation): ?>
                                                <tr class="hover:bg-slate-50 transition-colors consultation-row" data-status="<?= $consultation->statut ?? '' ?>">
                                                    <td class="px-4 lg:px-6 py-3 lg:py-4">
                                                        <div class="flex items-center gap-2 lg:gap-3">
                                                            <img src="<?= base_url('attachments/Users/' . (($consultation->medecin_photo ?? 'default-avatar.png'))) ?>" 
                                                                 class="w-8 h-8 lg:w-10 lg:h-10 rounded-full object-cover"
                                                                 onerror="this.src='<?= base_url('assets/images/default-avatar.png') ?>'">
                                                            <div>
                                                                <p class="font-medium text-sm text-slate-800"><?= htmlspecialchars(($consultation->medecin_prenom ?? '') . ' ' . ($consultation->medecin_nom ?? '')) ?></p>
                                                                <p class="text-xs text-slate-500"><?= htmlspecialchars($consultation->specialite ?? 'Généraliste') ?></p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 lg:px-6 py-3 lg:py-4">
                                                        <p class="text-sm text-slate-800"><?= date('d/m/Y', strtotime($consultation->date_souhaitee ?? $consultation->created_at ?? 'now')) ?></p>
                                                        <p class="text-xs text-slate-500"><?= date('H:i', strtotime($consultation->date_souhaitee ?? $consultation->created_at ?? 'now')) ?></p>
                                                    </td>
                                                    <td class="px-4 lg:px-6 py-3 lg:py-4">
                                                        <span class="inline-flex items-center gap-1 px-2 py-1 lg:px-3 lg:py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                                            <i class="fas fa-<?= ($consultation->type ?? 'video') == 'video' ? 'video' : (($consultation->type ?? '') == 'telephone' ? 'phone' : 'hospital') ?> text-xs"></i>
                                                            <?= ucfirst($consultation->type ?? 'Vidéo') ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-4 lg:px-6 py-3 lg:py-4">
                                                        <span class="px-2 py-1 lg:px-3 lg:py-1 rounded-full text-xs font-medium 
                                                            <?= ($consultation->statut ?? 'terminee') == 'terminee' ? 'bg-medical-100 text-medical-700' : 
                                                               (($consultation->statut ?? '') == 'confirmee' ? 'bg-primary-100 text-primary-700' : 
                                                               (($consultation->statut ?? '') == 'annulee' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700')) ?>">
                                                            <?= ucfirst(str_replace('_', ' ', $consultation->statut ?? 'Terminée')) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-4 lg:px-6 py-3 lg:py-4">
                                                        <div class="flex items-center gap-2">
                                                            <?php if (!empty($consultation->room_id ?? '') && in_array($consultation->statut ?? '', ['confirmee', 'en_cours'])): ?>
                                                                <a href="<?= base_url('Joinconsultation/index?room=' . urlencode($consultation->room_id) . '&user=' . ($this->session->userdata('user_id') ?? '')) ?>" 
                                                                   target="_blank" 
                                                                   class="inline-flex items-center gap-1 lg:gap-2 px-2 py-1 lg:px-4 lg:py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs lg:text-sm font-medium rounded-lg transition-all shadow-sm">
                                                                    <i class="fas fa-video text-xs"></i>
                                                                    <span>Rejoindre</span>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="px-4 lg:px-6 py-8 text-center text-slate-500">
                                                    <i class="fas fa-calendar-xmark text-3xl mb-2"></i>
                                                    <p>Aucune consultation trouvée</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION: ORDONNANCES -->
                    <section id="section-ordonnances" class="section-content hidden section-transition">
                        <div class="space-y-4 lg:space-y-6">
                            <?php if(empty($recent_prescriptions)): ?>
                                <div class="bg-white rounded-xl lg:rounded-2xl shadow-sm border border-slate-100 p-8 lg:p-12 text-center">
                                    <i class="fas fa-prescription-bottle-alt text-4xl lg:text-5xl text-slate-300 mb-4"></i>
                                    <p class="text-slate-500">Aucune ordonnance disponible</p>
                                </div>
                            <?php else: ?>
                                <?php foreach($recent_prescriptions as $prescription): ?>
                                    <div class="bg-white rounded-xl lg:rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                                        <div class="p-4 lg:p-6">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 lg:w-12 lg:h-12 bg-medical-50 rounded-xl flex items-center justify-center text-medical-600">
                                                        <i class="fas fa-pills text-lg lg:text-xl"></i>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-slate-800"><?= htmlspecialchars($prescription->medicament ?? 'Médicament') ?></p>
                                                        <p class="text-xs lg:text-sm text-slate-500"><?= htmlspecialchars(($prescription->medecin_prenom ?? '') . ' ' . ($prescription->medecin_nom ?? '')) ?></p>
                                                    </div>
                                                </div>
                                                <span class="px-2 py-1 lg:px-3 lg:py-1 rounded-full text-xs font-medium <?= ($prescription->is_active ?? false) ? 'bg-medical-100 text-medical-700' : 'bg-slate-100 text-slate-600' ?>">
                                                    <?= ($prescription->is_active ?? false) ? 'Active' : 'Terminée' ?>
                                                </span>
                                            </div>
                                            
                                            <?php if(!empty($prescription->dosage ?? '')): ?>
                                                <div class="bg-slate-50 rounded-lg p-3 mb-3">
                                                    <p class="text-sm text-slate-600"><span class="font-medium">Dosage:</span> <?= htmlspecialchars($prescription->dosage) ?></p>
                                                    <?php if(!empty($prescription->instructions ?? '')): ?>
                                                        <p class="text-sm text-slate-600 mt-1"><span class="font-medium">Instructions:</span> <?= htmlspecialchars($prescription->instructions) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                                                <p class="text-xs text-slate-400">
                                                    <i class="far fa-calendar mr-1"></i>
                                                    <?= date('d/m/Y', strtotime($prescription->consultation_date ?? $prescription->created_at ?? 'now')) ?>
                                                </p>
                                                <div class="flex gap-2">
                                                    <button onclick="printPrescription('<?= $prescription->id ?? '' ?>')" 
                                                            class="text-xs lg:text-sm text-slate-600 hover:text-slate-700 font-medium">
                                                        <i class="fas fa-print mr-1"></i> Imprimer
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- SECTION: DOCUMENTS -->
                    <section id="section-documents" class="section-content hidden section-transition">
                        <div class="bg-white rounded-xl lg:rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="p-4 lg:p-6 border-b border-slate-100">
                                <h3 class="font-bold text-xl text-slate-800">Documents Médicaux</h3>
                                <p class="text-sm text-slate-500 mt-1">Examens, ordonnances et preuves de paiement</p>
                            </div>
                            
                            <div class="p-4 lg:p-6">
                                <?php if(empty($medical_documents)): ?>
                                    <div class="text-center py-8 lg:py-12 text-slate-400">
                                        <i class="fas fa-folder-open text-3xl lg:text-5xl mb-4"></i>
                                        <p>Aucun document disponible</p>
                                    </div>
                                <?php else: ?>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-4">
                                        <?php foreach($medical_documents as $doc): ?>
                                            <div class="document-card group border border-slate-200 rounded-xl p-3 lg:p-4 hover:shadow-lg transition-all cursor-pointer bg-white card-hover" 
                                                 onclick="openDocument('<?= $doc->view_url ?? '#' ?>', '<?= $doc->download_url ?? '#' ?>')">
                                                <div class="flex items-start justify-between mb-2 lg:mb-3">
                                                    <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl flex items-center justify-center text-xl
                                                        <?= ($doc->type ?? '') == 'examen' ? 'bg-purple-50 text-purple-600' : 
                                                           (($doc->type ?? '') == 'ordonnance' ? 'bg-medical-50 text-medical-600' : 
                                                           (($doc->type ?? '') == 'paiement' ? 'bg-amber-50 text-amber-600' : 'bg-slate-50 text-slate-600')) ?>">
                                                        <i class="fas fa-<?= ($doc->type ?? '') == 'examen' ? 'microscope' : (($doc->type ?? '') == 'ordonnance' ? 'file-prescription' : (($doc->type ?? '') == 'paiement' ? 'receipt' : 'file')) ?>"></i>
                                                    </div>
                                                    <button class="opacity-0 group-hover:opacity-100 transition-opacity p-1" onclick="event.stopPropagation(); downloadDoc('<?= $doc->download_url ?? '#' ?>')">
                                                        <i class="fas fa-download text-slate-400"></i>
                                                    </button>
                                                </div>
                                                <h4 class="font-medium text-sm text-slate-800 mb-1 truncate"><?= htmlspecialchars($doc->original_name ?? 'Document') ?></h4>
                                                <p class="text-[10px] lg:text-xs text-slate-500"><?= ucfirst($doc->type ?? 'fichier') ?> • <?= date('d/m/Y', strtotime($doc->created_at ?? 'now')) ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION: PAIEMENTS -->
                    <section id="section-paiements" class="section-content hidden section-transition">
                        <div class="bg-white rounded-xl lg:rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="p-4 lg:p-6 border-b border-slate-100">
                                <h3 class="font-bold text-xl text-slate-800">Historique des Paiements</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[600px] lg:min-w-full">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-semibold text-slate-500 uppercase">N° Consult</th>
                                            <th class="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-semibold text-slate-500 uppercase">Médecin</th>
                                            <th class="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                                            <th class="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-semibold text-slate-500 uppercase">Montant</th>
                                            <th class="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-semibold text-slate-500 uppercase">Reçu</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php if(empty($payment_history)): ?>
                                            <tr>
                                                <td colspan="5" class="px-4 lg:px-6 py-8 lg:py-12 text-center text-slate-400">
                                                    <i class="fas fa-receipt text-3xl lg:text-4xl mb-3"></i>
                                                    <p>Aucun paiement effectué</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach($payment_history as $payment): ?>
                                                <tr class="hover:bg-slate-50 transition-colors">
                                                    <td class="px-4 lg:px-6 py-3 lg:py-4 font-medium text-sm text-slate-800"><?= $payment->numero_consultation ?? 'N/A' ?></td>
                                                    <td class="px-4 lg:px-6 py-3 lg:py-4 text-sm"><?= htmlspecialchars(($payment->medecin_prenom ?? '') . ' ' . ($payment->medecin_nom ?? '')) ?></td>
                                                    <td class="px-4 lg:px-6 py-3 lg:py-4 text-sm text-slate-600"><?= date('d/m/Y', strtotime($payment->payment_date ?? $payment->created_at ?? 'now')) ?></td>
                                                    <td class="px-4 lg:px-6 py-3 lg:py-4 font-semibold text-sm text-slate-800"><?= number_format($payment->prix_ttc ?? 0, 2) ?> <?= $payment->devise ?? 'BIF' ?></td>
                                                    <td class="px-4 lg:px-6 py-3 lg:py-4">
                                                        <button onclick="downloadReceipt(<?= $payment->id ?? 0 ?>)" class="text-primary-600 hover:text-primary-700">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION: MESSAGES -->
                    <section id="section-messages" class="section-content hidden section-transition">
                        <div class="bg-white rounded-xl lg:rounded-2xl shadow-sm border border-slate-100 overflow-hidden h-[calc(100vh-200px)] lg:h-[calc(100vh-250px)] flex flex-col">
                            <div class="flex flex-col lg:flex-row h-full">
                                <div id="conversations-list-container" class="w-full lg:w-80 border-r border-slate-100 flex flex-col h-full lg:block">
                                    <div class="p-4 border-b border-slate-100">
                                        <h3 class="font-bold text-lg text-slate-800">Conversations</h3>
                                    </div>
                                    <div class="flex-1 overflow-y-auto p-4 space-y-2" id="conversations-list">
                                        <p class="text-center text-slate-400 py-4">Aucune conversation</p>
                                    </div>
                                </div>
                                
                                <div id="chat-container" class="flex-1 flex flex-col h-full lg:block hidden">
                                    <div class="p-4 border-b border-slate-100 flex items-center justify-between" id="chat-header">
                                        <div class="flex items-center gap-3">
                                            <button id="back-to-conversations" class="lg:hidden p-2 -ml-2 text-slate-500">
                                                <i class="fas fa-arrow-left"></i>
                                            </button>
                                            <div>
                                                <p class="text-slate-500">Sélectionnez une conversation</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
                                        <div class="flex items-center justify-center h-full text-slate-400">
                                            <p>Aucune conversation sélectionnée</p>
                                        </div>
                                    </div>
                                    <div class="p-4 border-t border-slate-100" id="chat-input" style="display: none;">
                                        <div class="flex gap-2">
                                            <input type="text" id="message-input" placeholder="Écrivez votre message..." 
                                                   class="flex-1 px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                            <button id="send-message-btn" class="bg-primary-600 text-white px-4 py-2 rounded-xl hover:bg-primary-700 transition-colors">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION: PROFIL -->
                    <section id="section-profil" class="section-content hidden section-transition">
                        <div class="max-w-3xl mx-auto">
                            <div class="bg-white rounded-xl lg:rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                                <div class="p-4 lg:p-6 border-b border-slate-100 bg-gradient-to-r from-primary-50 to-medical-50">
                                    <h3 class="font-bold text-xl text-slate-800">Mon Profil</h3>
                                    <p class="text-sm text-slate-600 mt-1">Gérez vos informations personnelles</p>
                                </div>
                                
                                <form action="<?= base_url('Dashboard/PatientDashboard/update_profile') ?>" method="POST" enctype="multipart/form-data" class="p-4 lg:p-6 space-y-6">
                                    <div class="flex flex-col sm:flex-row items-center gap-6">
                                        <div class="relative">
                                            <img src="<?= base_url('attachments/Users/' . (isset($user->photo) && $user->photo ? $user->photo : 'default-avatar.png')) ?>" 
                                                 alt="Profil" 
                                                 class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg"
                                                 id="preview-photo"
                                                 onerror="this.src='<?= base_url('assets/images/default-avatar.png') ?>'">
                                            <label class="absolute bottom-0 right-0 w-8 h-8 bg-primary-600 text-white rounded-full flex items-center justify-center cursor-pointer hover:bg-primary-700 transition-colors shadow-md">
                                                <i class="fas fa-camera text-sm"></i>
                                                <input type="file" name="photo" accept="image/*" class="hidden" onchange="previewImage(this)">
                                            </label>
                                        </div>
                                        <div class="text-center sm:text-left">
                                            <h4 class="font-semibold text-slate-800">Photo de profil</h4>
                                            <p class="text-sm text-slate-500">JPG, PNG ou GIF. Max 2MB.</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Prénom</label>
                                            <input type="text" name="prenom" value="<?= htmlspecialchars($user->prenom ?? '') ?>" required
                                                   class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Nom</label>
                                            <input type="text" name="nom" value="<?= htmlspecialchars($user->nom ?? '') ?>" required
                                                   class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                                            <input type="email" name="email" value="<?= htmlspecialchars($user->email ?? '') ?>" required
                                                   class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Téléphone</label>
                                            <input type="tel" name="telephone" value="<?= htmlspecialchars($user->telephone ?? '') ?>"
                                                   class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Date de naissance</label>
                                            <input type="date" name="date_naissance" value="<?= $user->date_naissance ?? '' ?>"
                                                   class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Genre</label>
                                            <select name="genre" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                                <option value="">Non spécifié</option>
                                                <option value="M" <?= ($user->genre ?? '') == 'M' ? 'selected' : '' ?>>Masculin</option>
                                                <option value="F" <?= ($user->genre ?? '') == 'F' ? 'selected' : '' ?>>Féminin</option>
                                                <option value="Autre" <?= ($user->genre ?? '') == 'Autre' ? 'selected' : '' ?>>Autre</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="border-t border-slate-100 pt-6">
                                        <h4 class="font-semibold text-slate-800 mb-4">Changer le mot de passe</h4>
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-2">Mot de passe actuel</label>
                                                <input type="password" name="current_password"
                                                       class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 mb-2">Nouveau mot de passe</label>
                                                    <input type="password" name="new_password" minlength="8"
                                                           class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 mb-2">Confirmer</label>
                                                    <input type="password" name="confirm_password"
                                                           class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-6 border-t border-slate-100">
                                        <button type="button" onclick="showSection('dashboard')" class="px-6 py-2 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50 transition-colors">
                                            Annuler
                                        </button>
                                        <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors font-medium">
                                            <i class="fas fa-save mr-2"></i>Enregistrer
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>

                </div>
            </div>
        </main>
    </div>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="fixed inset-0 bg-black/50 z-50 hidden lg:hidden" onclick="toggleMobileMenu()">
        <div class="absolute right-0 top-0 bottom-0 w-64 bg-white shadow-xl" onclick="event.stopPropagation()">
            <div class="p-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <img src="<?= base_url('attachments/Users/' . (isset($user->photo) && $user->photo ? $user->photo : 'default-avatar.png')) ?>" 
                         class="w-12 h-12 rounded-full object-cover"
                         onerror="this.src='<?= base_url('assets/images/default-avatar.png') ?>'">
                    <div>
                        <p class="font-semibold text-slate-800"><?= htmlspecialchars(($user->prenom ?? '') . ' ' . ($user->nom ?? '')) ?></p>
                        <p class="text-xs text-slate-500"><?= htmlspecialchars($user->email ?? '') ?></p>
                    </div>
                </div>
            </div>
            <div class="p-4 space-y-2">
                <button onclick="showSection('dashboard'); toggleMobileMenu()" class="w-full text-left px-4 py-3 rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fas fa-home w-5 mr-3"></i> Tableau de Bord
                </button>
                <button onclick="showSection('consultations'); toggleMobileMenu()" class="w-full text-left px-4 py-3 rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fas fa-calendar-check w-5 mr-3"></i> Consultations
                </button>
                <button onclick="showSection('ordonnances'); toggleMobileMenu()" class="w-full text-left px-4 py-3 rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fas fa-pills w-5 mr-3"></i> Ordonnances
                </button>
                <button onclick="showSection('documents'); toggleMobileMenu()" class="w-full text-left px-4 py-3 rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fas fa-file-medical w-5 mr-3"></i> Documents
                </button>
                <button onclick="showSection('paiements'); toggleMobileMenu()" class="w-full text-left px-4 py-3 rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fas fa-credit-card w-5 mr-3"></i> Paiements
                </button>
                <button onclick="showSection('messages'); toggleMobileMenu()" class="w-full text-left px-4 py-3 rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fas fa-comments w-5 mr-3"></i> Messages
                </button>
                <button onclick="showSection('profil'); toggleMobileMenu()" class="w-full text-left px-4 py-3 rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fas fa-user-cog w-5 mr-3"></i> Mon Profil
                </button>
                <a href="<?= base_url('Auth/logout') ?>" class="w-full text-left px-4 py-3 rounded-xl hover:bg-red-50 transition-colors text-red-600 block">
                    <i class="fas fa-sign-out-alt w-5 mr-3"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>

    <!-- Modal pour visualiser les documents -->
    <div id="doc-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-lg" id="modal-title">Document</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="flex-1 overflow-auto p-4 bg-slate-100" id="modal-content">
            </div>
            <div class="p-4 border-t border-slate-100 flex justify-end gap-2">
                <a id="modal-download" href="#" class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                    <i class="fas fa-download mr-2"></i>Télécharger
                </a>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed bottom-20 lg:bottom-4 right-4 z-50 space-y-2"></div>

    <script>
        // Données PHP injectées dans JavaScript
        const userData = {
            id: <?= $user->id ?? 0 ?>,
            name: "<?= htmlspecialchars(($user->prenom ?? '') . ' ' . ($user->nom ?? '')) ?>"
        };

        // Variables pour le scroll
        let lastScrollTop = 0;
        let bottomNav = document.getElementById('bottomNav');
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            updateDate();
            setInterval(updateDate, 60000);
            initScrollBehavior();
        });

        function initScrollBehavior() {
            const content = document.getElementById('main-content');
            if (!content) return;
            
            content.addEventListener('scroll', function() {
                const scrollTop = this.scrollTop;
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    if (bottomNav) bottomNav.classList.add('hide');
                } else {
                    if (bottomNav) bottomNav.classList.remove('hide');
                }
                lastScrollTop = scrollTop;
            });
        }

        function showSection(sectionName) {
            document.querySelectorAll('.section-content').forEach(section => {
                section.classList.add('hidden');
            });
            
            const targetSection = document.getElementById('section-' + sectionName);
            if(targetSection) {
                targetSection.classList.remove('hidden');
                targetSection.classList.add('section-transition');
            }
            
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('bg-primary-50', 'text-primary-700');
                item.classList.add('text-slate-600');
            });
            
            const activeNav = document.getElementById('nav-' + sectionName);
            if(activeNav) {
                activeNav.classList.remove('text-slate-600');
                activeNav.classList.add('bg-primary-50', 'text-primary-700');
            }
            
            document.querySelectorAll('.bottom-nav-item').forEach(item => {
                item.classList.remove('text-primary-600');
                item.classList.add('text-slate-500');
            });
            
            const activeBottomNav = document.querySelector(`[data-nav="${sectionName}"]`);
            if(activeBottomNav) {
                activeBottomNav.classList.remove('text-slate-500');
                activeBottomNav.classList.add('text-primary-600');
            }
            
            const titles = {
                'dashboard': 'Tableau de Bord',
                'consultations': 'Mes Consultations',
                'ordonnances': 'Mes Ordonnances',
                'documents': 'Documents Médicaux',
                'paiements': 'Historique des Paiements',
                'messages': 'Messages',
                'profil': 'Mon Profil'
            };
            
            const titleText = titles[sectionName] || 'Tableau de Bord';
            const pageTitleDesktop = document.getElementById('page-title');
            const pageTitleMobile = document.getElementById('page-title-mobile');
            
            if(pageTitleDesktop) pageTitleDesktop.textContent = titleText;
            if(pageTitleMobile) pageTitleMobile.textContent = titleText;
            
            const mainContent = document.getElementById('main-content');
            if(mainContent) mainContent.scrollTop = 0;
            
            const overlay = document.getElementById('mobile-menu-overlay');
            if(overlay && !overlay.classList.contains('hidden')) {
                toggleMobileMenu();
            }
        }

        function toggleMobileMenu() {
            const overlay = document.getElementById('mobile-menu-overlay');
            if(overlay) {
                overlay.classList.toggle('hidden');
            }
        }

        function toggleNotifications() {
            showToast('Notifications - Fonctionnalité en développement', 'info');
        }

        function updateDate() {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateStr = new Date().toLocaleDateString('fr-FR', options);
            
            const dateDesktop = document.getElementById('current-date');
            const dateMobile = document.getElementById('current-date-mobile');
            
            if(dateDesktop) dateDesktop.textContent = dateStr;
            if(dateMobile) dateMobile.textContent = dateStr;
        }

        function openDocument(viewUrl, downloadUrl) {
            const modal = document.getElementById('doc-modal');
            const content = document.getElementById('modal-content');
            const downloadBtn = document.getElementById('modal-download');
            
            if(!modal) return;
            
            content.innerHTML = '<div class="flex items-center justify-center h-64"><i class="fas fa-spinner fa-spin text-3xl text-primary-600"></i></div>';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            if(downloadBtn) downloadBtn.href = downloadUrl;
            
            content.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-file-alt text-6xl text-slate-300 mb-4"></i>
                    <p class="text-slate-600">Aperçu non disponible</p>
                    <a href="${downloadUrl}" class="inline-block mt-4 text-primary-600 hover:underline">Télécharger le fichier</a>
                </div>
            `;
        }

        function closeModal() {
            const modal = document.getElementById('doc-modal');
            if(modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function downloadDoc(url) {
            window.open(url, '_blank');
        }

        function downloadReceipt(paymentId) {
            showToast('Téléchargement du reçu...', 'info');
        }

        function printPrescription(id) {
            window.open(`<?= base_url('ordonnance/print/') ?>${id}`, '_blank');
        }

        function markAsRead(messageId) {
            showToast('Message marqué comme lu', 'success');
            setTimeout(() => location.reload(), 500);
        }

        function timeAgo(dateString) {
            if(!dateString) return 'N/A';
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            
            if(seconds < 60) return 'À l\'instant';
            const minutes = Math.floor(seconds / 60);
            if(minutes < 60) return `Il y a ${minutes} min`;
            const hours = Math.floor(minutes / 60);
            if(hours < 24) return `Il y a ${hours}h`;
            const days = Math.floor(hours / 24);
            if(days < 7) return `Il y a ${days}j`;
            return date.toLocaleDateString('fr-FR');
        }

        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            if(!container) return;
            
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-medical-500',
                error: 'bg-red-500',
                info: 'bg-primary-500'
            };
            
            toast.className = `${colors[type]} text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 transform translate-x-full transition-transform duration-300 text-sm`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation' : 'info'}-circle"></i>
                <span>${message}</span>
            `;
            
            container.appendChild(toast);
            setTimeout(() => toast.classList.remove('translate-x-full'), 100);
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function previewImage(input) {
            if(input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('preview-photo');
                    if(preview) preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        const modal = document.getElementById('doc-modal');
        if(modal) {
            modal.addEventListener('click', function(e) {
                if(e.target === this) closeModal();
            });
        }
    </script>
</body>
</html>