<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Administration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Templates d'Emails</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#create_template">
                <i class="bx bx-plus"></i> Nouveau Template
            </a>
        </div>
    </div>

    <!-- Messages flash -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-check-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bx bx-error-circle fs-5 me-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-primary"><i class="bx bx-envelope me-2"></i>Gestion des Templates d'Emails</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="templatesTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Clé</th>
                            <th width="20%">Nom</th>
                            <th width="15%">Catégorie</th>
                            <th width="25%">Sujet</th>
                            <th width="10%">Statut</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($templates)): $i = 1; foreach ($templates as $value): 
                        
                        // Badges pour catégorie
                        $category_badges = [
                            'user' => '<span class="badge bg-info"><i class="bx bx-user me-1"></i>Utilisateur</span>',
                            'investor' => '<span class="badge bg-success"><i class="bx bx-chart me-1"></i>Investisseur</span>',
                            'broker' => '<span class="badge bg-warning text-dark"><i class="bx bx-transfer me-1"></i>Broker</span>',
                            'partner' => '<span class="badge bg-primary"><i class="bx bx-handshake me-1"></i>Partenaire</span>',
                            'system' => '<span class="badge bg-secondary"><i class="bx bx-cog me-1"></i>Système</span>',
                            'other' => '<span class="badge bg-dark"><i class="bx bx-file me-1"></i>Autre</span>'
                        ];
                        
                        $category_badge = $category_badges[$value['category']] ?? '<span class="badge bg-secondary">Autre</span>';
                        
                        // Décoder les variables
                        $variables = json_decode($value['variables'], true);
                        $variables_str = !empty($variables) ? implode(', ', $variables) : 'Aucune variable';
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>

                            <td>
                                <code class="text-primary"><?= htmlspecialchars($value['template_key'] ?? '-') ?></code>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark"><?= htmlspecialchars($value['template_name'] ?? '-') ?></strong>
                                    <?php if (!empty($value['description'])): ?>
                                        <small class="text-muted"><?= htmlspecialchars(substr($value['description'], 0, 50)) ?><?= strlen($value['description']) > 50 ? '...' : '' ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <?= $category_badge ?>
                            </td>

                            <td>
                                <span title="Sujet complet: <?= htmlspecialchars($value['subject']) ?>">
                                    <?= htmlspecialchars(substr($value['subject'], 0, 40)) ?><?= strlen($value['subject']) > 40 ? '...' : '' ?>
                                </span>
                                <br>
                                <small class="text-muted" title="Variables disponibles: <?= htmlspecialchars($variables_str) ?>">
                                    <i class="bx bx-code-curly me-1"></i><?= count($variables ?? []) ?> variable(s)
                                </small>
                            </td>

                            <td class="text-center">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#status_<?= $value['id'] ?>" class="text-decoration-none">
                                    <?php if (!empty($value['is_active']) && $value['is_active'] == 1): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactif</span>
                                    <?php endif; ?>
                                </a>
                            </td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('Email_templates/preview/' . $value['id']) ?>" target="_blank">
                                                <i class="bx bx-show text-info me-2"></i>Aperçu
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="showTestModal(<?= $value['id'] ?>, '<?= htmlspecialchars($value['template_name']) ?>')">
                                                <i class="bx bx-send text-success me-2"></i>Tester
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#update_<?= $value['id'] ?>">
                                                <i class="bx bx-edit text-warning me-2"></i>Modifier
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('Email_templates/duplicate/' . $value['id']) ?>" onclick="return confirm('Dupliquer ce template ?')">
                                                <i class="bx bx-copy text-info me-2"></i>Dupliquer
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#delete_<?= $value['id'] ?>">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL UPDATE -->
                        <div class="modal fade" id="update_<?= $value['id'] ?>" data-bs-backdrop="static" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Modifier le Template</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('Email_templates/update') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Clé du template <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="template_key" value="<?= htmlspecialchars($value['template_key'] ?? '') ?>" required pattern="[a-z0-9_]+" title="Lettres minuscules, chiffres et underscore uniquement">
                                                    <small class="text-muted">Identifiant unique (ex: welcome_user, investor_confirmation)</small>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Nom du template <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="template_name" value="<?= htmlspecialchars($value['template_name'] ?? '') ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="category" required>
                                                        <option value="">Sélectionner...</option>
                                                        <option value="user" <?= ($value['category'] ?? '') == 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                                        <option value="investor" <?= ($value['category'] ?? '') == 'investor' ? 'selected' : '' ?>>Investisseur</option>
                                                        <option value="broker" <?= ($value['category'] ?? '') == 'broker' ? 'selected' : '' ?>>Broker</option>
                                                        <option value="partner" <?= ($value['category'] ?? '') == 'partner' ? 'selected' : '' ?>>Partenaire</option>
                                                        <option value="system" <?= ($value['category'] ?? '') == 'system' ? 'selected' : '' ?>>Système</option>
                                                        <option value="other" <?= ($value['category'] ?? '') == 'other' ? 'selected' : '' ?>>Autre</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Variables disponibles</label>
                                                    <input type="text" class="form-control" name="variables" value="<?= !empty($value['variables']) ? implode(', ', json_decode($value['variables'], true)) : '' ?>" placeholder="nom, email, date...">
                                                    <small class="text-muted">Séparées par des virgules (ex: full_name, email, year)</small>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Sujet de l'email <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="subject" value="<?= htmlspecialchars($value['subject'] ?? '') ?>" required>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Corps du message (HTML) <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="body" rows="15" required><?= htmlspecialchars($value['body'] ?? '') ?></textarea>
                                                    <small class="text-muted">Utilisez {variable} pour insérer des variables dynamiques</small>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Description</label>
                                                    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($value['description'] ?? '') ?></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" name="is_active" id="update_is_active_<?= $value['id'] ?>" value="1" <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="update_is_active_<?= $value['id'] ?>">Template actif</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bx bx-save me-2"></i>Enregistrer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DELETE -->
                        <div class="modal fade" id="delete_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Confirmer la suppression</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3">Êtes-vous sûr ?</h5>
                                        <p class="text-muted">Vous êtes sur le point de supprimer le template <strong><?= htmlspecialchars($value['template_name'] ?? '') ?></strong>.</p>
                                        <p class="text-danger small">Cette action est irréversible.</p>
                                    </div>
                                    <form action="<?= base_url('Email_templates/delete') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <div class="modal-footer bg-light justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bx bx-trash me-2"></i>Supprimer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL STATUS -->
                        <div class="modal fade" id="status_<?= $value['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'bg-warning' : 'bg-success' ?> text-white">
                                        <h5 class="modal-title">
                                            <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? '<i class="bx bx-block me-2"></i>Désactiver' : '<i class="bx bx-check-circle me-2"></i>Activer' ?> le template
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <p>Voulez-vous vraiment <strong><?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'désactiver' : 'activer' ?></strong> le template <strong><?= htmlspecialchars($value['template_name']) ?></strong> ?</p>
                                    </div>
                                    <form action="<?= base_url('Email_templates/toggle_status') ?>" method="POST">
                                        <input type="hidden" name="id" value="<?= $value['id'] ?>">
                                        <input type="hidden" name="is_active" value="<?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 1 : 0 ?>">
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'btn-warning' : 'btn-success' ?>">
                                                <?= (!empty($value['is_active']) && $value['is_active'] == 1) ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bx bx-envelope text-muted" style="font-size: 4rem;"></i>
                                <p class="mt-3 text-muted">Aucun template d'email trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

