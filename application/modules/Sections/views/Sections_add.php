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
                        <li class="breadcrumb-item active" aria-current="page">Ajouter une section</li>
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
                <h5 class="mb-0 text-success"><i class="bx bx-plus me-2"></i>Nouvelle Section</h5>
            </div>
            <div class="card-body">
                <form id="sectionForm" action="<?= base_url('Sections/Create') ?>" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Page associée <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_page" required>
                                <option value="">Sélectionner une page...</option>
                                <?php foreach ($pages as $page): ?>
                                    <option value="<?= $page['id_page'] ?>"><?= htmlspecialchars($page['titre_page']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Type de section <span class="text-danger">*</span></label>
                            <select class="form-select" name="type_section" required>
                                <option value="">Sélectionner un type...</option>
                                <?php 
                                // Types de sections EXACTEMENT comme dans l'ENUM de votre table
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
                                    <option value="<?= $value ?>" <?= $value == 'texte' ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Ordre <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="ordre" value="0" required min="0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Titre de la section</label>
                            <input type="text" class="form-control" name="titre_section" placeholder="Titre principal">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Sous-titre</label>
                            <input type="text" class="form-control" name="sous_titre" placeholder="Sous-titre">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Contenu texte</label>
                            <textarea class="form-control" id="contenu_texte" name="contenu_texte" rows="15" placeholder="Contenu de la section..."></textarea>
                        </div>

                        <!-- ZONE D'UPLOAD SIMPLE -->
                        <div class="col-12 mt-3 p-3 border rounded bg-light">
                            <label class="form-label fw-bold text-primary">
                                <i class="bx bx-image-add"></i> Insérer une image
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
                                L'image sera insérée à l'endroit où se trouve votre curseur dans l'éditeur.
                            </div>
                            <div id="upload_message" class="mt-2"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Image de section (fichier)</label>
                            <input type="file" class="form-control" name="image_file" accept="image/*">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ou URL de l'image</label>
                            <input type="text" class="form-control" name="image_url" placeholder="https://exemple.com/image.jpg">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Position image</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" class="form-check-input" name="image_droite" id="create_image_droite" value="1">
                                <label class="form-check-label" for="create_image_droite">Image à droite (sinon gauche)</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Texte du bouton (CTA)</label>
                            <input type="text" class="form-control" name="bouton_texte" placeholder="En savoir plus">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lien du bouton (CTA)</label>
                            <input type="text" class="form-control" name="bouton_lien" placeholder="/page-url">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Classe CSS personnalisée</label>
                            <input type="text" class="form-control" name="custom_class" placeholder="my-custom-class">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Options JSON</label>
                            <textarea class="form-control" name="options_json" rows="2" placeholder='{"key": "value"}'></textarea>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="<?= base_url('Sections') ?>" class="btn btn-secondary me-2">Annuler</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bx bx-save me-2"></i>Créer la section
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

<!-- TinyMCE Self-Hosted -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>

<script>
tinymce.init({
    selector: '#contenu_texte',
    height: 500,
    language: 'fr_FR',
    
    // TOUS LES PLUGINS NÉCESSAIRES
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    
    // TOOLBAR COMPLÈTE
    toolbar: [
        'undo redo | formatselect | bold italic underline strikethrough | forecolor backcolor',
        'alignleft aligncenter alignright alignjustify',
        'bullist numlist outdent indent',
        'table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
        'link image media | code fullscreen help'
    ].join(' | '),
    
    // MENU COMPLET
    menubar: 'file edit view insert format tools table help',
    
    // CONFIGURATION DES TABLEAUX
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
    
    // CONFIGURATION UPLOAD IMAGES
    images_upload_url: '<?= base_url("Sections/uploadImage") ?>',
    automatic_uploads: true,
    file_picker_types: 'image',
    
    // GESTIONNAIRE D'UPLOAD
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
    
    // STYLE PAR DÉFAUT
    content_style: `
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 14px; line-height: 1.6; }
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        table td, table th { border: 1px solid #dee2e6; padding: 8px; }
        table th { background-color: #f8f9fa; font-weight: 600; }
        .table-bordered { border: 1px solid #dee2e6; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.05); }
        img { max-width: 100%; height: auto; }
    `,
    
    // OPTIONS ADDITIONNELLES
    image_advtab: true,
    image_caption: true,
    image_title: true,
    
    // BRANDE
    branding: false,
    
    // DÉBOGAGE
    setup: function(editor) {
        editor.on('init', function() {
            console.log('✅ TinyMCE chargé avec succès !');
        });
    }
});

// UPLOAD MANUEL
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
                            
                            // Insérer l'image dans TinyMCE
                            tinymce.activeEditor.insertContent(
                                '<img src="' + imageUrl + '" class="img-fluid" style="max-width:100%; margin:10px 0;">'
                            );
                            
                            messageDiv.innerHTML = '<div class="alert alert-success">✅ Image insérée avec succès !</div>';
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
    
    // Gestionnaire de soumission de formulaire
    var form = document.getElementById('sectionForm');
    if (form) {
        form.addEventListener('submit', function() {
            // TinyMCE met automatiquement à jour le textarea
            return true;
        });
    }
});
</script>

<!-- Style -->
<style>
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
    .btn-success {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        border: none;
        color: #1e7e34;
        font-weight: 600;
    }
    .btn-success:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(132, 250, 176, 0.4);
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
    .bg-light {
        background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
    }
    .form-control:focus {
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
</style>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>