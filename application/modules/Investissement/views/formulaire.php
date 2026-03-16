
<section class="invest-form-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <a href="javascript:history.back()" class="btn-back mb-4">
                    <i class="bi bi-arrow-left"></i> Retour aux projets
                </a>

                <div class="invest-main-card">
                    <div class="row g-0">
                        <div class="col-lg-5 invest-info-panel">
                            <div class="panel-content">
                                <span class="badge-status">En cours d'ouverture</span>
                                <h2 id="selected-project-title" class="text-white mt-3">Manifestation d'Intérêt</h2>
                                <p class="text-white-50">Vous avez choisi d'investir dans un projet stratégique d'AGF Phytomed. Veuillez remplir ce formulaire pour accéder au mémorandum d'information (IM).</p>
                                
                                <div class="benefits-list mt-5">
                                    <div class="benefit-item">
                                        <i class="bi bi-check-circle-fill text-accent"></i>
                                        <span>Accès prioritaire aux rapports financiers</span>
                                    </div>
                                    <div class="benefit-item">
                                        <i class="bi bi-check-circle-fill text-accent"></i>
                                        <span>Audit complet des installations disponible</span>
                                    </div>
                                    <div class="benefit-item">
                                        <i class="bi bi-check-circle-fill text-accent"></i>
                                        <span>Consultation avec la direction générale</span>
                                    </div>
                                </div>

                                <div class="secure-tag mt-5">
                                    <i class="bi bi-shield-lock-fill"></i>
                                    <span>Données cryptées et confidentielles</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7 invest-form-panel">
                            <form action="<?= base_url('Investissement/Submit') ?>" method="POST" class="p-5">
                                <h3 class="mb-4 text-primary-dark">Détails de l'Investisseur</h3>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nom Complet</label>
                                        <input type="text" class="form-control" placeholder="Ex: Jean Dupont" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Entreprise / Organisation</label>
                                        <input type="text" class="form-control" placeholder="Optionnel">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Adresse Email Professionnelle</label>
                                        <input type="email" class="form-control" placeholder="invest@votre-domaine.com" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Téléphone (WhatsApp de préférence)</label>
                                        <input type="tel" class="form-control" placeholder="+257 ..." required>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <label class="form-label">Capacité d'Investissement Estimée</label>
                                        <select class="form-select" required>
                                            <option value="" selected disabled>Choisir une tranche...</option>
                                            <option value="1">5.000.000 - 20.000.000 BIF</option>
                                            <option value="2">20.000.000 - 100.000.000 BIF</option>
                                            <option value="3">Plus de 100.000.000 BIF</option>
                                            <option value="4">Partenariat Technique (Non financier)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Message ou Questions Particulières</label>
                                        <textarea class="form-control" rows="4" placeholder="Dites-nous en plus sur vos attentes..."></textarea>
                                    </div>

                                    <div class="col-md-12 mt-4">
                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="nda" required>
                                            <label class="form-check-label small" for="nda">
                                                J'accepte de signer un accord de non-divulgation (NDA) pour consulter les documents confidentiels.
                                            </label>
                                        </div>
                                        <button type="submit" class="btn-submit-invest">
                                            Envoyer ma Candidature d'Investissement
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<style>
    .invest-form-page {
    padding: 150px 0 100px;
    background: #f8fafc;
    min-height: 100vh;
}

.btn-back {
    text-decoration: none;
    color: var(--primary);
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: 0.3s;
}

.btn-back:hover { transform: translateX(-5px); }

.invest-main-card {
    background: white;
    border-radius: 30px;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0,0,0,0.1);
}

/* Panel Gauche (Inspiration Médicale/Industrielle) */
.invest-info-panel {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    padding: 60px;
    position: relative;
}

.badge-status {
    background: rgba(212, 175, 55, 0.2);
    color: var(--accent);
    padding: 6px 15px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    color: white;
    font-size: 15px;
}

.secure-tag {
    background: rgba(255,255,255,0.05);
    border: 1px dashed rgba(255,255,255,0.2);
    padding: 15px;
    border-radius: 10px;
    color: rgba(255,255,255,0.7);
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
}

/* Formulaire */
.form-label {
    font-weight: 600;
    font-size: 14px;
    color: var(--dark);
    margin-bottom: 8px;
}

.form-control, .form-select {
    padding: 12px 15px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #fdfdfd;
}

.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(15, 76, 58, 0.1);
}

.btn-submit-invest {
    width: 100%;
    background: var(--accent);
    color: var(--primary-dark);
    border: none;
    padding: 18px;
    border-radius: 12px;
    font-weight: 800;
    font-size: 16px;
    transition: 0.3s;
    box-shadow: 0 10px 20px rgba(212, 175, 55, 0.2);
}

.btn-submit-invest:hover {
    background: var(--primary-dark);
    color: white;
    transform: translateY(-3px);
}

@media (max-width: 992px) {
    .invest-info-panel { padding: 30px; }
}
</style>
