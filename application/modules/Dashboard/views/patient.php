<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Patient - Nufotec</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js pour les graphiques -->
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
        /* Scrollbar personnalisée */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Animation de transition */
        .section-transition {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #0ea5e9 0%, #22c55e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <!-- Layout Principal -->
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar Statique -->
        <aside class="w-72 bg-white border-r border-slate-200 flex flex-col shadow-lg z-20">
            <!-- Logo -->
            <div class="p-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-medical-500 flex items-center justify-center text-white font-bold text-lg">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-xl text-slate-800">Nufotec</h1>
                        <p class="text-xs text-slate-500">Espace Patient</p>
                    </div>
                </div>
            </div>

            <!-- Profil Rapide -->
            <div class="p-4 bg-gradient-to-r from-primary-50 to-medical-50 m-4 rounded-2xl">
                <div class="flex items-center gap-3">
                    <img src="<?= base_url('attachments/Users/' . ($user->photo ?? 'default-avatar.png')) ?>" 
                         alt="Profil" 
                         class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm">
                    <div class="overflow-hidden">
                        <p class="font-semibold text-sm text-slate-800 truncate"><?= htmlspecialchars($user->prenom . ' ' . $user->nom) ?></p>
                        <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($user->email) ?></p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-4 space-y-1">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 px-3 mt-4">Menu Principal</p>
                
                <button onclick="showSection('dashboard')" id="nav-dashboard" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 bg-primary-50 text-primary-700">
                    <i class="fas fa-home w-5"></i>
                    <span>Tableau de Bord</span>
                    <span class="ml-auto bg-primary-500 text-white text-xs px-2 py-0.5 rounded-full">Actif</span>
                </button>

                <button onclick="showSection('consultations')" id="nav-consultations" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all duration-200">
                    <i class="fas fa-calendar-check w-5"></i>
                    <span>Mes Consultations</span>
                    <?php if(count($upcoming_consultations) > 0): ?>
                        <span class="ml-auto bg-medical-500 text-white text-xs px-2 py-0.5 rounded-full"><?= count($upcoming_consultations) ?></span>
                    <?php endif; ?>
                </button>

                <button onclick="showSection('ordonnances')" id="nav-ordonnances" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all duration-200">
                    <i class="fas fa-pills w-5"></i>
                    <span>Ordonnances</span>
                    <?php if($stats['active_prescriptions'] > 0): ?>
                        <span class="ml-auto bg-amber-500 text-white text-xs px-2 py-0.5 rounded-full"><?= $stats['active_prescriptions'] ?></span>
                    <?php endif; ?>
                </button>

                <button onclick="showSection('documents')" id="nav-documents" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all duration-200">
                    <i class="fas fa-file-medical w-5"></i>
                    <span>Documents</span>
                    <?php if(count($medical_documents) > 0): ?>
                        <span class="ml-auto bg-slate-500 text-white text-xs px-2 py-0.5 rounded-full"><?= count($medical_documents) ?></span>
                    <?php endif; ?>
                </button>

                <button onclick="showSection('paiements')" id="nav-paiements" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all duration-200">
                    <i class="fas fa-credit-card w-5"></i>
                    <span>Paiements</span>
                </button>

                <button onclick="showSection('messages')" id="nav-messages" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all duration-200">
                    <i class="fas fa-comments w-5"></i>
                    <span>Messages</span>
                    <?php if(count($unread_messages) > 0): ?>
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

            <!-- Footer Sidebar -->
            <div class="p-4 border-t border-slate-100">
                <div class="bg-gradient-to-r from-primary-500 to-medical-500 rounded-xl p-4 text-white">
                    <p class="text-xs font-medium opacity-90">Besoin d'aide ?</p>
                    <p class="text-sm font-semibold mt-1">Support 24/7</p>
                    <button class="mt-2 text-xs bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-lg transition-colors">
                        Contacter
                    </button>
                </div>
            </div>
        </aside>

        <!-- Contenu Principal -->
        <main class="flex-1 overflow-hidden flex flex-col bg-slate-50">
            
            <!-- Header -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shadow-sm">
                <div class="flex items-center gap-4">
                    <h2 id="page-title" class="text-xl font-bold text-slate-800">Tableau de Bord</h2>
                    <span class="text-slate-400">|</span>
                    <p class="text-sm text-slate-500" id="current-date"></p>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Recherche -->
                    <div class="relative">
                        <input type="text" placeholder="Rechercher..." 
                               class="pl-10 pr-4 py-2 bg-slate-100 border-0 rounded-xl text-sm w-64 focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>

                    <!-- Notifications -->
                    <button class="relative p-2 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors" onclick="toggleNotifications()">
                        <i class="fas fa-bell text-lg"></i>
                        <?php if(count($notifications) > 0): ?>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        <?php endif; ?>
                    </button>

                    <!-- Quick Actions -->
                    <a href="<?= base_url('nouvelle-consultation') ?>" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        <span>Nouvelle Consultation</span>
                    </a>
                </div>
            </header>

            <!-- Zone de Contenu Scrollable -->
            <div class="flex-1 overflow-y-auto p-8" id="main-content">
                
                <!-- SECTION: DASHBOARD -->
                <section id="section-dashboard" class="section-content section-transition">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                    <i class="fas fa-calendar-check text-xl"></i>
                                </div>
                                <span class="text-xs font-medium text-medical-600 bg-medical-50 px-2 py-1 rounded-lg">+2 ce mois</span>
                            </div>
                            <p class="text-2xl font-bold text-slate-800"><?= $stats['total_consultations'] ?></p>
                            <p class="text-sm text-slate-500">Consultations totales</p>
                        </div>

                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                                    <i class="fas fa-clock text-xl"></i>
                                </div>
                                <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-lg">À venir</span>
                            </div>
                            <p class="text-2xl font-bold text-slate-800"><?= $stats['upcoming_appointments'] ?></p>
                            <p class="text-sm text-slate-500">Rendez-vous à venir</p>
                        </div>



                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-medical-50 rounded-xl flex items-center justify-center text-medical-600">
                                    <i class="fas fa-pills text-xl"></i>
                                </div>
                                <span class="text-xs font-medium text-medical-600 bg-medical-50 px-2 py-1 rounded-lg">Actives</span>
                            </div>
                            <p class="text-2xl font-bold text-slate-800"><?= $stats['active_prescriptions'] ?></p>
                            <p class="text-sm text-slate-500">Ordonnances actives</p>
                        </div>

                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                                    <i class="fas fa-heartbeat text-xl"></i>
                                </div>
                                <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-lg">Bon</span>
                            </div>
                            <p class="text-2xl font-bold text-slate-800"><?= $stats['health_score'] ?>/100</p>
                            <p class="text-sm text-slate-500">Score de santé</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Prochains RDV -->
                        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="font-bold text-lg text-slate-800">Prochains Rendez-vous</h3>
                                <button onclick="showSection('consultations')" class="text-primary-600 text-sm font-medium hover:underline">Voir tout</button>
                            </div>
                            <div class="p-6">
                                <?php if(empty($upcoming_consultations)): ?>
                                    <div class="text-center py-8 text-slate-400">
                                        <i class="fas fa-calendar-plus text-4xl mb-3"></i>
                                        <p>Aucun rendez-vous à venir</p>
                                        <a href="<?= base_url('nouvelle-consultation') ?>" class="text-primary-600 font-medium mt-2 inline-block">Prendre rendez-vous</a>
                                    </div>
                                <?php else: ?>
                                    <div class="space-y-4">
                                        <?php foreach(array_slice($upcoming_consultations, 0, 3) as $consultation): ?>
                                            <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors">
                                                <div class="w-14 h-14 bg-primary-100 rounded-xl flex flex-col items-center justify-center text-primary-700">
                                                    <span class="text-xs font-bold uppercase"><?= date('M', strtotime($consultation->date_souhaitee)) ?></span>
                                                    <span class="text-lg font-bold"><?= date('d', strtotime($consultation->date_souhaitee)) ?></span>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-semibold text-slate-800">
                                                        Dr. <?= htmlspecialchars($consultation->medecin_prenom . ' ' . $consultation->medecin_nom) ?>
                                                    </p>
                                                    <p class="text-sm text-slate-500">
                                                        <?= htmlspecialchars($consultation->specialite) ?> • 
                                                        <?= date('H:i', strtotime($consultation->date_souhaitee)) ?>
                                                    </p>
                                                </div>
                                                <span class="px-3 py-1 rounded-full text-xs font-medium 
                                                    <?= $consultation->statut == 'confirmee' ? 'bg-medical-100 text-medical-700' : 'bg-amber-100 text-amber-700' ?>">
                                                    <?= $consultation->statut == 'confirmee' ? 'Confirmé' : 'En attente' ?>
                                                </span>
                                               <?php if($consultation->statut == 'confirmee' && !empty($consultation->room_id)): ?>
    <a href="<?= base_url('joinconsultation/index?room=' . $consultation->room_id . '&user=' . $this->session->userdata('user_id')) ?>" 
       target="_blank" 
       class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
        <i class="fas fa-video mr-1"></i> Rejoindre
    </a>
<?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Notifications & Messages -->
                        <div class="space-y-6">
                            <!-- Messages non lus -->
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                                <div class="p-6 border-b border-slate-100">
                                    <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                                        Messages
                                        <?php if(count($unread_messages) > 0): ?>
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
                                                <div class="flex gap-3 p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors" onclick="markAsRead(<?= $msg->id ?>)">
                                                    <img src="<?= base_url('attachments/Users/' . ($msg->sender_photo ?? 'default-avatar.png')) ?>" 
                                                         class="w-10 h-10 rounded-full object-cover">
                                                    <div class="flex-1 min-w-0">
                                                        <p class="font-medium text-sm text-slate-800 truncate">
                                                            Dr. <?= htmlspecialchars($msg->sender_prenom . ' ' . $msg->sender_nom) ?>
                                                        </p>
                                                        <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($msg->message) ?></p>
                                                        <p class="text-xs text-slate-400 mt-1"><?= timeAgo($msg->created_at) ?></p>
                                                    </div>
                                                    <span class="w-2 h-2 bg-primary-500 rounded-full mt-2"></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Médecins favoris -->
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                                <div class="p-6 border-b border-slate-100">
                                    <h3 class="font-bold text-lg text-slate-800">Mes Médecins</h3>
                                </div>
                                <div class="p-4">
                                    <?php if(empty($favorite_doctors)): ?>
                                        <p class="text-sm text-slate-400 text-center py-4">Aucun médecin consulté</p>
                                    <?php else: ?>
                                        <div class="space-y-3">
                                            <?php foreach(array_slice($favorite_doctors, 0, 4) as $doc): ?>
                                                <div class="flex items-center gap-3 p-3 hover:bg-slate-50 rounded-xl transition-colors cursor-pointer">
                                                    <img src="<?= base_url('attachments/Users/' . ($doc->photo ?? 'default-avatar.png')) ?>" 
                                                         class="w-12 h-12 rounded-full object-cover">
                                                    <div class="flex-1">
                                                        <p class="font-medium text-sm text-slate-800">Dr. <?= htmlspecialchars($doc->prenom . ' ' . $doc->nom) ?></p>
                                                        <p class="text-xs text-slate-500"><?= htmlspecialchars($doc->specialite) ?></p>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="flex items-center gap-1 text-amber-500 text-xs">
                                                            <i class="fas fa-star"></i>
                                                            <span><?= number_format($doc->note_moyenne, 1) ?></span>
                                                        </div>
                                                        <p class="text-xs text-slate-400"><?= $doc->consultation_count ?> consultations</p>
                                                    </div>
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
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-xl text-slate-800">Historique des Consultations</h3>
                                <p class="text-sm text-slate-500 mt-1">Toutes vos consultations passées et à venir</p>
                            </div>
                            <div class="flex gap-2">
                                <select id="filter-status" onchange="filterConsultations()" class="px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500">
                                    <option value="all">Tous les statuts</option>
                                    <option value="en_attente">En attente</option>
                                    <option value="confirmee">Confirmée</option>
                                    <option value="terminee">Terminée</option>
                                    <option value="annulee">Annulée</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Médecin</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Statut</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100" id="consultations-table">
                                    <?php foreach($all_consultations as $consultation): ?>
                                        <tr class="hover:bg-slate-50 transition-colors consultation-row" data-status="<?= $consultation->statut ?>">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <img src="<?= base_url('attachments/Users/' . ($consultation->medecin_photo ?? 'default-avatar.png')) ?>" 
                                                         class="w-10 h-10 rounded-full object-cover">
                                                    <div>
                                                        <p class="font-medium text-slate-800">Dr. <?= htmlspecialchars($consultation->medecin_prenom . ' ' . $consultation->medecin_nom) ?></p>
                                                        <p class="text-xs text-slate-500"><?= htmlspecialchars($consultation->specialite) ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="text-sm text-slate-800"><?= date('d/m/Y', strtotime($consultation->date_souhaitee ?? $consultation->date_fin)) ?></p>
                                                <p class="text-xs text-slate-500"><?= date('H:i', strtotime($consultation->date_souhaitee ?? $consultation->date_fin)) ?></p>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                                    <i class="fas fa-<?= $consultation->type == 'video' ? 'video' : ($consultation->type == 'telephone' ? 'phone' : 'hospital') ?>"></i>
                                                    <?= ucfirst($consultation->type) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-3 py-1 rounded-full text-xs font-medium 
                                                    <?= $consultation->statut == 'terminee' ? 'bg-medical-100 text-medical-700' : 
                                                       ($consultation->statut == 'confirmee' ? 'bg-primary-100 text-primary-700' : 
                                                       ($consultation->statut == 'annulee' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700')) ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $consultation->statut)) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    <?php 
// 1. On récupère le timestamp du rendez-vous
$rendez_vous_time = strtotime($consultation->date_souhaitee);
// 2. On définit la fenêtre d'ouverture (ex: 10 minutes avant)
$ouverture_salle = $rendez_vous_time - (10 * 60); 
// 3. Heure actuelle
$maintenant = time();


// Vérifier que la consultation a un room_id, que le statut est confirmee ou en_cours,
// et que l'heure est venue (ou déjà en cours)
if (!empty($consultation->room_id) && 
    in_array($consultation->statut, ['confirmee', 'en_cours']) && 
    ($maintenant >= $ouverture_salle || $consultation->statut == 'en_cours')
): ?>
    <a href="<?= base_url('Joinconsultation/index?room=' . urlencode($consultation->room_id) . '&user=' . $this->session->userdata('user_id')) ?>" 
       target="_blank" 
       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-all shadow-sm animate-pulse" 
       title="Rejoindre la consultation">
        <i class="fas fa-video text-xs"></i>
        <span>Rejoindre</span>
    </a>
<?php elseif($consultation->statut == 'confirmee'): ?>
    <span class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-400 text-sm font-medium rounded-lg cursor-not-allowed" 
          title="Le bouton s'activera 10min avant l'heure">
        <i class="fas fa-lock text-xs"></i>
        <span>Bientôt disponible</span>
    </span>
<?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- SECTION: ORDONNANCES -->
                <section id="section-ordonnances" class="section-content hidden section-transition">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Liste des ordonnances -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                                <div class="p-6 border-b border-slate-100">
                                    <h3 class="font-bold text-xl text-slate-800">Mes Ordonnances</h3>
                                </div>
                                <div class="p-6">
                                    <?php if(empty($recent_prescriptions)): ?>
                                        <div class="text-center py-12 text-slate-400">
                                            <i class="fas fa-prescription-bottle-alt text-5xl mb-4"></i>
                                            <p>Aucune ordonnance disponible</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="space-y-4">
                                            <?php foreach($recent_prescriptions as $prescription): ?>
                                                <div class="border border-slate-200 rounded-xl p-4 hover:shadow-md transition-shadow bg-white">
                                                    <div class="flex items-start justify-between mb-3">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-12 h-12 bg-medical-50 rounded-xl flex items-center justify-center text-medical-600">
                                                                <i class="fas fa-pills text-xl"></i>
                                                            </div>
                                                            <div>
                                                                <p class="font-semibold text-slate-800"><?= htmlspecialchars($prescription->medicament) ?></p>
                                                                <p class="text-sm text-slate-500">Dr. <?= htmlspecialchars($prescription->medecin_prenom . ' ' . $prescription->medecin_nom) ?></p>
                                                            </div>
                                                        </div>
                                                        <span class="px-3 py-1 rounded-full text-xs font-medium <?= $prescription->is_active ? 'bg-medical-100 text-medical-700' : 'bg-slate-100 text-slate-600' ?>">
                                                            <?= $prescription->is_active ? 'Active' : 'Terminée' ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <?php if($prescription->dosage): ?>
                                                        <div class="bg-slate-50 rounded-lg p-3 mb-3">
                                                            <p class="text-sm text-slate-600"><span class="font-medium">Dosage:</span> <?= htmlspecialchars($prescription->dosage) ?></p>
                                                            <?php if($prescription->instructions): ?>
                                                                <p class="text-sm text-slate-600 mt-1"><span class="font-medium">Instructions:</span> <?= htmlspecialchars($prescription->instructions) ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                                                        <p class="text-xs text-slate-400">
                                                            <i class="far fa-calendar mr-1"></i>
                                                            <?= date('d/m/Y', strtotime($prescription->consultation_date)) ?>
                                                        </p>
                                                        <div class="flex gap-2">
                                                            <?php if($prescription->source == 'json' && isset($prescription->file_url)): ?>
                                                                <a href="<?= $prescription->file_url ?>" target="_blank" 
                                                                   class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                                                                    <i class="fas fa-download mr-1"></i> Télécharger
                                                                </a>
                                                            <?php endif; ?>
                                                            <button onclick="printPrescription('<?= $prescription->id ?>')" 
                                                                    class="text-sm text-slate-600 hover:text-slate-700 font-medium">
                                                                <i class="fas fa-print mr-1"></i> Imprimer
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Calendrier de traitement -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="p-6 border-b border-slate-100">
                                <h3 class="font-bold text-lg text-slate-800">Calendrier de traitement</h3>
                            </div>
                            <div class="p-6">
                                <div id="treatment-calendar" class="space-y-3">
                                    <!-- Généré par JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION: DOCUMENTS -->
                <section id="section-documents" class="section-content hidden section-transition">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-xl text-slate-800">Documents Médicaux</h3>
                                <p class="text-sm text-slate-500 mt-1">Examens, ordonnances et preuves de paiement</p>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="filterDocs('all')" class="px-4 py-2 rounded-xl text-sm font-medium bg-primary-50 text-primary-700 hover:bg-primary-100 transition-colors doc-filter active" data-filter="all">
                                    Tous
                                </button>
                                <button onclick="filterDocs('examen')" class="px-4 py-2 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors doc-filter" data-filter="examen">
                                    Examens
                                </button>
                                <button onclick="filterDocs('ordonnance')" class="px-4 py-2 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors doc-filter" data-filter="ordonnance">
                                    Ordonnances
                                </button>
                                <button onclick="filterDocs('paiement')" class="px-4 py-2 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors doc-filter" data-filter="paiement">
                                    Paiements
                                </button>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <?php if(empty($medical_documents)): ?>
                                <div class="text-center py-12 text-slate-400">
                                    <i class="fas fa-folder-open text-5xl mb-4"></i>
                                    <p>Aucun document disponible</p>
                                </div>
                            <?php else: ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="documents-grid">
                                    <?php foreach($medical_documents as $doc): ?>
                                        <div class="document-card group border border-slate-200 rounded-xl p-4 hover:shadow-lg transition-all cursor-pointer bg-white" 
                                             data-type="<?= $doc->type ?>" onclick="openDocument('<?= $doc->view_url ?>', '<?= $doc->download_url ?>')">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl
                                                    <?= $doc->type == 'examen' ? 'bg-purple-50 text-purple-600' : 
                                                       ($doc->type == 'ordonnance' ? 'bg-medical-50 text-medical-600' : 
                                                       ($doc->type == 'paiement' ? 'bg-amber-50 text-amber-600' : 'bg-slate-50 text-slate-600')) ?>">
                                                    <i class="fas fa-<?= $doc->type == 'examen' ? 'microscope' : ($doc->type == 'ordonnance' ? 'file-prescription' : ($doc->type == 'paiement' ? 'receipt' : 'file')) ?>"></i>
                                                </div>
                                                <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <button class="p-2 text-slate-400 hover:text-primary-600" onclick="event.stopPropagation(); downloadDoc('<?= $doc->download_url ?>')">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <h4 class="font-medium text-slate-800 mb-1 truncate"><?= htmlspecialchars($doc->original_name) ?></h4>
                                            <p class="text-xs text-slate-500 mb-2"><?= ucfirst($doc->type) ?> • <?= date('d/m/Y', strtotime($doc->created_at)) ?></p>
                                            <?php if($doc->consultation_numero): ?>
                                                <p class="text-xs text-slate-400">Consultation: <?= $doc->consultation_numero ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- SECTION: PAIEMENTS -->
                <section id="section-paiements" class="section-content hidden section-transition">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-100">
                            <h3 class="font-bold text-xl text-slate-800">Historique des Paiements</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">N° Consultation</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Médecin</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Montant</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Mode</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Reçu</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if(empty($payment_history)): ?>
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                                <i class="fas fa-receipt text-4xl mb-3"></i>
                                                <p>Aucun paiement effectué</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($payment_history as $payment): ?>
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="px-6 py-4 font-medium text-slate-800"><?= $payment->numero_consultation ?></td>
                                                <td class="px-6 py-4">Dr. <?= htmlspecialchars($payment->medecin_prenom . ' ' . $payment->medecin_nom) ?></td>
                                                <td class="px-6 py-4 text-sm text-slate-600"><?= date('d/m/Y', strtotime($payment->payment_date)) ?></td>
                                                <td class="px-6 py-4 font-semibold text-slate-800"><?= number_format($payment->prix_ttc, 2) ?> <?= $payment->devise ?></td>
                                                <td class="px-6 py-4">
                                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                                        <?= htmlspecialchars($payment->mode_paiement) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <button onclick="downloadReceipt(<?= $payment->id ?>)" class="text-primary-600 hover:text-primary-700">
                                                        <i class="fas fa-download"></i> Télécharger
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
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden h-[calc(100vh-200px)]">
                        <div class="grid grid-cols-1 lg:grid-cols-3 h-full">
                            <!-- Liste des conversations -->
                            <div class="border-r border-slate-100 flex flex-col">
                                <div class="p-4 border-b border-slate-100">
                                    <h3 class="font-bold text-lg text-slate-800">Conversations</h3>
                                </div>
                                <div class="flex-1 overflow-y-auto p-4 space-y-2" id="conversations-list">
                                    <!-- Rempli par JavaScript -->
                                </div>
                            </div>
                            
                            <!-- Zone de chat -->
                            <div class="lg:col-span-2 flex flex-col">
                                <div class="p-4 border-b border-slate-100 flex items-center justify-between" id="chat-header">
                                    <p class="text-slate-500">Sélectionnez une conversation</p>
                                </div>
                                <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
                                    <div class="flex items-center justify-center h-full text-slate-400">
                                        <p>Aucune conversation sélectionnée</p>
                                    </div>
                                </div>
                                <div class="p-4 border-t border-slate-100" id="chat-input" style="display: none;">
                                    <div class="flex gap-2">
                                        <input type="text" placeholder="Écrivez votre message..." 
                                               class="flex-1 px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <button class="bg-primary-600 text-white px-4 py-2 rounded-xl hover:bg-primary-700 transition-colors">
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
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-primary-50 to-medical-50">
                                <h3 class="font-bold text-xl text-slate-800">Mon Profil</h3>
                                <p class="text-sm text-slate-600 mt-1">Gérez vos informations personnelles</p>
                            </div>
                            
                            <form action="<?= base_url('Dashboard/PatientDashboard/update_profile') ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                                <!-- Photo de profil -->
                                <div class="flex items-center gap-6">
                                    <div class="relative">
                                        <img src="<?= base_url('attachments/Users/' . ($user->photo ?? 'default-avatar.png')) ?>" 
                                             alt="Profil" 
                                             class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg"
                                             id="preview-photo">
                                        <label class="absolute bottom-0 right-0 w-8 h-8 bg-primary-600 text-white rounded-full flex items-center justify-center cursor-pointer hover:bg-primary-700 transition-colors shadow-md">
                                            <i class="fas fa-camera text-sm"></i>
                                            <input type="file" name="photo" accept="image/*" class="hidden" onchange="previewImage(this)">
                                        </label>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-800">Photo de profil</h4>
                                        <p class="text-sm text-slate-500">JPG, PNG ou GIF. Max 2MB.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Prénom</label>
                                        <input type="text" name="prenom" value="<?= htmlspecialchars($user->prenom) ?>" required
                                               class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Nom</label>
                                        <input type="text" name="nom" value="<?= htmlspecialchars($user->nom) ?>" required
                                               class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                                        <input type="email" name="email" value="<?= htmlspecialchars($user->email) ?>" required
                                               class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Téléphone</label>
                                        <input type="tel" name="telephone" value="<?= htmlspecialchars($user->telephone ?? '') ?>"
                                               class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Date de naissance</label>
                                        <input type="date" name="date_naissance" value="<?= $user->date_naissance ?>"
                                               class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Genre</label>
                                        <select name="genre" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                            <option value="">Non spécifié</option>
                                            <option value="M" <?= $user->genre == 'M' ? 'selected' : '' ?>>Masculin</option>
                                            <option value="F" <?= $user->genre == 'F' ? 'selected' : '' ?>>Féminin</option>
                                            <option value="Autre" <?= $user->genre == 'Autre' ? 'selected' : '' ?>>Autre</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="border-t border-slate-100 pt-6">
                                    <h4 class="font-semibold text-slate-800 mb-4">Changer le mot de passe</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Mot de passe actuel</label>
                                            <input type="password" name="current_password"
                                                   class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        </div>
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

                                <div class="flex justify-end gap-4 pt-6 border-t border-slate-100">
                                    <button type="button" onclick="showSection('dashboard')" class="px-6 py-2 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50 transition-colors">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors font-medium">
                                        <i class="fas fa-save mr-2"></i>Enregistrer les modifications
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

            </div>
        </main>
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
                <!-- Contenu du document -->
            </div>
            <div class="p-4 border-t border-slate-100 flex justify-end gap-2">
                <a id="modal-download" href="#" class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                    <i class="fas fa-download mr-2"></i>Télécharger
                </a>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    <script>
        // Données PHP injectées dans JavaScript
        const userData = {
            id: <?= $user->id ?>,
            name: "<?= htmlspecialchars($user->prenom . ' ' . $user->nom) ?>"
        };

        const consultations = <?= json_encode($upcoming_consultations) ?>;
        const messages = <?= json_encode($unread_messages) ?>;
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            updateDate();
            setInterval(updateDate, 60000);
            initTreatmentCalendar();
            initConversations();
            
            // Polling pour les mises à jour
            setInterval(pollUpdates, 30000);
        });

        // Navigation entre sections
        function showSection(sectionName) {
            // Cacher toutes les sections
            document.querySelectorAll('.section-content').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Afficher la section demandée
            const targetSection = document.getElementById('section-' + sectionName);
            if(targetSection) {
                targetSection.classList.remove('hidden');
                targetSection.classList.add('section-transition');
            }
            
            // Mettre à jour la navigation
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('bg-primary-50', 'text-primary-700');
                item.classList.add('text-slate-600');
            });
            
            const activeNav = document.getElementById('nav-' + sectionName);
            if(activeNav) {
                activeNav.classList.remove('text-slate-600');
                activeNav.classList.add('bg-primary-50', 'text-primary-700');
            }
            
            // Mettre à jour le titre
            const titles = {
                'dashboard': 'Tableau de Bord',
                'consultations': 'Mes Consultations',
                'ordonnances': 'Mes Ordonnances',
                'documents': 'Documents Médicaux',
                'paiements': 'Historique des Paiements',
                'messages': 'Messages',
                'profil': 'Mon Profil'
            };
            document.getElementById('page-title').textContent = titles[sectionName] || 'Tableau de Bord';
            
            // Scroll en haut
            document.getElementById('main-content').scrollTop = 0;
        }

        // Mise à jour de la date
        function updateDate() {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('current-date').textContent = new Date().toLocaleDateString('fr-FR', options);
        }

        // Filtre des consultations
        function filterConsultations() {
            const filter = document.getElementById('filter-status').value;
            const rows = document.querySelectorAll('.consultation-row');
            
            rows.forEach(row => {
                if(filter === 'all' || row.dataset.status === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Filtre des documents
        function filterDocs(type) {
            // Mettre à jour les boutons
            document.querySelectorAll('.doc-filter').forEach(btn => {
                btn.classList.remove('bg-primary-50', 'text-primary-700');
                btn.classList.add('text-slate-600');
            });
            event.target.classList.remove('text-slate-600');
            event.target.classList.add('bg-primary-50', 'text-primary-700');
            
            // Filtrer les cartes
            const cards = document.querySelectorAll('.document-card');
            cards.forEach(card => {
                if(type === 'all' || card.dataset.type === type) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Ouverture d'un document
        function openDocument(viewUrl, downloadUrl) {
            const modal = document.getElementById('doc-modal');
            const content = document.getElementById('modal-content');
            const downloadBtn = document.getElementById('modal-download');
            
            content.innerHTML = '<div class="flex items-center justify-center h-64"><i class="fas fa-spinner fa-spin text-3xl text-primary-600"></i></div>';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            downloadBtn.href = downloadUrl;
            
            // Déterminer le type de contenu
            const ext = viewUrl.split('.').pop().toLowerCase();
            if(['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                content.innerHTML = `<img src="${viewUrl}" class="max-w-full mx-auto rounded-lg shadow-lg">`;
            } else if(ext === 'pdf') {
                content.innerHTML = `<iframe src="${viewUrl}" class="w-full h-[70vh] rounded-lg"></iframe>`;
            } else {
                content.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-file-alt text-6xl text-slate-300 mb-4"></i>
                        <p class="text-slate-600">Ce fichier ne peut pas être prévisualisé</p>
                        <a href="${downloadUrl}" class="inline-block mt-4 text-primary-600 hover:underline">Télécharger le fichier</a>
                    </div>
                `;
            }
        }

        function closeModal() {
            const modal = document.getElementById('doc-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function downloadDoc(url) {
            window.open(url, '_blank');
        }

        // Téléchargement du reçu
        function downloadReceipt(paymentId) {
            showToast('Téléchargement du reçu...', 'info');
            // Implémenter l'appel AJAX pour générer le PDF
        }

        // Impression d'ordonnance
        function printPrescription(id) {
            window.open(`<?= base_url('ordonnance/print/') ?>${id}`, '_blank');
        }

        // Marquer un message comme lu
        function markAsRead(messageId) {
            fetch(`<?= base_url('Dashboard/PatientDashboard/mark_message_read/') ?>${messageId}`)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        showToast('Message marqué comme lu', 'success');
                        // Rafraîchir la liste
                        location.reload();
                    }
                });
        }

        // Calendrier de traitement
        function initTreatmentCalendar() {
            const calendar = document.getElementById('treatment-calendar');
            const days = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
            const today = new Date();
            
            let html = '';
            for(let i = 0; i < 7; i++) {
                const date = new Date(today);
                date.setDate(today.getDate() + i);
                const isToday = i === 0;
                const hasMed = Math.random() > 0.5; // Simuler des médicaments
                
                html += `
                    <div class="flex items-center gap-3 p-3 rounded-xl ${isToday ? 'bg-primary-50 border border-primary-200' : 'bg-slate-50'}">
                        <div class="w-12 h-12 rounded-lg ${isToday ? 'bg-primary-500 text-white' : 'bg-white text-slate-600'} flex flex-col items-center justify-center font-bold">
                            <span class="text-xs">${days[date.getDay()]}</span>
                            <span class="text-lg">${date.getDate()}</span>
                        </div>
                        <div class="flex-1">
                            ${hasMed ? 
                                `<p class="text-sm font-medium text-slate-800">Paracétamol</p>
                                 <p class="text-xs text-slate-500">08:00 • 1 comprimé</p>` : 
                                `<p class="text-sm text-slate-400">Aucun médicament</p>`
                            }
                        </div>
                        ${hasMed ? `<i class="fas fa-check-circle text-medical-500"></i>` : ''}
                    </div>
                `;
            }
            calendar.innerHTML = html;
        }

        // Initialisation des conversations
        function initConversations() {
            const list = document.getElementById('conversations-list');
            // Regrouper les messages par médecin
            const conversations = {};
            
            messages.forEach(msg => {
                const key = msg.medecin_id || msg.sender_id;
                if(!conversations[key]) {
                    conversations[key] = {
                        doctor: msg.sender_prenom + ' ' + msg.sender_nom,
                        specialty: msg.specialite,
                        photo: msg.sender_photo,
                        lastMessage: msg,
                        unread: 0
                    };
                }
                if(!msg.is_read) conversations[key].unread++;
            });
            
            let html = '';
            Object.values(conversations).forEach(conv => {
                html += `
                    <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 cursor-pointer transition-colors" onclick="openChat('${conv.doctor}', '${conv.specialty}', '${conv.photo}')">
                        <div class="relative">
                            <img src="<?= base_url('attachments/Users/') ?>${conv.photo || 'default-avatar.png'}" class="w-12 h-12 rounded-full object-cover">
                            ${conv.unread > 0 ? `<span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">${conv.unread}</span>` : ''}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm text-slate-800 truncate">Dr. ${conv.doctor}</p>
                            <p class="text-xs text-slate-500 truncate">${conv.lastMessage.message}</p>
                        </div>
                        <span class="text-xs text-slate-400">${timeAgo(conv.lastMessage.created_at)}</span>
                    </div>
                `;
            });
            
            list.innerHTML = html || '<p class="text-center text-slate-400 py-4">Aucune conversation</p>';
        }

        function openChat(doctorName, specialty, photo) {
            document.getElementById('chat-header').innerHTML = `
                <div class="flex items-center gap-3">
                    <img src="<?= base_url('attachments/Users/') ?>${photo || 'default-avatar.png'}" class="w-10 h-10 rounded-full object-cover">
                    <div>
                        <p class="font-semibold text-slate-800">Dr. ${doctorName}</p>
                        <p class="text-xs text-slate-500">${specialty}</p>
                    </div>
                </div>
            `;
            document.getElementById('chat-input').style.display = 'block';
            document.getElementById('chat-messages').innerHTML = `
                <div class="flex justify-center mb-4">
                    <span class="bg-slate-100 text-slate-500 text-xs px-3 py-1 rounded-full">Aujourd'hui</span>
                </div>
                <div class="flex gap-3 mb-4">
                    <div class="bg-slate-100 rounded-2xl rounded-tl-none px-4 py-2 max-w-[70%]">
                        <p class="text-sm text-slate-700">Bonjour, comment puis-je vous aider aujourd'hui ?</p>
                        <span class="text-xs text-slate-400 mt-1 block">10:30</span>
                    </div>
                </div>
            `;
        }

        // Utilitaires
        function timeAgo(dateString) {
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
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-medical-500',
                error: 'bg-red-500',
                info: 'bg-primary-500'
            };
            
            toast.className = `${colors[type]} text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 transform translate-x-full transition-transform duration-300`;
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
                    document.getElementById('preview-photo').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Polling pour mises à jour
        function pollUpdates() {
            fetch('<?= base_url('Dashboard/PatientDashboard/get_dashboard_data') ?>')
                .then(response => response.json())
                .then(data => {
                    // Mettre à jour les compteurs si nécessaire
                    console.log('Dashboard updated:', data);
                });
        }

        // Fermer le modal en cliquant à l'extérieur
        document.getElementById('doc-modal').addEventListener('click', function(e) {
            if(e.target === this) closeModal();
        });
    </script>
</body>
</html>