<!-- MODAL CREATE TEMPLATE -->
<div class="modal fade" id="create_template" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Nouveau Template d'Email</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('Email_templates/create') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Clé du template <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="template_key" required pattern="[a-z0-9_]+" title="Lettres minuscules, chiffres et underscore uniquement">
                            <small class="text-muted">Identifiant unique (ex: welcome_user, investor_confirmation)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nom du template <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="template_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" required>
                                <option value="">Sélectionner...</option>
                                <option value="user">Utilisateur</option>
                                <option value="investor">Investisseur</option>
                                <option value="broker">Broker</option>
                                <option value="partner">Partenaire</option>
                                <option value="system">Système</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Variables disponibles</label>
                            <input type="text" class="form-control" name="variables" placeholder="nom, email, date...">
                            <small class="text-muted">Séparées par des virgules (ex: full_name, email, year)</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Sujet de l'email <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Corps du message (HTML) <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="body" rows="15" required></textarea>
                            <small class="text-muted">Utilisez {variable} pour insérer des variables dynamiques</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="is_active" id="create_is_active" value="1" checked>
                                <label class="form-check-label" for="create_is_active">Template actif</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-2"></i>Créer le template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL TEST EMAIL -->
<div class="modal fade" id="testEmailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bx bx-send me-2"></i>Tester l'envoi d'email</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p>Envoyer un email de test pour le template : <strong id="testTemplateName"></strong></p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email de test</label>
                    <input type="email" class="form-control" id="testEmail" placeholder="test@exemple.com">
                </div>
                <div id="testResult" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-info" id="sendTestBtn" onclick="sendTestEmail()">
                    <i class="bx bx-send me-2"></i>Envoyer le test
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation DataTable
    $('#templatesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [6] }
        ]
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

let currentTestTemplateId = null;

function showTestModal(templateId, templateName) {
    currentTestTemplateId = templateId;
    document.getElementById('testTemplateName').textContent = templateName;
    document.getElementById('testEmail').value = '';
    document.getElementById('testResult').style.display = 'none';
    new bootstrap.Modal(document.getElementById('testEmailModal')).show();
}

function sendTestEmail() {
    const email = document.getElementById('testEmail').value;
    const resultDiv = document.getElementById('testResult');
    
    if (!email || !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        resultDiv.className = 'alert alert-danger';
        resultDiv.innerHTML = '<i class="bx bx-error-circle me-2"></i>Veuillez entrer un email valide';
        resultDiv.style.display = 'block';
        return;
    }
    
    document.getElementById('sendTestBtn').disabled = true;
    document.getElementById('sendTestBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi...';
    
    fetch('<?= base_url('Email_templates/test_send') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + currentTestTemplateId + '&test_email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
        resultDiv.className = data.success ? 'alert alert-success' : 'alert alert-danger';
        resultDiv.innerHTML = '<i class="bx ' + (data.success ? 'bx-check-circle' : 'bx-error-circle') + ' me-2"></i>' + data.message;
        resultDiv.style.display = 'block';
    })
    .catch(error => {
        resultDiv.className = 'alert alert-danger';
        resultDiv.innerHTML = '<i class="bx bx-error-circle me-2"></i>Erreur de connexion';
        resultDiv.style.display = 'block';
    })
    .finally(() => {
        document.getElementById('sendTestBtn').disabled = false;
        document.getElementById('sendTestBtn').innerHTML = '<i class="bx bx-send me-2"></i>Envoyer le test';
    });
}
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>