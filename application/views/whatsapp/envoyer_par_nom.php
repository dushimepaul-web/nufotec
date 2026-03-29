<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoyer un message - WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #075E54 0%, #128C7E 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: none;
        }
        .card-header {
            background: linear-gradient(135deg, #075E54 0%, #128C7E 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .form-check {
            padding: 10px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s ease;
        }
        .form-check:hover {
            background: #f8f9fa;
        }
        .form-check-input:checked {
            background-color: #25D366;
            border-color: #25D366;
        }
        .btn-success {
            background: #25D366;
            border: none;
            padding: 12px 30px;
            font-weight: bold;
            transition: transform 0.3s ease;
        }
        .btn-success:hover {
            background: #128C7E;
            transform: translateY(-2px);
        }
        .btn-secondary {
            padding: 12px 30px;
            font-weight: bold;
        }
        .selected-count {
            background: #25D366;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
        }
        .group-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 10px;
        }
        .group-icon {
            color: #25D366;
            margin-right: 10px;
        }
        .group-id {
            font-size: 11px;
            color: #999;
            font-family: monospace;
        }
        .select-all {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        textarea {
            resize: none;
            font-size: 16px;
        }
        .counter {
            float: right;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fab fa-whatsapp"></i> Envoyer un message WhatsApp
                        </h4>
                        <p class="mb-0 mt-2 small">
                            <i class="fas fa-info-circle"></i> Sélectionnez les groupes qui recevront le message
                        </p>
                    </div>
                    <div class="card-body">
                        
                        <?php if($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($this->session->flashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($this->session->flashdata('warning')): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('warning') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?= site_url('whatsapp/traiter_envoi') ?>" id="formEnvoi">
                            
                            <!-- Sélection des groupes -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-users"></i> Groupes destinataires
                                    <span class="selected-count" id="selectedCount">0 sélectionné(s)</span>
                                </label>
                                
                                <div class="select-all">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label fw-bold" for="selectAll">
                                            <i class="fas fa-check-double"></i> Sélectionner tous les groupes
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="group-list">
                                    <?php if(empty($groupes)): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> Aucun groupe trouvé. 
                                            <a href="<?= site_url('whatsapp/synchroniser') ?>" class="alert-link">Synchronisez d'abord les groupes</a>.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach($groupes as $groupe): ?>
                                        <div class="form-check">
                                            <input class="form-check-input group-checkbox" 
                                                   type="checkbox" 
                                                   name="groupes_ids[]" 
                                                   value="<?= htmlspecialchars($groupe['groupe_id']) ?>" 
                                                   id="groupe_<?= $groupe['id'] ?>">
                                            <label class="form-check-label" for="groupe_<?= $groupe['id'] ?>">
                                                <i class="fab fa-whatsapp group-icon"></i>
                                                <strong><?= htmlspecialchars($groupe['nom']) ?></strong>
                                                <br>
                                                <small class="group-id">
                                                    <i class="fas fa-id-card"></i> ID: <?= htmlspecialchars($groupe['groupe_id']) ?>
                                                </small>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if(!empty($groupes)): ?>
                                <small class="text-muted mt-2 d-block">
                                    <i class="fas fa-info-circle"></i> 
                                    <?= $total_groupes ?> groupes disponibles. Sélectionnez ceux qui doivent recevoir le message.
                                </small>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Message -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-comment"></i> Message
                                </label>
                                <textarea name="message" 
                                          id="message" 
                                          class="form-control" 
                                          rows="6" 
                                          placeholder="Saisissez votre message ici..." 
                                          required></textarea>
                                <div class="counter">
                                    <span id="charCount">0</span> caractères
                                </div>
                            </div>
                            
                            <!-- Aperçu du message -->
                            <div class="mb-4" id="previewSection" style="display: none;">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-eye"></i> Aperçu du message
                                </label>
                                <div class="alert alert-info" id="messagePreview" style="white-space: pre-wrap;"></div>
                            </div>
                            
                            <!-- Boutons d'action -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="<?= site_url('whatsapp/liste_groupes') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </a>
                                <button type="submit" class="btn btn-success" id="btnEnvoyer">
                                    <i class="fas fa-paper-plane"></i> Envoyer le message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Information supplémentaire -->
                <div class="card mt-3">
                    <div class="card-body bg-light">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <i class="fas fa-shield-alt fa-2x text-success"></i>
                                <p class="small mt-2">Messages sécurisés<br>Chiffrement de bout en bout</p>
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-bolt fa-2x text-warning"></i>
                                <p class="small mt-2">Envoi instantané<br>Livraison rapide</p>
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-chart-line fa-2x text-info"></i>
                                <p class="small mt-2">Suivi des envois<br>Statistiques disponibles</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sélectionner tous les groupes
        const selectAllCheckbox = document.getElementById('selectAll');
        const groupCheckboxes = document.querySelectorAll('.group-checkbox');
        const selectedCountSpan = document.getElementById('selectedCount');
        const messageTextarea = document.getElementById('message');
        const charCountSpan = document.getElementById('charCount');
        const previewSection = document.getElementById('previewSection');
        const messagePreview = document.getElementById('messagePreview');
        
        // Mettre à jour le compteur de sélection
        function updateSelectedCount() {
            const checked = document.querySelectorAll('.group-checkbox:checked');
            const count = checked.length;
            selectedCountSpan.textContent = count + ' sélectionné(s)';
            
            if (count > 0) {
                selectedCountSpan.style.background = '#25D366';
            } else {
                selectedCountSpan.style.background = '#dc3545';
            }
        }
        
        // Sélectionner/déselectionner tous
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                groupCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateSelectedCount();
            });
        }
        
        // Mettre à jour le compteur quand on change la sélection
        groupCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();
                
                // Vérifier si tous sont sélectionnés
                const allChecked = document.querySelectorAll('.group-checkbox:checked').length === groupCheckboxes.length;
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked;
                }
            });
        });
        
        // Compteur de caractères et aperçu
        if (messageTextarea) {
            messageTextarea.addEventListener('input', function() {
                const count = this.value.length;
                charCountSpan.textContent = count;
                
                if (count > 0) {
                    previewSection.style.display = 'block';
                    messagePreview.textContent = this.value;
                } else {
                    previewSection.style.display = 'none';
                }
            });
        }
        
        // Initialiser le compteur
        updateSelectedCount();
        
        // Confirmation avant envoi
        const form = document.getElementById('formEnvoi');
        if (form) {
            form.addEventListener('submit', function(e) {
                const checked = document.querySelectorAll('.group-checkbox:checked');
                const message = messageTextarea.value.trim();
                
                if (checked.length === 0) {
                    e.preventDefault();
                    alert('Veuillez sélectionner au moins un groupe.');
                    return false;
                }
                
                if (message === '') {
                    e.preventDefault();
                    alert('Veuillez saisir un message.');
                    return false;
                }
                
                const confirmation = confirm(
                    'Êtes-vous sûr de vouloir envoyer ce message à ' + checked.length + ' groupe(s) ?\n\n' +
                    'Message :\n' + message.substring(0, 100) + (message.length > 100 ? '...' : '')
                );
                
                if (!confirmation) {
                    e.preventDefault();
                }
            });
        }
        
        // Empêcher l'envoi multiple
        const btnEnvoyer = document.getElementById('btnEnvoyer');
        if (btnEnvoyer) {
            form.addEventListener('submit', function() {
                btnEnvoyer.disabled = true;
                btnEnvoyer.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
            });
        }
        
        // Limiter la sélection à 10 groupes (optionnel)
        groupCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checked = document.querySelectorAll('.group-checkbox:checked');
                if (checked.length > 10) {
                    this.checked = false;
                    alert('Vous ne pouvez sélectionner que 10 groupes maximum par envoi.');
                }
                updateSelectedCount();
            });
        });
        
        console.log('✅ Formulaire d\'envoi WhatsApp prêt');
    </script>
</body>
</html>