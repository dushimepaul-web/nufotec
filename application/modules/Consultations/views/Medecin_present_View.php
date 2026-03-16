<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style type="text/css">
:root {
    --primary: #0f4c3a;
    --primary-light: #1a6b52;
    --primary-dark: #0a3326;
    --accent: #d4af37;
    --light: #f8f9fa;
    --dark: #212529;
    --gray: #6c757d;
    --success: #28a745;
    --shadow: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
    --radius: 12px;
}

.teleconsultation-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    padding: 60px 0;
    position: relative;
    overflow: hidden;
}

.teleconsultation-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 600px;
    height: 600px;
    background: rgba(212, 175, 55, 0.1);
    border-radius: 50%;
    animation: pulse 4s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.header-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: white;
}

.header-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(212, 175, 55, 0.2);
    border: 1px solid var(--accent);
    color: var(--accent);
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 20px;
}

.header-title {
    font-family: 'Playfair Display', serif;
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 16px;
}

.header-subtitle {
    font-size: 18px;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

.filters-section {
    background: white;
    padding: 30px 0;
    border-bottom: 1px solid #dee2e6;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: var(--shadow);
}

.filters-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: center;
    justify-content: space-between;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 300px;
    max-width: 500px;
}

.search-box input {
    width: 100%;
    padding: 14px 20px 14px 50px;
    border: 2px solid #dee2e6;
    border-radius: 50px;
    font-size: 15px;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(15, 76, 58, 0.1);
}

.search-box i {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    font-size: 18px;
}

.stats-bar {
    display: flex;
    gap: 30px;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: var(--gray);
}

.stat-item i {
    color: var(--accent);
    font-size: 20px;
}

.stat-item strong {
    color: var(--primary);
    font-size: 18px;
}

.doctors-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -15px;
    padding: 40px 0;
}

.doctor-col {
    flex: 0 0 50%;
    max-width: 50%;
    padding: 0 15px;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .doctor-col {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

.doctor-card {
    background: white;
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.doctor-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-light);
}

.doctor-header {
    padding: 25px;
    background: linear-gradient(135deg, var(--light) 0%, white 100%);
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.doctor-avatar {
    position: relative;
    flex-shrink: 0;
}

.doctor-avatar img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: var(--shadow-lg);
}

.status-badge {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 3px solid white;
    background: var(--success);
    animation: pulse-status 2s infinite;
}

.status-badge.offline {
    background: #dc3545;
    animation: none;
}

@keyframes pulse-status {
    0%, 100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
    50% { box-shadow: 0 0 0 8px rgba(40, 167, 69, 0); }
}

.doctor-main-info {
    flex: 1;
    min-width: 0;
}

.doctor-name {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-dark);
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.verified-badge {
    color: var(--accent);
    font-size: 16px;
}

.doctor-specialty {
    color: var(--primary);
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}

.doctor-rating {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.stars {
    color: #ffc107;
    font-size: 13px;
}

.rating-text {
    font-size: 12px;
    color: var(--gray);
}

.doctor-body {
    padding: 0 25px 20px;
    flex: 1;
}

.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #dee2e6;
    font-size: 13px;
    color: var(--gray);
}

.info-list li i {
    color: var(--primary);
    font-size: 14px;
    width: 18px;
    text-align: center;
}

.info-list li strong {
    color: var(--dark);
    margin-left: auto;
    font-weight: 600;
    font-size: 12px;
}

.horaires-preview {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #dee2e6;
}

.horaires-preview h6 {
    font-size: 12px;
    color: var(--primary);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.horaires-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.horaire-tag {
    background: rgba(15, 76, 58, 0.1);
    color: var(--primary);
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 500;
}

.horaire-tag.more {
    background: var(--accent);
    color: var(--primary-dark);
}

.doctor-footer {
    padding: 20px 25px;
    background: var(--light);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-top: auto;
    flex-wrap: wrap;
}

.price-block {
    text-align: left;
}

.price-label {
    font-size: 11px;
    color: var(--gray);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 4px;
}

.price-value {
    font-size: 30px;
    font-weight: 700;
    color: var(--primary);
    display: flex;
    align-items: baseline;
    gap: 4px;
}

.price-value span {
    font-size: 25px;
    color: var(--gray);
    font-weight: 700;
}

.next-slot {
    font-size: 11px;
    color: var(--success);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 4px;
}

.doctor-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-consult {
    padding: 10px 18px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
    cursor: pointer;
    border: none;
    white-space: nowrap;
}

.btn-consult-primary {
    background: var(--primary);
    color: white;
    box-shadow: 0 4px 15px rgba(15, 76, 58, 0.3);
}

.btn-consult-primary:hover {
    background: var(--primary-light);
    transform: translateY(-2px);
    color: white;
}

.btn-consult-secondary {
    background: white;
    color: var(--primary);
    border: 2px solid var(--primary);
}

.btn-consult-secondary:hover {
    background: var(--primary);
    color: white;
}

/* Modal Détails */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.8);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
    padding: 20px;
    overflow-y: auto;
}

