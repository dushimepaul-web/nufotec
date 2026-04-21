<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<div class="page-wrapper">
    <div class="page-content">

        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">CMS</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('Dashboard') ?>"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('Pages') ?>">Pages</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('Sections') ?>">Sections</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Modifier la section</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="<?= base_url('Sections') ?>" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Retour
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-warning"><i class="bx bx-edit me-2"></i>Modifier la section #<?= $detail['id_section'] ?></h5>
            </div>
            <div class="card-body">
                <form id="sectionForm" action="<?= base_url('Sections/Update') ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_section" value="<?= $detail['id_section'] ?>">
                    
                    <!-- Onglets de langue -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#french" type="button" role="tab">
                                <i class="bx bx-flag"></i> 🇫🇷 Français
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#english" type="button" role="tab">
                                <i class="bx bx-flag"></i> 🇬🇧 English
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#swahili" type="button" role="tab">
                                <i class="bx bx-flag"></i> 🇹🇿 Kiswahili
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- ==================== FRANÇAIS ==================== -->
                        <div class="tab-pane fade show active" id="french" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Titre (FR) <span class="text-muted small">(optionnel)</span></label>
                                    <input type="text" class="form-control" name="titre_section_fr" value="<?= htmlspecialchars($detail['titre_section_fr'] ?? '') ?>" placeholder="Titre principal en français">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Sous-titre (FR) <span class="text-muted small">(optionnel)</span></label>
                                    <input type="text" class="form-control" name="sous_titre_fr" value="<?= htmlspecialchars($detail['sous_titre_fr'] ?? '') ?>" placeholder="Sous-titre en français">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Contenu texte (FR)</label>
                                    <textarea class="form-control tinymce-editor" id="contenu_texte_fr" name="contenu_texte_fr" rows="15"><?= htmlspecialchars($detail['contenu_texte_fr'] ?? '') ?></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Texte du bouton (FR) <span class="text-muted small">(optionnel)</span></label>
                                    <input type="text" class="form-control" name="bouton_texte_fr" value="<?= htmlspecialchars($detail['bouton_texte_fr'] ?? '') ?>" placeholder="En savoir plus">
                                </div>
                            </div>
                        </div>

                        <!-- ==================== ENGLISH ==================== -->
                        <div class="tab-pane fade" id="english" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Title (EN) <span class="text-muted small">(optional)</span></label>
                                    <input type="text" class="form-control" name="titre_section_en" value="<?= htmlspecialchars($detail['titre_section_en'] ?? '') ?>" placeholder="Main title in English">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Subtitle (EN) <span class="text-muted small">(optional)</span></label>
                                    <input type="text" class="form-control" name="sous_titre_en" value="<?= htmlspecialchars($detail['sous_titre_en'] ?? '') ?>" placeholder="Subtitle in English">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Text content (EN)</label>
                                    <textarea class="form-control tinymce-editor" id="contenu_texte_en" name="contenu_texte_en" rows="15"><?= htmlspecialchars($detail['contenu_texte_en'] ?? '') ?></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Button text (EN) <span class="text-muted small">(optional)</span></label>
                                    <input type="text" class="form-control" name="bouton_texte_en" value="<?= htmlspecialchars($detail['bouton_texte_en'] ?? '') ?>" placeholder="Learn more">
                                </div>
                            </div>
                        </div>

                        <!-- ==================== KISWAHILI ==================== -->
                        <div class="tab-pane fade" id="swahili" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Kichwa (SW) <span class="text-muted small">(siyo lazima)</span></label>
                                    <input type="text" class="form-control" name="titre_section_sw" value="<?= htmlspecialchars($detail['titre_section_sw'] ?? '') ?>" placeholder="Kichwa kikuu kwa Kiswahili">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Kichwa kidogo (SW) <span class="text-muted small">(siyo lazima)</span></label>
                                    <input type="text" class="form-control" name="sous_titre_sw" value="<?= htmlspecialchars($detail['sous_titre_sw'] ?? '') ?>" placeholder="Kichwa kidogo kwa Kiswahili">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Maudhui (SW)</label>
                                    <textarea class="form-control tinymce-editor" id="contenu_texte_sw" name="contenu_texte_sw" rows="15"><?= htmlspecialchars($detail['contenu_texte_sw'] ?? '') ?></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Maandishi ya kitufe (SW) <span class="text-muted small">(siyo lazima)</span></label>
                                    <input type="text" class="form-control" name="bouton_texte_sw" value="<?= htmlspecialchars($detail['bouton_texte_sw'] ?? '') ?>" placeholder="Jifunze zaidi">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paramètres communs (non traduits) -->
                    <div class="row g-3 mt-4 pt-3 border-top">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="bx bx-cog"></i> Paramètres généraux</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Page associée <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_page" required>
                                <?php foreach ($pages as $page): ?>
                                    <option value="<?= $page['id_page'] ?>" <?= ($detail['id_page'] ?? '') == $page['id_page'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($page['titre_page']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Type de section <span class="text-danger">*</span></label>
                            <select class="form-select" name="type_section" required>
                                <?php 
                                $types_sections = [
                                    'hero' => 'Hero (Bannière principale)',
                                    'texte' => 'Texte',
                                    'image_texte' => 'Image + Texte',
                                    'html' => 'html',
                                    'grille' => 'Grille',
                                    'grille_card' => 'Grille de cartes',
                                    'grille_inline' => 'Grille en ligne',
                                    'liste_card' => 'Liste de cartes',
                                    'liste' => 'Liste',
                                    'liste_inline' => 'Liste en ligne',
                                    'liste_item' => 'Item de liste',
                                    'tableau' => 'Tableau',
                                    'timeline' => 'Chronologie (Timeline)'
                                ];
                                
                                foreach ($types_sections as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= ($detail['type_section'] ?? '') == $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Ordre <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="ordre" value="<?= $detail['ordre'] ?? 0 ?>" required min="0">
                        </div>

                        <!-- ZONE D'UPLOAD SIMPLE -->
                        <div class="col-12 mt-3 p-3 border rounded bg-light">
                            <label class="form-label fw-bold text-primary">
                                <i class="bx bx-image-add"></i> Insérer une image dans l'éditeur
                            </label>
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="file" class="form-control" id="image_upload" accept="image/*">
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary w-100" type="button" id="upload_btn">
                                        <i class="bx bx-upload"></i> Uploader et insérer
                                    </button>
                                </div>
                            </div>
                            <div class="form-text text-muted mt-2">
                                L'image sera insérée dans l'onglet actif de l'éditeur.
                            </div>
                            <div id="upload_message" class="mt-2"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Image de section (fichier)</label>
                            <input type="file" class="form-control" name="image_file" accept="image/*">
                            <?php if (!empty($detail['image_url'])): ?>
                                <div class="mt-2">
                                    <small class="text-muted d-block">Image actuelle:</small>
                                    <img src="<?= base_url($detail['image_url']) ?>" style="max-height: 50px;" class="border rounded">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ou URL de l'image</label>
                            <input type="text" class="form-control" name="image_url" value="<?= htmlspecialchars($detail['image_url'] ?? '') ?>" placeholder="https://exemple.com/image.jpg">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Position image</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" class="form-check-input" name="image_droite" id="image_droite" value="1" <?= (!empty($detail['image_droite']) && $detail['image_droite'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="image_droite">Image à droite (sinon gauche)</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lien du bouton (CTA)</label>
                            <input type="text" class="form-control" name="bouton_lien" value="<?= htmlspecialchars($detail['bouton_lien'] ?? '') ?>" placeholder="/page-url">
                            <small class="text-muted">Le lien est commun à toutes les langues</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Classe CSS personnalisée</label>
                            <input type="text" class="form-control" name="custom_class" value="<?= htmlspecialchars($detail['custom_class'] ?? '') ?>" placeholder="my-custom-class">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Options JSON</label>
                            <textarea class="form-control" name="options_json" rows="2" placeholder='{"key": "value"}'><?= htmlspecialchars($detail['options_json'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="<?= base_url('Sections') ?>" class="btn btn-secondary me-2">Annuler</a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bx bx-save me-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>


<!-- TinyMCE Self-Hosted -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>

<script>
// Initialiser TinyMCE pour chaque éditeur multilingue
function initTinyMCE(selector) {
    tinymce.init({
        selector: selector,
        height: 400,
        language: 'fr_FR',
        
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        
        toolbar: [
            'undo redo | formatselect | bold italic underline strikethrough | forecolor backcolor',
            'alignleft aligncenter alignright alignjustify',
            'bullist numlist outdent indent',
            'table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
            'link image media | code fullscreen help'
        ].join(' | '),
        
        menubar: 'file edit view insert format tools table help',
        
        table_appearance_options: true,
        table_grid: true,
        table_cell_advtab: true,
        table_row_advtab: true,
        table_advtab: true,
        table_sizing_mode: 'relative',
        table_default_attributes: {
            class: 'table table-bordered'
        },
        table_default_styles: {
            width: '100%',
            borderCollapse: 'collapse'
        },
        
        images_upload_url: '<?= base_url("Sections/uploadImage") ?>',
        automatic_uploads: true,
        file_picker_types: 'image',
        
        images_upload_handler: function(blobInfo, progress) {
            return new Promise(function(success, failure) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?= base_url("Sections/uploadImage") ?>');
                
                xhr.onload = function() {
                    if (xhr.status !== 200) {
                        failure('Erreur HTTP: ' + xhr.status);
                        return;
                    }
                    
                    try {
                        var json = JSON.parse(xhr.responseText);
                    } catch (e) {
                        failure('Réponse invalide: ' + xhr.responseText);
                        return;
                    }
                    
                    if (!json || typeof json.uploaded !== 'number' || json.uploaded !== 1) {
                        failure(json.error ? json.error.message : 'Erreur inconnue');
                        return;
                    }
                    
                    success(json.url);
                };
                
                xhr.onerror = function() {
                    failure('Erreur réseau');
                };
                
                var formData = new FormData();
                formData.append('upload', blobInfo.blob(), blobInfo.filename());
                xhr.send(formData);
            });
        },
        
        content_style: `
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 14px; line-height: 1.6; }
            table { border-collapse: collapse; width: 100%; margin: 15px 0; }
            table td, table th { border: 1px solid #dee2e6; padding: 8px; }
            table th { background-color: #f8f9fa; font-weight: 600; }
            .table-bordered { border: 1px solid #dee2e6; }
            .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.05); }
            img { max-width: 100%; height: auto; }
        `,
        
        image_advtab: true,
        image_caption: true,
        image_title: true,
        
        branding: false,
        
        setup: function(editor) {
            editor.on('init', function() {
                console.log('✅ TinyMCE chargé pour: ' + selector);
            });
        }
    });
}

// Initialiser TinyMCE pour les 3 éditeurs
document.addEventListener('DOMContentLoaded', function() {
    initTinyMCE('#contenu_texte_fr');
    initTinyMCE('#contenu_texte_en');
    initTinyMCE('#contenu_texte_sw');
});

// Upload manuel (insère dans l'onglet actif)
document.addEventListener('DOMContentLoaded', function() {
    var uploadBtn = document.getElementById('upload_btn');
    if (uploadBtn) {
        uploadBtn.addEventListener('click', function() {
            var fileInput = document.getElementById('image_upload');
            var messageDiv = document.getElementById('upload_message');
            
            if (!fileInput.files.length) {
                messageDiv.innerHTML = '<div class="alert alert-warning">⚠️ Sélectionnez une image</div>';
                return;
            }
            
            // Trouver l'onglet actif
            var activeTab = document.querySelector('.tab-pane.active');
            var activeEditorId = null;
            
            if (activeTab && activeTab.id) {
                if (activeTab.id === 'french') activeEditorId = 'contenu_texte_fr';
                else if (activeTab.id === 'english') activeEditorId = 'contenu_texte_en';
                else if (activeTab.id === 'swahili') activeEditorId = 'contenu_texte_sw';
            }
            
            if (!activeEditorId) {
                messageDiv.innerHTML = '<div class="alert alert-warning">⚠️ Veuillez sélectionner un onglet d\'édition</div>';
                return;
            }
            
            var formData = new FormData();
            formData.append('upload', fileInput.files[0]);
            
            messageDiv.innerHTML = '<div class="alert alert-info">⏳ Upload en cours...</div>';
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= base_url("Sections/uploadImage") ?>', true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.uploaded == 1) {
                            var imageUrl = response.url;
                            
                            // Insérer l'image dans l'éditeur actif
                            if (tinymce.get(activeEditorId)) {
                                tinymce.get(activeEditorId).insertContent(
                                    '<img src="' + imageUrl + '" class="img-fluid" style="max-width:100%; margin:10px 0;" alt="Image">'
                                );
                            } else {
                                // Fallback pour textarea normal
                                var textarea = document.getElementById(activeEditorId);
                                if (textarea) {
                                    textarea.value += '<img src="' + imageUrl + '" class="img-fluid">';
                                }
                            }
                            
                            messageDiv.innerHTML = '<div class="alert alert-success">✅ Image insérée avec succès dans l\'onglet ' + activeTab.id + ' !</div>';
                            fileInput.value = '';
                            
                            setTimeout(function() {
                                messageDiv.innerHTML = '';
                            }, 3000);
                        } else {
                            messageDiv.innerHTML = '<div class="alert alert-danger">❌ ' + (response.error ? response.error.message : 'Erreur') + '</div>';
                        }
                    } catch (e) {
                        messageDiv.innerHTML = '<div class="alert alert-danger">❌ Erreur de réponse serveur</div>';
                    }
                } else {
                    messageDiv.innerHTML = '<div class="alert alert-danger">❌ Erreur ' + xhr.status + '</div>';
                }
            };
            
            xhr.onerror = function() {
                messageDiv.innerHTML = '<div class="alert alert-danger">❌ Erreur réseau</div>';
            };
            
            xhr.send(formData);
        });
    }
});
</script>

<!-- Style -->
<style>
    .nav-tabs .nav-link {
        color: #495057;
        font-weight: 500;
        padding: 10px 20px;
        border: none;
        border-bottom: 2px solid transparent;
    }
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        color: #667eea;
    }
    .nav-tabs .nav-link.active {
        color: #667eea;
        border-bottom: 2px solid #667eea;
        background: transparent;
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 8px 20px;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    .btn-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        color: white;
    }
    .btn-warning:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
    }
    .btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        border: none;
        color: white;
    }
    .alert {
        padding: 12px 20px;
        border-radius: 5px;
        margin-top: 10px;
        font-weight: 500;
    }
    .alert-success {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        color: #1e7e34;
        border: none;
    }
    .alert-info {
        background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
        color: #0c5460;
        border: none;
    }
    .alert-warning {
        background: linear-gradient(135deg, #ffe6b0 0%, #ffd5a4 100%);
        color: #856404;
        border: none;
    }
    .alert-danger {
        background: linear-gradient(135deg, #ffb8b8 0%, #ff9a9e 100%);
        color: #721c24;
        border: none;
    }
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .card {
        border-radius: 15px;
        overflow: hidden;
    }
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    .border-top {
        border-top: 1px solid #dee2e6 !important;
    }
</style>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>