<!-- Dashboard Patient -->
<div class="patient-dashboard">
    
    <!-- Header avec profil -->
    <div class="dashboard-header">
        <div class="patient-info">
            <img src="<?= base_url(!empty($patient['photo']) ? 'attachments/Users/' . $patient['photo'] : 'assets/frontend/img/default-avatar.jpg') ?>" 
                 alt="Photo" class="patient-avatar">
            <div>
                <h1>Bonjour, <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></h1>
                <p>ID Patient: <strong><?= $patient['uuid'] ?></strong></p>
            </div>
        </div>
        <div class="quick-actions">
            <a href="<?= base_url('PatientForm') ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nouvelle Consultation
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="bi bi-calendar-check"></i>
            <h3><?= $stats['total'] ?? 0 ?></h3>
            <p>Total Consultations</p>
        </div>
        <div class="stat-card warning">
            <i class="bi bi-hourglass-split"></i>
            <h3><?= $stats['en_attente'] ?? 0 ?></h3>
            <p>En Attente</p>
        </div>
        <div class="stat-card success">
            <i class="bi bi-check-circle"></i>
            <h3><?= $stats['terminees'] ?? 0 ?></h3>
            <p>Terminées</p>
        </div>
        <?php if ($messages_non_lus > 0): ?>
        <div class="stat-card danger">
            <i class="bi bi-chat-dots"></i>
            <h3><?= $messages_non_lus ?></h3>
            <p>Messages Non Lus</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Prochain RDV -->
    <?php if ($prochain_rdv): ?>
    <div class="next-appointment">
        <h3><i class="bi bi-calendar-event"></i> Prochain Rendez-vous</h3>
        <div class="appointment-card">
            <div class="doctor-info">
                <img src="<?= !empty($prochain_rdv['medecin_photo']) ? base_url('attachments/Users/' . $prochain_rdv['medecin_photo']) : base_url('assets/frontend/img/default-doctor.jpg') ?>" 
                     alt="Dr. <?= $prochain_rdv['nom'] ?>">
                <div>
                    <h4>Dr. <?= htmlspecialchars($prochain_rdv['prenom'] . ' ' . $prochain_rdv['nom']) ?></h4>
                    <p><?= htmlspecialchars($prochain_rdv['specialite']) ?></p>
                </div>
            </div>
            <div class="appointment-datetime">
                <i class="bi bi-clock"></i>
                <?= date('d/m/Y à H:i', strtotime($prochain_rdv['date_confirmee'])) ?>
            </div>
            <?php if ($prochain_rdv['room_url']): ?>
            <a href="<?= $prochain_rdv['room_url'] ?>" target="_blank" class="btn btn-success">
                <i class="bi bi-camera-video"></i> Rejoindre la consultation
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Historique récent -->
    <div class="recent-consultations">
        <h3>Consultations Récentes</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Médecin</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($consultations_recentes as $consultation): ?>
                <tr>
                    <td><?= $consultation['numero_consultation'] ?></td>
                    <td>
                        Dr. <?= htmlspecialchars(($consultation['medecin_prenom'] ?? '') . ' ' . ($consultation['medecin_nom'] ?? 'Non assigné')) ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($consultation['created_at'])) ?></td>
                    <td>
                        <span class="badge badge-<?= $consultation['statut'] ?>">
                            <?= ucfirst($consultation['statut']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= base_url('PatientDashboard/consultation_detail/' . $consultation['numero_consultation']) ?>" 
                           class="btn btn-sm btn-outline-primary">
                            Voir
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?= base_url('PatientDashboard/consultations') ?>" class="btn btn-link">
            Voir tout l'historique <i class="bi bi-arrow-right"></i>
        </a>
    </div>

</div>