.modal-overlay.active {
    display: flex;
}

.modal-container {
    background: white;
    border-radius: var(--radius);
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-header {
    padding: 25px;
    background: var(--primary);
    color: white;
    position: relative;
}

.modal-header h3 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 24px;
}

.modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.modal-close:hover {
    background: rgba(255,255,255,0.2);
}

.modal-body {
    padding: 30px;
}

.doctor-detail-header {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #dee2e6;
}

.doctor-detail-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.doctor-detail-info h2 {
    margin: 0 0 10px 0;
    color: var(--primary-dark);
}

.detail-section {
    margin-bottom: 25px;
}

.detail-section h4 {
    color: var(--primary);
    margin-bottom: 15px;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.horaires-table {
    width: 100%;
    border-collapse: collapse;
}

.horaires-table th,
.horaires-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

.horaires-table th {
    background: var(--light);
    color: var(--primary);
    font-weight: 600;
}

.horaires-table .badge-actif {
    background: var(--success);
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
}

.horaires-table .badge-inactif {
    background: #6c757d;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
}

.why-choose-section {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    padding: 80px 0;
    margin-top: 60px;
    color: white;
    position: relative;
}

.why-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
}

.why-item {
    text-align: center;
    padding: 30px;
    background: rgba(255,255,255,0.05);
    border-radius: var(--radius);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
}

.why-item:hover {
    transform: translateY(-5px);
    background: rgba(255,255,255,0.1);
    border-color: var(--accent);
}

.why-icon {
    width: 70px;
    height: 70px;
    background: var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 28px;
    color: var(--primary-dark);
}

.why-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 10px;
}

.why-text {
    font-size: 15px;
    opacity: 0.9;
}
</style>




<!-- Header -->
<section class="teleconsultation-header">
    <div class="container">
        <div class="header-content">
            <div class="header-badge">
                <i class="bi bi-camera-video-fill"></i>
                Service available 24/7
            </div>
            <h1 class="header-title">Our Expert Doctors</h1>
            <p class="header-subtitle">
                Consult our doctors specialized in phytotherapy and natural medicine. Book your appointment online now.
            </p>
        </div>
    </div>
</section>

