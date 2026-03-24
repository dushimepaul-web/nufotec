<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Contenu</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active">Hero Slides</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
                    <i class="bx bx-plus me-1"></i>Nouveau Slide
                </button>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $this->session->flashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $this->session->flashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Liste des slides -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bx bx-images me-2"></i>Slides de la page d'accueil</h5>
            </div>
            <div class="card-body">
                <?php if (empty($slides)): ?>
                    <div class="text-center py-5">
                        <i class="bx bx-image text-muted" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3">Aucun slide créé</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tableSlides">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">Ordre</th>
                                    <th width="150">Image</th>
                                    <th>Contenu</th>
                                    <th>Boutons</th>
                                    <th width="80">Statut</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="sortableSlides">
                                <?php foreach ($slides as $slide): ?>
                                <tr data-id="<?= $slide['id'] ?>">
                                    <td>
                                        <span class="badge bg-secondary handle" style="cursor: move;">
                                            <i class="bx bx-move"></i> <?= $slide['slide_order'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <img src="<?= base_url('attachments/heros/' . $slide['background_image']) ?>" 
                                             class="rounded" style="width: 120px; height: 70px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <small class="text-primary text-uppercase"><?= htmlspecialchars($slide['subtitle']) ?></small>
                                        <h6 class="mb-1"><?= htmlspecialchars($slide['title']) ?></h6>
                                        <p class="text-muted small mb-0 text-truncate" style="max-width: 300px;">
                                            <?= htmlspecialchars($slide['description']) ?>
                                        </p>
                                    </td>
                                    <td>
                                        <?php if ($slide['button_primary_text']): ?>
                                            <span class="badge bg-primary me-1">
                                                <i class="bx <?= $slide['button_primary_icon'] ?> me-1"></i>
                                                <?= $slide['button_primary_text'] ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($slide['button_secondary_text']): ?>
                                            <span class="badge bg-secondary">
                                                <i class="bx <?= $slide['button_secondary_icon'] ?> me-1"></i>
                                                <?= $slide['button_secondary_text'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input toggle-active" type="checkbox" 
                                                   data-id="<?= $slide['id'] ?>"
                                                   <?= $slide['is_active'] ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                data-bs-target="#modalEdit<?= $slide['id'] ?>">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $slide['id'] ?>)">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal Edit -->
                                <div class="modal fade" id="modalEdit<?= $slide['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form action="<?= base_url('Slides/Update/' . $slide['id']) ?>" method="POST" enctype="multipart/form-data">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">Modifier le slide</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <div class="mb-3">
                                                                <label class="form-label">Sous-titre</label>
                                                                <input type="text" class="form-control" name="subtitle" value="<?= htmlspecialchars($slide['subtitle']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Titre principal</label>
                                                                <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($slide['title']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Description</label>
                                                                <textarea class="form-control" name="description" rows="3" required><?= htmlspecialchars($slide['description']) ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label class="form-label">Image actuelle</label>
                                                                <img src="<?= base_url('attachments/heros/' . $slide['background_image']) ?>" class="img-fluid rounded mb-2">
                                                                <input type="file" class="form-control" name="background_image" accept="image/*">
                                                                <small class="text-muted">Laisser vide pour garder l'actuelle</small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Ordre</label>
                                                                <input type="number" class="form-control" name="slide_order" value="<?= $slide['slide_order'] ?>" min="1">
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= $slide['is_active'] ? 'checked' : '' ?>>
                                                                <label class="form-check-label">Actif</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <hr>
                                                    <h6>Bouton principal</h6>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <input type="text" class="form-control" name="button_primary_text" value="<?= $slide['button_primary_text'] ?>" placeholder="Texte">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <select class="form-select" name="button_primary_icon">
                                                                <option value="">Pas d'icône</option>
                                                                <option value="bx-cart" <?= $slide['button_primary_icon'] == 'bx-cart' ? 'selected' : '' ?>>🛒 Panier</option>
                                                                <option value="bx-right-arrow-alt" <?= $slide['button_primary_icon'] == 'bx-right-arrow-alt' ? 'selected' : '' ?>>→ Flèche</option>
                                                                <option value="bx-play" <?= $slide['button_primary_icon'] == 'bx-play' ? 'selected' : '' ?>>▶ Lecture</option>
                                                                <option value="bx-info-circle" <?= $slide['button_primary_icon'] == 'bx-info-circle' ? 'selected' : '' ?>>ℹ Info</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="text" class="form-control" name="button_primary_link" value="<?= $slide['button_primary_link'] ?>" placeholder="Lien (ex: Produits)">
                                                        </div>
                                                    </div>

                                                    <hr>
                                                    <h6>Bouton secondaire</h6>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <input type="text" class="form-control" name="button_secondary_text" value="<?= $slide['button_secondary_text'] ?>" placeholder="Texte">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <select class="form-select" name="button_secondary_icon">
                                                                <option value="">Pas d'icône</option>
                                                                <option value="bx-info-circle" <?= $slide['button_secondary_icon'] == 'bx-info-circle' ? 'selected' : '' ?>>ℹ Info</option>
                                                                <option value="bx-phone" <?= $slide['button_secondary_icon'] == 'bx-phone' ? 'selected' : '' ?>>📞 Téléphone</option>
                                                                <option value="bx-envelope" <?= $slide['button_secondary_icon'] == 'bx-envelope' ? 'selected' : '' ?>>✉ Email</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="text" class="form-control" name="button_secondary_link" value="<?= $slide['button_secondary_link'] ?>" placeholder="Lien">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted"><i class="bx bx-info-circle"></i> Glissez-déposez les lignes pour changer l'ordre</small>
                <?php endif; ?>
            </div>
        </div>

    </div>


<!-- Modal Create -->
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('Slides/Create') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Nouveau slide</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Sous-titre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="subtitle" placeholder="Ex: NOUVEAUTÉ 2024" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Titre principal <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" placeholder="Ex: Découvrez notre collection" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="description" rows="3" placeholder="Description du slide..." required></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Image de fond <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="background_image" accept="image/*" required>
                                <small class="text-muted">Format: JPG, PNG, WEBP. Max 2MB</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ordre d'affichage</label>
                                <input type="number" class="form-control" name="slide_order" value="1" min="1">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                <label class="form-check-label">Actif immédiatement</label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6>Bouton principal</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="button_primary_text" placeholder="Ex: Découvrir">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="button_primary_icon">
                                <option value="">Pas d'icône</option>
                                <option value="bx-cart">🛒 Panier</option>
                                <option value="bx-right-arrow-alt">→ Flèche</option>
                                <option value="bx-play">▶ Lecture</option>
                                <option value="bx-info-circle">ℹ Info</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="button_primary_link" placeholder="Ex: Produits/Categorie">
                        </div>
                    </div>

                    <hr>
                    <h6>Bouton secondaire</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="button_secondary_text" placeholder="Ex: En savoir plus">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="button_secondary_icon">
                                <option value="">Pas d'icône</option>
                                <option value="bx-info-circle">ℹ Info</option>
                                <option value="bx-phone">📞 Téléphone</option>
                                <option value="bx-envelope">✉ Email</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="button_secondary_link" placeholder="Ex: Contact">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Créer le slide</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="modalDelete" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="bx bx-error-circle text-danger" style="font-size: 3rem;"></i>
                <p class="mt-3">Supprimer ce slide ?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <a href="#" id="btnConfirmDelete" class="btn btn-danger">Supprimer</a>
            </div>
        </div>
    </div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
// Tri par drag & drop
const tbody = document.getElementById('sortableSlides');
new Sortable(tbody, {
    handle: '.handle',
    animation: 150,
    onEnd: function() {
        const rows = tbody.querySelectorAll('tr');
        const order = {};
        rows.forEach((row, index) => {
            order[row.dataset.id] = index + 1;
        });

        // Envoyer nouvel ordre au serveur
        $.post('<?= base_url("Slides/Reorder") ?>', { order: order }, function(response) {
            if (response.success) {
                location.reload();
            }
        }, 'json');
    }
});

// Activer/Désactiver
$('.toggle-active').change(function() {
    const id = $(this).data('id');
    $.post('<?= base_url("Slides/ToggleActive/") ?>' + id, function(response) {
        if (!response.success) {
            alert('Erreur');
        }
    }, 'json');
});

// Confirmation suppression
function confirmDelete(id) {
    $('#btnConfirmDelete').attr('href', '<?= base_url("Slides/Delete/") ?>' + id);
    $('#modalDelete').modal('show');
}
</script>