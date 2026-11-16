<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
/* ============================================
   DASHBOARD CLIENT AGF - STYLE PROFESSIONNEL
   ============================================ */

:root {
    --primary-green: #0B4F2E;
    --secondary-green: #1B7B4B;
    --accent-green: #27ae60;
    --jaune: #FFD700;
    --light-green: #2ecc71;
    --dark-bg: #0a3d24;
    --text-dark: #1a2e3f;
    --text-muted: #6c757d;
    --border-light: #e9ecef;
    --shadow-soft: 0 10px 30px rgba(0,0,0,0.05);
    --shadow-hover: 0 20px 40px rgba(0,0,0,0.1);
    --danger: #dc3545;
    --success: #28a745;
    --warning: #ffc107;
    --info: #17a2b8;
}

.dashboard-page {
    padding: 40px 0 60px;
    background: linear-gradient(135deg, #f8faf9 0%, #ffffff 100%);
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ============================================
   EN-TÊTE DU DASHBOARD
   ============================================ */
.dashboard-header {
    margin-bottom: 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.welcome-section h1 {
    font-size: 32px;
    font-weight: 800;
    color: var(--primary-green);
    margin-bottom: 8px;
}

.welcome-section p {
    font-size: 16px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 8px;
}

.welcome-section p i {
    color: var(--secondary-green);
}

.last-login {
    background: white;
    padding: 12px 25px;
    border-radius: 40px;
    box-shadow: var(--shadow-soft);
    border: 1px solid var(--border-light);
    font-size: 14px;
    color: var(--text-muted);
}

.last-login i {
    color: var(--jaune);
    margin-right: 8px;
}

.last-login strong {
    color: var(--primary-green);
    margin-left: 5px;
}

/* ============================================
   GRILLE PRINCIPALE
   ============================================ */
.dashboard-grid {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 30px;
    align-items: start;
}

/* ============================================
   MENU LATÉRAL (SIDEBAR)
   ============================================ */
.dashboard-sidebar {
    background: white;
    border-radius: 30px;
    padding: 30px 20px;
    box-shadow: var(--shadow-soft);
    border: 1px solid var(--border-light);
    position: sticky;
    top: 100px;
}

/* Profil utilisateur */
.user-profile {
    text-align: center;
    padding-bottom: 25px;
    border-bottom: 2px solid var(--border-light);
    margin-bottom: 25px;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    background: linear-gradient(145deg, var(--primary-green), var(--secondary-green));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    border: 4px solid var(--jaune);
    position: relative;
}

.profile-avatar span {
    font-size: 40px;
    font-weight: 700;
    color: white;
    text-transform: uppercase;
}

.avatar-edit {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 30px;
    height: 30px;
    background: var(--jaune);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-green);
    cursor: pointer;
    border: 2px solid white;
    font-size: 14px;
    transition: all 0.3s ease;
}

.avatar-edit:hover {
    transform: scale(1.1);
    background: var(--secondary-green);
    color: white;
}

.user-profile h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.user-profile p {
    font-size: 13px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.user-profile p i {
    color: var(--secondary-green);
    font-size: 12px;
}

/* Menu de navigation */
.dashboard-menu {
    list-style: none;
    padding: 0;
    margin: 0 0 25px;
}

.dashboard-menu li {
    margin-bottom: 5px;
}

.dashboard-menu a {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 14px 20px;
    border-radius: 15px;
    color: var(--text-muted);
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
}

.dashboard-menu a i {
    width: 22px;
    font-size: 18px;
    color: var(--text-muted);
    transition: all 0.3s ease;
}

.dashboard-menu a:hover {
    background: rgba(27, 123, 75, 0.1);
    color: var(--secondary-green);
}

.dashboard-menu a:hover i {
    color: var(--secondary-green);
}

.dashboard-menu li.active a {
    background: var(--secondary-green);
    color: white;
    box-shadow: 0 10px 20px rgba(27, 123, 75, 0.3);
}

.dashboard-menu li.active a i {
    color: white;
}

.menu-badge {
    margin-left: auto;
    background: var(--jaune);
    color: var(--primary-green);
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

/* Bouton déconnexion */
.btn-logout {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 14px 20px;
    background: #fff1f0;
    border: none;
    border-radius: 15px;
    color: var(--danger);
    font-weight: 600;
    width: 100%;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 20px;
}

.btn-logout:hover {
    background: var(--danger);
    color: white;
}

.btn-logout:hover i {
    color: white;
}

.btn-logout i {
    font-size: 18px;
    color: var(--danger);
    transition: all 0.3s ease;
}

/* ============================================
   CONTENU PRINCIPAL
   ============================================ */
.dashboard-content {
    background: white;
    border-radius: 30px;
    padding: 30px;
    box-shadow: var(--shadow-soft);
    border: 1px solid var(--border-light);
}

/* En-tête de section */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.section-header h2 {
    font-size: 22px;
    font-weight: 700;
    color: var(--primary-green);
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header h2 i {
    color: var(--jaune);
    font-size: 24px;
}

.btn-view-all {
    padding: 10px 20px;
    background: transparent;
    border: 2px solid var(--border-light);
    border-radius: 30px;
    color: var(--text-muted);
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-view-all:hover {
    border-color: var(--secondary-green);
    color: var(--secondary-green);
}

/* ============================================
   STATISTIQUES (CARDS)
   ============================================ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(145deg, #ffffff, #f8faf9);
    padding: 25px;
    border-radius: 20px;
    border: 1px solid var(--border-light);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--secondary-green);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: rgba(27, 123, 75, 0.1);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: var(--secondary-green);
    margin-bottom: 15px;
}

.stat-value {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.stat-label {
    font-size: 13px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ============================================
   COMMANDES RÉCENTES
   ============================================ */
.orders-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
}

.orders-table th {
    text-align: left;
    padding: 15px 10px;
    background: #f8faf9;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-dark);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 10px 10px 0 0;
}

.orders-table td {
    padding: 18px 10px;
    border-bottom: 1px solid var(--border-light);
    font-size: 14px;
    color: var(--text-dark);
}

.orders-table tr:last-child td {
    border-bottom: none;
}

.orders-table tr:hover td {
    background: #f8faf9;
}

.order-status {
    display: inline-block;
    padding: 6px 15px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-delivered {
    background: rgba(40, 167, 69, 0.1);
    color: var(--success);
}

.status-processing {
    background: rgba(255, 193, 7, 0.1);
    color: #856404;
}

.status-shipped {
    background: rgba(23, 162, 184, 0.1);
    color: var(--info);
}

.status-pending {
    background: rgba(108, 117, 125, 0.1);
    color: var(--text-muted);
}

.order-total {
    font-weight: 700;
    color: var(--primary-green);
}

.btn-order-detail {
    padding: 6px 15px;
    background: transparent;
    border: 1px solid var(--border-light);
    border-radius: 20px;
    color: var(--text-muted);
    font-size: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-order-detail:hover {
    background: var(--secondary-green);
    border-color: var(--secondary-green);
    color: white;
}

/* ============================================
   PRODUITS FAVORIS
   ============================================ */
.favorites-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 20px;
}

.favorite-card {
    background: #f8faf9;
    border-radius: 15px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s ease;
    border: 1px solid var(--border-light);
}

.favorite-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
    background: white;
}

.fav-image {
    width: 60px;
    height: 60px;
    background: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: var(--secondary-green);
    border: 1px solid var(--border-light);
}

.fav-info {
    flex: 1;
}

.fav-info h4 {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.fav-info p {
    font-size: 13px;
    color: var(--secondary-green);
    font-weight: 600;
}

.btn-fav {
    width: 30px;
    height: 30px;
    background: white;
    border: 1px solid var(--border-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--danger);
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-fav:hover {
    background: var(--danger);
    color: white;
}

/* ============================================
   ACTIVITÉS RÉCENTES
   ============================================ */
.activities-list {
    margin-top: 20px;
}

.activity-item {
    display: flex;
    gap: 15px;
    padding: 20px 0;
    border-bottom: 1px solid var(--border-light);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 45px;
    height: 45px;
    background: rgba(27, 123, 75, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--secondary-green);
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-content p {
    font-size: 14px;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.activity-content strong {
    color: var(--primary-green);
}

.activity-time {
    font-size: 12px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 5px;
}

.activity-time i {
    font-size: 10px;
    color: var(--secondary-green);
}

/* ============================================
   SECTION MES INFORMATIONS
   ============================================ */
.profile-form {
    max-width: 600px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid var(--border-light);
    border-radius: 15px;
    font-size: 15px;
    transition: all 0.3s ease;
    background: white;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--secondary-green);
    outline: none;
    box-shadow: 0 0 0 4px rgba(27, 123, 75, 0.1);
}

.form-group input[readonly] {
    background: #f8faf9;
    cursor: not-allowed;
}

.btn-save {
    padding: 16px 40px;
    background: var(--secondary-green);
    color: white;
    border: none;
    border-radius: 15px;
    font-weight: 700;
    font-size: 16px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 10px 25px rgba(27, 123, 75, 0.3);
    margin-top: 20px;
}

.btn-save:hover {
    background: var(--primary-green);
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(27, 123, 75, 0.4);
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 1199px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .favorites-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 991px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-sidebar {
        position: static;
        margin-bottom: 30px;
    }
    
    .dashboard-menu {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .dashboard-menu li {
        flex: 1 1 auto;
    }
    
    .dashboard-menu a {
        padding: 12px 15px;
        justify-content: center;
    }
    
    .dashboard-menu a i {
        margin-right: 5px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .favorites-grid {
        grid-template-columns: 1fr;
    }
    
    .orders-table {
        display: block;
        overflow-x: auto;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .btn-view-all {
        width: 100%;
        text-align: center;
    }
}

@media (max-width: 576px) {
    .dashboard-content {
        padding: 20px;
    }
    
    .dashboard-menu {
        flex-direction: column;
    }
    
    .dashboard-menu a {
        justify-content: flex-start;
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
    }
    
    .profile-avatar span {
        font-size: 32px;
    }
}
</style>

<!-- ============================================
     DASHBOARD CLIENT AGF
     ============================================ -->
<section class="dashboard-page">
    <div class="container">
        
        <!-- En-tête du dashboard -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1>Bonjour, Jean Dupont !</h1>
                <p>
                    <i class="bi bi-house-door"></i>
                    Bienvenue dans votre espace personnel African Green Farmers
                </p>
            </div>
            <div class="last-login">
                <i class="bi bi-clock-history"></i>
                Dernière connexion : <strong>18/02/2026 à 14:30</strong>
            </div>
        </div>

        <!-- Grille principale -->
        <div class="dashboard-grid">
            
            <!-- Menu latéral gauche -->
            <aside class="dashboard-sidebar">
                
                <!-- Profil utilisateur -->
                <div class="user-profile">
                    <div class="profile-avatar">
                        <span>JD</span>
                        <div class="avatar-edit" onclick="editAvatar()">
                            <i class="bi bi-camera"></i>
                        </div>
                    </div>
                    <h3>Jean Dupont</h3>
                    <p><i class="bi bi-envelope"></i> jean.dupont@email.com</p>
                    <p><i class="bi bi-telephone"></i> +33 6 12 34 56 78</p>
                </div>

                <!-- Menu de navigation -->
                <ul class="dashboard-menu">
                    <li class="active">
                        <a href="#dashboard" onclick="showSection('dashboard')">
                            <i class="bi bi-speedometer2"></i>
                            Tableau de bord
                        </a>
                    </li>
                    <li>
                        <a href="#commandes" onclick="showSection('commandes')">
                            <i class="bi bi-bag-check"></i>
                            Mes commandes
                            <span class="menu-badge">3</span>
                        </a>
                    </li>
                    <li>
                        <a href="#favoris" onclick="showSection('favoris')">
                            <i class="bi bi-heart"></i>
                            Mes favoris
                            <span class="menu-badge">5</span>
                        </a>
                    </li>
                    <li>
                        <a href="#adresses" onclick="showSection('adresses')">
                            <i class="bi bi-geo-alt"></i>
                            Adresses de livraison
                        </a>
                    </li>
                    <li>
                        <a href="#paiement" onclick="showSection('paiement')">
                            <i class="bi bi-credit-card"></i>
                            Moyens de paiement
                        </a>
                    </li>
                    <li>
                        <a href="#profil" onclick="showSection('profil')">
                            <i class="bi bi-person"></i>
                            Mes informations
                        </a>
                    </li>
                    <li>
                        <a href="#consultations" onclick="showSection('consultations')">
                            <i class="bi bi-camera-video"></i>
                            Consultations
                        </a>
                    </li>
                    <li>
                        <a href="#factures" onclick="showSection('factures')">
                            <i class="bi bi-file-text"></i>
                            Factures
                        </a>
                    </li>
                </ul>

                <!-- Bouton déconnexion -->
                <button class="btn-logout" onclick="logout()">
                    <i class="bi bi-box-arrow-right"></i>
                    Déconnexion
                </button>
            </aside>

            <!-- Contenu principal -->
            <main class="dashboard-content">
                
                <!-- SECTION 1 : TABLEAU DE BORD (accueil) -->
                <div id="dashboard-section" class="content-section active">
                    
                    <!-- Cartes statistiques -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-bag"></i>
                            </div>
                            <div class="stat-value">12</div>
                            <div class="stat-label">Commandes totales</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div class="stat-value">3</div>
                            <div class="stat-label">Commandes en cours</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-star"></i>
                            </div>
                            <div class="stat-value">5</div>
                            <div class="stat-label">Produits favoris</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-camera-video"></i>
                            </div>
                            <div class="stat-value">2</div>
                            <div class="stat-label">Consultations</div>
                        </div>
                    </div>

                    <!-- Commandes récentes -->
                    <div class="section-header">
                        <h2><i class="bi bi-clock-history"></i> Commandes récentes</h2>
                        <a href="#" class="btn-view-all" onclick="showSection('commandes')">Voir toutes les commandes</a>
                    </div>

                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Date</th>
                                <th>Produits</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#AGF-2026-02-15</strong></td>
                                <td>15/02/2026</td>
                                <td>Masques Enfant (200pcs)</td>
                                <td class="order-total">1 400,20 €</td>
                                <td><span class="order-status status-delivered">Livrée</span></td>
                                <td><a href="#" class="btn-order-detail">Détail</a></td>
                            </tr>
                            <tr>
                                <td><strong>#AGF-2026-02-10</strong></td>
                                <td>10/02/2026</td>
                                <td>Pizzeria jetable (7pcs)</td>
                                <td class="order-total">693,01 €</td>
                                <td><span class="order-status status-shipped">Expédiée</span></td>
                                <td><a href="#" class="btn-order-detail">Détail</a></td>
                            </tr>
                            <tr>
                                <td><strong>#AGF-2026-02-05</strong></td>
                                <td>05/02/2026</td>
                                <td>Moringa bio (120 sachets)</td>
                                <td class="order-total">12 500 FCFA</td>
                                <td><span class="order-status status-processing">En traitement</span></td>
                                <td><a href="#" class="btn-order-detail">Détail</a></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Produits favoris -->
                    <div class="section-header" style="margin-top: 40px;">
                        <h2><i class="bi bi-heart"></i> Produits favoris</h2>
                        <a href="#" class="btn-view-all" onclick="showSection('favoris')">Voir tous les favoris</a>
                    </div>

                    <div class="favorites-grid">
                        <div class="favorite-card">
                            <div class="fav-image">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <div class="fav-info">
                                <h4>Moringa bio</h4>
                                <p>12 500 FCFA</p>
                            </div>
                            <button class="btn-fav" onclick="removeFromFavorites(1)">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>

                        <div class="favorite-card">
                            <div class="fav-image">
                                <i class="fas fa-capsules"></i>
                            </div>
                            <div class="fav-info">
                                <h4>Artemisia Plus</h4>
                                <p>15 000 FCFA</p>
                            </div>
                            <button class="btn-fav" onclick="removeFromFavorites(2)">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>

                        <div class="favorite-card">
                            <div class="fav-image">
                                <i class="fas fa-seedling"></i>
                            </div>
                            <div class="fav-info">
                                <h4>Engrais neem</h4>
                                <p>8 500 FCFA</p>
                            </div>
                            <button class="btn-fav" onclick="removeFromFavorites(3)">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Activités récentes -->
                    <div class="section-header" style="margin-top: 40px;">
                        <h2><i class="bi bi-activity"></i> Activités récentes</h2>
                    </div>

                    <div class="activities-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="bi bi-bag-check"></i>
                            </div>
                            <div class="activity-content">
                                <p>Votre commande <strong>#AGF-2026-02-15</strong> a été livrée</p>
                                <span class="activity-time">
                                    <i class="bi bi-clock"></i>
                                    Il y a 2 jours
                                </span>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="bi bi-star"></i>
                            </div>
                            <div class="activity-content">
                                <p>Vous avez ajouté <strong>Moringa bio</strong> à vos favoris</p>
                                <span class="activity-time">
                                    <i class="bi bi-clock"></i>
                                    Il y a 5 jours
                                </span>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="bi bi-camera-video"></i>
                            </div>
                            <div class="activity-content">
                                <p>Consultation avec <strong>Dr. Marie Claire</strong> terminée</p>
                                <span class="activity-time">
                                    <i class="bi bi-clock"></i>
                                    Il y a 1 semaine
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2 : MES COMMANDES (cachée par défaut) -->
                <div id="commandes-section" class="content-section" style="display: none;">
                    <div class="section-header">
                        <h2><i class="bi bi-bag-check"></i> Mes commandes</h2>
                    </div>

                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Date</th>
                                <th>Produits</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#AGF-2026-02-15</strong></td>
                                <td>15/02/2026</td>
                                <td>Masques Enfant (200pcs)</td>
                                <td class="order-total">1 400,20 €</td>
                                <td><span class="order-status status-delivered">Livrée</span></td>
                                <td><a href="#" class="btn-order-detail">Suivre</a></td>
                            </tr>
                            <tr>
                                <td><strong>#AGF-2026-02-10</strong></td>
                                <td>10/02/2026</td>
                                <td>Pizzeria jetable (7pcs)</td>
                                <td class="order-total">693,01 €</td>
                                <td><span class="order-status status-shipped">Expédiée</span></td>
                                <td><a href="#" class="btn-order-detail">Suivre</a></td>
                            </tr>
                            <tr>
                                <td><strong>#AGF-2026-02-05</strong></td>
                                <td>05/02/2026</td>
                                <td>Moringa bio (120 sachets)</td>
                                <td class="order-total">12 500 FCFA</td>
                                <td><span class="order-status status-processing">En traitement</span></td>
                                <td><a href="#" class="btn-order-detail">Suivre</a></td>
                            </tr>
                            <tr>
                                <td><strong>#AGF-2026-01-28</strong></td>
                                <td>28/01/2026</td>
                                <td>Stimulant immunitaire</td>
                                <td class="order-total">19 800 FCFA</td>
                                <td><span class="order-status status-delivered">Livrée</span></td>
                                <td><a href="#" class="btn-order-detail">Détail</a></td>
                            </tr>
                            <tr>
                                <td><strong>#AGF-2026-01-15</strong></td>
                                <td>15/01/2026</td>
                                <td>Engrais neem, Moringa</td>
                                <td class="order-total">21 000 FCFA</td>
                                <td><span class="order-status status-delivered">Livrée</span></td>
                                <td><a href="#" class="btn-order-detail">Détail</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- SECTION 3 : MES FAVORIS -->
                <div id="favoris-section" class="content-section" style="display: none;">
                    <div class="section-header">
                        <h2><i class="bi bi-heart"></i> Mes produits favoris</h2>
                    </div>

                    <div class="favorites-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="favorite-card">
                            <div class="fav-image">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <div class="fav-info">
                                <h4>Moringa bio</h4>
                                <p>12 500 FCFA</p>
                            </div>
                            <button class="btn-fav" onclick="removeFromFavorites(1)">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>

                        <div class="favorite-card">
                            <div class="fav-image">
                                <i class="fas fa-capsules"></i>
                            </div>
                            <div class="fav-info">
                                <h4>Artemisia Plus</h4>
                                <p>15 000 FCFA</p>
                            </div>
                            <button class="btn-fav" onclick="removeFromFavorites(2)">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>

                        <div class="favorite-card">
                            <div class="fav-image">
                                <i class="fas fa-seedling"></i>
                            </div>
                            <div class="fav-info">
                                <h4>Engrais neem</h4>
                                <p>8 500 FCFA</p>
                            </div>
                            <button class="btn-fav" onclick="removeFromFavorites(3)">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>

                        <div class="favorite-card">
                            <div class="fav-image">
                                <i class="fas fa-head-side-mask"></i>
                            </div>
                            <div class="fav-info">
                                <h4>Masque Enfant Type 2R</h4>
                                <p>0,10 €/pièce</p>
                            </div>
                            <button class="btn-fav" onclick="removeFromFavorites(4)">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>

                        <div class="favorite-card">
                            <div class="fav-image">
                                <i class="fas fa-pizza-slice"></i>
                            </div>
                            <div class="fav-info">
                                <h4>Pizzeria jetable XXL</h4>
                                <p>1,88 €/pièce</p>
                            </div>
                            <button class="btn-fav" onclick="removeFromFavorites(5)">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4 : MES INFORMATIONS PERSONNELLES -->
                <div id="profil-section" class="content-section" style="display: none;">
                    <div class="section-header">
                        <h2><i class="bi bi-person"></i> Mes informations personnelles</h2>
                    </div>

                    <form class="profile-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Prénom</label>
                                <input type="text" value="Jean" readonly>
                            </div>
                            <div class="form-group">
                                <label>Nom</label>
                                <input type="text" value="Dupont" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="jean.dupont@email.com" readonly>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Téléphone</label>
                                <input type="tel" value="+33 6 12 34 56 78">
                            </div>
                            <div class="form-group">
                                <label>Date de naissance</label>
                                <input type="date" value="1985-06-15">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Pays</label>
                            <select>
                                <option value="FR" selected>France</option>
                                <option value="BE">Belgique</option>
                                <option value="CH">Suisse</option>
                                <option value="LU">Luxembourg</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Langue préférée</label>
                            <select>
                                <option value="fr" selected>Français</option>
                                <option value="en">English</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-save">
                            <i class="bi bi-check-lg"></i>
                            Mettre à jour mes informations
                        </button>
                    </form>

                    <!-- Changement de mot de passe -->
                    <div style="margin-top: 50px; padding-top: 30px; border-top: 2px solid var(--border-light);">
                        <h3 style="color: var(--primary-green); font-size: 18px; margin-bottom: 20px;">Changer mon mot de passe</h3>
                        
                        <form class="profile-form">
                            <div class="form-group">
                                <label>Mot de passe actuel</label>
                                <input type="password" placeholder="********">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Nouveau mot de passe</label>
                                    <input type="password" placeholder="Minimum 8 caractères">
                                </div>
                                <div class="form-group">
                                    <label>Confirmer le mot de passe</label>
                                    <input type="password" placeholder="********">
                                </div>
                            </div>
                            <button type="submit" class="btn-save" style="background: var(--primary-green);">
                                <i class="bi bi-shield-lock"></i>
                                Mettre à jour le mot de passe
                            </button>
                        </form>
                    </div>
                </div>

                <!-- SECTION 5 : ADRESSES DE LIVRAISON -->
                <div id="adresses-section" class="content-section" style="display: none;">
                    <div class="section-header">
                        <h2><i class="bi bi-geo-alt"></i> Mes adresses de livraison</h2>
                        <button class="btn-view-all" onclick="addNewAddress()">
                            <i class="bi bi-plus-lg"></i> Ajouter une adresse
                        </button>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <!-- Adresse principale -->
                        <div style="background: #f8faf9; border-radius: 20px; padding: 25px; border: 2px solid var(--secondary-green);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <span style="background: var(--secondary-green); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">Principale</span>
                                <div>
                                    <button style="background: none; border: none; color: var(--text-muted; margin-right: 10px;"><i class="bi bi-pencil"></i></button>
                                    <button style="background: none; border: none; color: var(--danger);"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                            <h4 style="font-weight: 700; margin-bottom: 10px;">Jean Dupont</h4>
                            <p style="color: var(--text-muted); line-height: 1.6; margin-bottom: 10px;">
                                123 Rue de Paris<br>
                            75001 Paris<br>
                            France
                        </p>
                        <p style="color: var(--text-dark);"><i class="bi bi-telephone"></i> +33 6 12 34 56 78</p>
                    </div>

                    <!-- Adresse secondaire -->
                    <div style="background: #f8faf9; border-radius: 20px; padding: 25px; border: 1px solid var(--border-light);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <span style="background: var(--border-light); color: var(--text-muted); padding: 4px 12px; border-radius: 20px; font-size: 12px;">Secondaire</span>
                            <div>
                                <button style="background: none; border: none; color: var(--text-muted); margin-right: 10px;"><i class="bi bi-pencil"></i></button>
                                <button style="background: none; border: none; color: var(--danger);"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <h4 style="font-weight: 700; margin-bottom: 10px;">Jean Dupont</h4>
                        <p style="color: var(--text-muted); line-height: 1.6; margin-bottom: 10px;">
                            456 Avenue des Champs<br>
                            69000 Lyon<br>
                            France
                        </p>
                        <p style="color: var(--text-dark);"><i class="bi bi-telephone"></i> +33 6 98 76 54 32</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 6 : MOYENS DE PAIEMENT -->
            <div id="paiement-section" class="content-section" style="display: none;">
                <div class="section-header">
                    <h2><i class="bi bi-credit-card"></i> Mes moyens de paiement</h2>
                    <button class="btn-view-all" onclick="addNewCard()">
                        <i class="bi bi-plus-lg"></i> Ajouter une carte
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <!-- Carte principale -->
                    <div style="background: linear-gradient(145deg, var(--primary-green), var(--secondary-green)); border-radius: 20px; padding: 25px; color: white; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                            <i class="bi bi-credit-card-2-front" style="font-size: 40px;"></i>
                            <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 12px;">Principale</span>
                        </div>
                        <h4 style="font-size: 20px; letter-spacing: 2px; margin-bottom: 20px;">**** **** **** 4242</h4>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Jean DUPONT</span>
                            <span>12/28</span>
                        </div>
                        <div style="position: absolute; top: 25px; right: 25px; display: flex; gap: 10px;">
                            <button style="background: rgba(255,255,255,0.2); border: none; color: white; width: 30px; height: 30px; border-radius: 50%;"><i class="bi bi-pencil"></i></button>
                            <button style="background: rgba(255,255,255,0.2); border: none; color: white; width: 30px; height: 30px; border-radius: 50%;"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>

                    <!-- Carte secondaire -->
                    <div style="background: linear-gradient(145deg, #2c3e50, #34495e); border-radius: 20px; padding: 25px; color: white; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                            <i class="bi bi-credit-card-2-front" style="font-size: 40px;"></i>
                            <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 12px;">Secondaire</span>
                        </div>
                        <h4 style="font-size: 20px; letter-spacing: 2px; margin-bottom: 20px;">**** **** **** 1234</h4>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Jean DUPONT</span>
                            <span>06/27</span>
                        </div>
                        <div style="position: absolute; top: 25px; right: 25px; display: flex; gap: 10px;">
                            <button style="background: rgba(255,255,255,0.2); border: none; color: white; width: 30px; height: 30px; border-radius: 50%;"><i class="bi bi-pencil"></i></button>
                            <button style="background: rgba(255,255,255,0.2); border: none; color: white; width: 30px; height: 30px; border-radius: 50%;"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Historique des transactions -->
                <div style="margin-top: 40px;">
                    <h3 style="color: var(--primary-green); font-size: 18px; margin-bottom: 20px;">Dernières transactions</h3>
                    
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Montant</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>15/02/2026</td>
                                <td>Commande #AGF-2026-02-15</td>
                                <td class="order-total">1 400,20 €</td>
                                <td><span class="order-status status-delivered">Payé</span></td>
                            </tr>
                            <tr>
                                <td>10/02/2026</td>
                                <td>Commande #AGF-2026-02-10</td>
                                <td class="order-total">693,01 €</td>
                                <td><span class="order-status status-delivered">Payé</span></td>
                            </tr>
                            <tr>
                                <td>05/02/2026</td>
                                <td>Commande #AGF-2026-02-05</td>
                                <td class="order-total">12 500 FCFA</td>
                                <td><span class="order-status status-processing">En attente</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 7 : CONSULTATIONS -->
            <div id="consultations-section" class="content-section" style="display: none;">
                <div class="section-header">
                    <h2><i class="bi bi-camera-video"></i> Mes consultations</h2>
                    <a href="<?= base_url('consultation') ?>" class="btn-view-all">
                        <i class="bi bi-plus-lg"></i> Nouvelle consultation
                    </a>
                </div>

                <!-- Consultations passées -->
                <div style="margin-bottom: 40px;">
                    <h3 style="color: var(--primary-green); font-size: 16px; margin-bottom: 20px;">Consultations à venir</h3>
                    
                    <div style="background: #f8faf9; border-radius: 15px; padding: 25px; border-left: 4px solid var(--secondary-green); margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                            <div>
                                <h4 style="font-weight: 700; margin-bottom: 8px;">Dr. Marie Claire - Naturopathe</h4>
                                <p style="color: var(--text-muted); display: flex; align-items: center; gap: 15px;">
                                    <span><i class="bi bi-calendar"></i> 20/02/2026 à 14h30</span>
                                    <span><i class="bi bi-camera-video"></i> Visioconférence</span>
                                </p>
                            </div>
                            <div>
                                <button class="btn-order-detail" style="background: var(--secondary-green); color: white; border: none; padding: 10px 25px;">
                                    <i class="bi bi-camera-video"></i> Rejoindre
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 style="color: var(--primary-green); font-size: 16px; margin-bottom: 20px;">Consultations passées</h3>
                    
                    <div style="background: white; border: 1px solid var(--border-light); border-radius: 15px; padding: 20px; margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                            <div>
                                <h4 style="font-weight: 700; margin-bottom: 8px;">Dr. Jean Paul - Phytothérapeute</h4>
                                <p style="color: var(--text-muted); display: flex; align-items: center; gap: 15px;">
                                    <span><i class="bi bi-calendar"></i> 10/02/2026 à 10h00</span>
                                    <span><i class="bi bi-clock-history"></i> 45 minutes</span>
                                </p>
                            </div>
                            <div>
                                <span class="order-status status-delivered">Terminée</span>
                                <button class="btn-order-detail" style="margin-left: 10px;">
                                    <i class="bi bi-file-text"></i> Voir compte-rendu
                                </button>
                            </div>
                        </div>
                    </div>

                    <div style="background: white; border: 1px solid var(--border-light); border-radius: 15px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                            <div>
                                <h4 style="font-weight: 700; margin-bottom: 8px;">Dr. Alice Ndayishimiye - Nutritionniste</h4>
                                <p style="color: var(--text-muted); display: flex; align-items: center; gap: 15px;">
                                    <span><i class="bi bi-calendar"></i> 25/01/2026 à 15h00</span>
                                    <span><i class="bi bi-clock-history"></i> 30 minutes</span>
                                </p>
                            </div>
                            <div>
                                <span class="order-status status-delivered">Terminée</span>
                                <button class="btn-order-detail" style="margin-left: 10px;">
                                    <i class="bi bi-file-text"></i> Voir compte-rendu
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 8 : FACTURES -->
            <div id="factures-section" class="content-section" style="display: none;">
                <div class="section-header">
                    <h2><i class="bi bi-file-text"></i> Mes factures</h2>
                </div>

                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>N° Facture</th>
                            <th>Date</th>
                            <th>Commande associée</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Télécharger</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>FAC-2026-02-15</strong></td>
                            <td>15/02/2026</td>
                            <td>#AGF-2026-02-15</td>
                            <td class="order-total">1 400,20 €</td>
                            <td><span class="order-status status-delivered">Payée</span></td>
                            <td><a href="#" class="btn-order-detail"><i class="bi bi-download"></i> PDF</a></td>
                        </tr>
                        <tr>
                            <td><strong>FAC-2026-02-10</strong></td>
                            <td>10/02/2026</td>
                            <td>#AGF-2026-02-10</td>
                            <td class="order-total">693,01 €</td>
                            <td><span class="order-status status-delivered">Payée</span></td>
                            <td><a href="#" class="btn-order-detail"><i class="bi bi-download"></i> PDF</a></td>
                        </tr>
                        <tr>
                            <td><strong>FAC-2026-02-05</strong></td>
                            <td>05/02/2026</td>
                            <td>#AGF-2026-02-05</td>
                            <td class="order-total">12 500 FCFA</td>
                            <td><span class="order-status status-processing">En attente</span></td>
                            <td><a href="#" class="btn-order-detail" style="opacity: 0.5; pointer-events: none;"><i class="bi bi-download"></i> PDF</a></td>
                        </tr>
                        <tr>
                            <td><strong>FAC-2026-01-28</strong></td>
                            <td>28/01/2026</td>
                            <td>#AGF-2026-01-28</td>
                            <td class="order-total">19 800 FCFA</td>
                            <td><span class="order-status status-delivered">Payée</span></td>
                            <td><a href="#" class="btn-order-detail"><i class="bi bi-download"></i> PDF</a></td>
                        </tr>
                        <tr>
                            <td><strong>FAC-2026-01-15</strong></td>
                            <td>15/01/2026</td>
                            <td>#AGF-2026-01-15</td>
                            <td class="order-total">21 000 FCFA</td>
                            <td><span class="order-status status-delivered">Payée</span></td>
                            <td><a href="#" class="btn-order-detail"><i class="bi bi-download"></i> PDF</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    </div>
</section>

<!-- Script pour la navigation entre sections -->
<script>
function showSection(sectionName) {
    // Cacher toutes les sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Afficher la section sélectionnée
    document.getElementById(sectionName + '-section').style.display = 'block';
    
    // Mettre à jour la classe active dans le menu
    document.querySelectorAll('.dashboard-menu li').forEach(item => {
        item.classList.remove('active');
    });
    
    // Trouver et activer l'élément de menu correspondant
    event.target.closest('li').classList.add('active');
}

// Fonctions utilitaires
function editAvatar() {
    alert('Fonctionnalité : Changer la photo de profil');
}

function removeFromFavorites(productId) {
    if (confirm('Retirer ce produit de vos favoris ?')) {
        alert('Produit retiré des favoris (simulation)');
    }
}

function logout() {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
        window.location.href = '<?= base_url("logout") ?>';
    }
}

function addNewAddress() {
    alert('Fonctionnalité : Ajouter une nouvelle adresse');
}

function addNewCard() {
    alert('Fonctionnalité : Ajouter une nouvelle carte de paiement');
}

// Initialisation : s'assurer que la section dashboard est visible
document.addEventListener('DOMContentLoaded', function() {
    // Par défaut, le dashboard est actif
});
</script>

<!-- Font Awesome et Bootstrap Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>