<!-- Filtres -->
<section class="filters-section">
    <div class="container">
        <div class="filters-container">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchDoctor" placeholder="Search by name, specialty...">
            </div>
            <div class="filter-group">
                <select class="filter-select" id="specialtyFilter">
                    <option value="">All specialties</option>
                    <?php 
                    $specialites_uniques = [];
                    foreach ($medecins as $medecin) {
                        $spec = $medecin['specialite'] ?? 'Généraliste';
                        if (!in_array($spec, $specialites_uniques)) {
                            $specialites_uniques[] = $spec;
                            echo '<option value="'.htmlspecialchars($spec).'">'.htmlspecialchars($spec).'</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Contenu principal -->
<section class="container">
    <!-- Statistiques -->
    <div class="stats-bar">
        <div class="stat-item">
            <i class="bi bi-people-fill"></i>
            <div><strong id="totalDoctors"><?= count($medecins) ?></strong> doctors</div>
        </div>
        <div class="stat-item">
            <i class="bi bi-circle-fill text-success"></i>
            <div>
                <strong id="onlineDoctors">
                    <?php
                    $online_count = 0;
                    foreach ($medecins as $medecin) {
                        if (!empty($medecin['est_disponible'])) $online_count++;
                    }
                    echo $online_count;
                    ?>
                </strong> available
            </div>
        </div>
    </div>



    

    <!-- Grille médecins -->
    <div class="doctors-row" id="doctorsGrid">
        <?php if (!empty($medecins)): ?>
            <?php foreach ($medecins as $medecin): 
                // Données du médecin
                $is_online = !empty($medecin['est_disponible']);
                $photo = !empty($medecin['photo']) ? base_url('attachments/Users/'.$medecin['photo']) : base_url('assets/images/default-doctor.png');
                $nom_complet = 'Dr. ' . htmlspecialchars(($medecin['prenom'] ?? '') . ' ' . ($medecin['nom'] ?? ''));
                $specialite_nom = htmlspecialchars($medecin['specialite'] ?? 'Médecin Généraliste');
                $note = number_format($medecin['note_moyenne'] ?? 0, 1);
                $nb_avis = $medecin['nombre_avis'] ?? 0;
                $experience = ($medecin['annees_experience'] ?? 0) . ' years of experience';
                $langues = !empty($medecin['langues_parlees']) ? $medecin['langues_parlees'] : 'French';
                $tarif = $medecin['honoraires_consultation'] ?? 0;
                $devise = $medecin['currency'] ?? 'BIF';

                // Horaires
                $horaires_list = [];
                if (!empty($medecin['horaires'])) {
                    foreach ($medecin['horaires'] as $h) {
                        if ($h['est_actif'] == 1) {
                            $horaires_list[] = [
                                'jour' => ucfirst($h['jour_semaine']),
                                'debut' => substr($h['heure_debut'], 0, 5),
                                'fin' => substr($h['heure_fin'], 0, 5)
                            ];
                        }
                    }
                }

                // Prochain créneau
                $prochain_slot = 'By appointment';
                if (!empty($horaires_list)) {
                    $jours_fr = ['Monday'=>'Lundi','Tuesday'=>'Mardi','Wednesday'=>'Mercredi','Thursday'=>'Jeudi','Friday'=>'Vendredi','Saturday'=>'Samedi','Sunday'=>'Dimanche'];
                    $aujourdhui = $jours_fr[date('l')] ?? '';
                    foreach ($horaires_list as $h) {
                        if ($h['jour'] === $aujourdhui) {
                            $prochain_slot = "Today " . $h['debut'];
                            break;
                        }
                    }
                }
            ?>
            
            <!-- Carte Médecin -->
            <div class="doctor-col" data-specialty="<?= strtolower(str_replace(' ', '-', $specialite_nom)) ?>">
                <div class="doctor-card">
                    
                    <!-- En-tête avec avatar -->
                    <div class="doctor-header">
                        <div class="doctor-avatar">
                            <img src="<?= $photo ?>" alt="<?= $nom_complet ?>" onerror="this.src='<?= base_url('assets/images/default-doctor.png') ?>'">
                            <span class="status-badge <?= $is_online ? '' : 'offline' ?>" title="<?= $is_online ? 'Available' : 'Unavailable' ?>"></span>
                        </div>
                        <div class="doctor-main-info">
                            <h3 class="doctor-name">
                                <?= $nom_complet ?>
                                <?php if (!empty($medecin['est_verifie'])): ?>
                                    <i class="bi bi-patch-check-fill verified-badge" title="Verified doctor"></i>
                                <?php endif; ?>
                            </h3>
                            <div class="doctor-specialty"><?= $specialite_nom ?></div>
                            <div class="doctor-rating">
                                <span class="stars">
                                    <?php 
                                    $note_entier = floor($note);
                                    for($i = 1; $i <= 5; $i++):
                                        if($i <= $note_entier): ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php elseif($i - 0.5 <= $note): ?>
                                            <i class="bi bi-star-half"></i>
                                        <?php else: ?>
                                            <i class="bi bi-star"></i>
                                        <?php endif;
                                    endfor; ?>
                                </span>
                                <span class="rating-text">
                                    <strong><?= $note > 0 ? $note : '-' ?></strong> (<?= $nb_avis ?> reviews)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Corps de la carte -->
                    <div class="doctor-body">
                        <ul class="info-list">
                            <li><i class="bi bi-award"></i> Experience: <strong><?= $experience ?></strong></li>
                            <li><i class="bi bi-globe"></i> Languages: <strong><?= htmlspecialchars($langues) ?></strong></li>
                            <li><i class="bi bi-shield-check"></i> License: <strong><?= htmlspecialchars($medecin['numero_licence'] ?? 'N/A') ?></strong></li>
                        </ul>
                        
                        <?php if (!empty($horaires_list)): ?>
                        <div class="horaires-preview">
                            <h6><i class="bi bi-clock"></i> Schedule</h6>
                            <div class="horaires-tags">
                                <?php foreach(array_slice($horaires_list, 0, 3) as $h): ?>
                                    <span class="horaire-tag"><?= $h['jour'] ?> <?= $h['debut'] ?>-<?= $h['fin'] ?></span>
                                <?php endforeach; ?>
                                <?php if(count($horaires_list) > 3): ?>
                                    <span class="horaire-tag more">+<?= count($horaires_list)-3 ?> others</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pied de carte avec actions -->
                    <div class="doctor-footer">
                        <div class="price-block">
                            <div class="price-label">Consultation</div>
                            <div class="price-value"><?= $tarif ?> <span><?= $devise ?></span></div>
                            <?php if($is_online): ?>
                                <div class="next-slot"><i class="bi bi-lightning-charge-fill"></i> <?= $prochain_slot ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="doctor-actions">
                            <button type="button" class="btn-consult btn-consult-secondary" onclick="openDetailModal(<?= $medecin['id'] ?>)">
                                <i class="bi bi-eye"></i> Details
                            </button>
                            
                            <?php if($is_online): ?>
    <?php if($this->session->userdata('user_id')): ?>
        
        <!-- Déjà connecté → lien direct vers le formulaire de rendez-vous -->
        <a href="<?= base_url('Consultations/PatientForm?doctor_uuid='.$medecin['uuid']) ?>" class="btn-consult btn-consult-primary btn-lg">
            <i class="bi bi-calendar-plus"></i> Book appointment
        </a>
    <?php else: ?>
        <!-- Non connecté → formulaire POST vers Auth pour sélectionner le médecin -->
        <form method="POST" action="<?= base_url('Auth') ?>" class="d-inline">
            <input type="hidden" name="selected_doctor_uuid" value="<?= $medecin['uuid'] ?>">
            <button type="submit" class="btn-consult btn-consult-primary btn-lg">
                <i class="bi bi-calendar-plus"></i> Book appointment
            </button>
        </form>
    <?php endif; ?>
<?php else: ?>
    <button class="btn btn-secondary btn-lg" disabled>
        <i class="bi bi-clock"></i> Doctor unavailable
    </button>
<?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- MODAL Détails Médecin -->
                <div class="modal-overlay" id="detailModal<?= $medecin['id'] ?>">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3><i class="bi bi-person-badge"></i> Doctor Profile</h3>
                            <button type="button" class="modal-close" onclick="closeModal('detailModal<?= $medecin['id'] ?>')">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        
                        <div class="modal-body">
                            <!-- En-tête du modal -->
                            <div class="doctor-detail-header">
                                <img src="<?= $photo ?>" alt="<?= $nom_complet ?>" class="doctor-detail-avatar">
                                <div class="doctor-detail-info">
                                    <h2><?= $nom_complet ?></h2>
                                    <p class="text-muted"><?= $specialite_nom ?></p>
                                    <div class="mb-2">
                                        <?php if(!empty($medecin['est_verifie'])): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Verified</span>
                                        <?php endif; ?>
                                        <span class="badge <?= $is_online ? 'bg-success' : 'bg-secondary' ?>">
                                            <i class="bi bi-circle-fill"></i> <?= $is_online ? 'Available' : 'Unavailable' ?>
                                        </span>
                                    </div>
                                    <div class="text-warning">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= $i <= round($note) ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                        <span class="text-muted ms-2">(<?= $nb_avis ?> reviews)</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Colonne gauche -->
                                <div class="col-md-6">
                                    <div class="detail-section">
                                        <h4><i class="bi bi-info-circle"></i> Information</h4>
                                        <table class="table table-borderless table-sm">
                                            <tr><td class="text-muted">License:</td><td class="fw-bold"><?= htmlspecialchars($medecin['numero_licence'] ?? 'N/A') ?></td></tr>
                                            <tr><td class="text-muted">Experience:</td><td><?= $experience ?></td></tr>
                                            <tr><td class="text-muted">Languages:</td><td><?= htmlspecialchars($langues) ?></td></tr>
                                            <tr><td class="text-muted">Email:</td><td><?= htmlspecialchars($medecin['email'] ?? 'Confidential') ?></td></tr>
                                            <tr><td class="text-muted">Phone:</td><td><?= htmlspecialchars($medecin['telephone'] ?? 'Confidential') ?></td></tr>
                                        </table>
                                    </div>
                                    
                                    <?php if(!empty($medecin['diplomes'])): ?>
                                        <div class="detail-section">
                                            <h4><i class="bi bi-award"></i> Diplomas</h4>
                                            <div class="bg-light p-3 rounded">
                                                <?= nl2br(htmlspecialchars($medecin['diplomes'])) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Colonne droite -->
                                <div class="col-md-6">
                                    <div class="detail-section">
                                        <h4><i class="bi bi-clock"></i> Consultation Hours</h4>
                                        <?php if(!empty($horaires_list)): ?>
                                            <table class="horaires-table">
                                                <thead>
                                                    <tr><th>Day</th><th>Start</th><th>End</th></tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($horaires_list as $h): ?>
                                                    <tr>
                                                        <td class="text-capitalize fw-bold"><?= $h['jour'] ?></td>
                                                        <td><?= $h['debut'] ?></td>
                                                        <td><?= $h['fin'] ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle"></i> No schedule defined.
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="detail-section">
                                        <h4><i class="bi bi-currency-exchange"></i> Pricing</h4>
                                        <div class="bg-primary text-white px-4 py-3 rounded">
                                            <div class="fs-3 fw-bold"><?= $tarif ?> <?= $devise ?></div>
                                            <div class="small">per consultation</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bouton d'action dans le modal -->
                            <div class="text-center mt-4">
                                <?php if($is_online): ?>
    <?php if($this->session->userdata('user_id')): ?>
        <!-- Déjà connecté → rediriger directement vers PatientForm -->
        <form method="POST" action="<?= base_url('Consultations/PatientForm') ?>">
            <input type="hidden" name="doctor_uuid" value="<?= $medecin['uuid'] ?>">
            <button type="submit" class="btn-consult btn-consult-primary btn-lg">
                <i class="bi bi-calendar-plus"></i> Book appointment
            </button>
        </form>
    <?php else: ?>
        <!-- Non connecté → rediriger vers Auth -->
        <form method="POST" action="<?= base_url('Auth') ?>" class="redirect-auth-form">
            <input type="hidden" name="selected_doctor_uuid" value="<?= $medecin['uuid'] ?>">
            <button type="submit" class="btn-consult btn-consult-primary btn-lg">
                <i class="bi bi-calendar-plus"></i> Book appointment
            </button>
        </form>
    <?php endif; ?>
<?php else: ?>
    <button class="btn btn-secondary btn-lg" disabled>
        <i class="bi bi-clock"></i> Doctor unavailable
    </button>
<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-emoji-frown" style="font-size: 3rem; color: #ccc;"></i>
                <p class="mt-3 text-muted">No doctors available at the moment</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Section Pourquoi nous choisir -->
<section class="why-choose-section">
    <div class="container">
        <div class="why-grid">
            <?php 
            $features = [
                ['icon' => 'bi-clock-history', 'title' => '24/7 Consultations', 'text' => 'Access our doctors anytime'],
                ['icon' => 'bi-shield-check', 'title' => 'Certified doctors', 'text' => 'All our practitioners are qualified and verified'],
                ['icon' => 'bi-currency-exchange', 'title' => 'Affordable prices', 'text' => 'Prices adapted to all budgets'],
                ['icon' => 'bi-lock', 'title' => 'Confidentiality', 'text' => 'Your medical data is protected'],
            ];
            foreach($features as $f): ?>
                <div class="why-item">
                    <div class="why-icon"><i class="bi <?= $f['icon'] ?>"></i></div>
                    <h3 class="why-title"><?= $f['title'] ?></h3>
                    <p class="why-text"><?= $f['text'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // CONFIGURATION
    // ============================================
    const USER_LOGGED_IN = <?= $this->session->userdata('user_id') ? 'true' : 'false' ?>;

    // ============================================
    // GESTION DES MODALS
    // ============================================
    window.openDetailModal = function(medecinId) {
        const modal = document.getElementById('detailModal' + medecinId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    // Fermeture en cliquant sur l'overlay
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // ============================================
    // FILTRE DE RECHERCHE
    // ============================================
    const searchInput = document.getElementById('searchDoctor');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            
            document.querySelectorAll('.doctor-col').forEach(col => {
                const text = col.textContent.toLowerCase();
                col.style.display = text.includes(term) ? 'block' : 'none';
            });
            
            // Mise à jour du compteur visible
            updateVisibleCount();
        });
    }

    // ============================================
    // FILTRE PAR SPÉCIALITÉ
    // ============================================
    const specialtyFilter = document.getElementById('specialtyFilter');
    if (specialtyFilter) {
        specialtyFilter.addEventListener('change', function() {
            const specialty = this.value.toLowerCase().replace(/\s+/g, '-');
            
            document.querySelectorAll('.doctor-col').forEach(col => {
                const cardSpecialty = col.dataset.specialty;
                col.style.display = (!specialty || cardSpecialty === specialty) ? 'block' : 'none';
            });
            
            updateVisibleCount();
        });
    }

    // ============================================
    // COMPTER LES MÉDECINS VISIBLES
    // ============================================
    function updateVisibleCount() {
        const visibleCount = Array.from(document.querySelectorAll('.doctor-col'))
            .filter(col => col.style.display !== 'none').length;
        
        // Optionnel : afficher le nombre de résultats
        const counterEl = document.getElementById('visibleDoctors');
        if (counterEl) {
            counterEl.textContent = visibleCount;
        }
    }

    // ============================================
    // GESTION DES FORMULAIRES DE REDIRECTION
    // ============================================
    // Ajouter un indicateur de chargement lors de la soumission
    document.querySelectorAll('form[action*="Auth"], form[action*="PatientForm"]').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Redirection...';
                btn.disabled = true;
                
                // Restaurer après 5 secondes au cas où
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 5000);
            }
        });
    });
});
</script>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>
