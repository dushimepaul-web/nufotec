<!-- admin/facility/add.php -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Ajouter un Espace</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/facility') ?>">Facility</a></li>
                        <li class="breadcrumb-item active">Ajouter</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Nouvel espace</h3>
                        </div>
                        <form action="<?= base_url('admin/facility/store') ?>" method="POST">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Parent <span class="text-danger">*</span></label>
                                            <select name="parent_id" class="form-control" required>
                                                <option value="">Sélectionner un parent</option>
                                                <?php foreach($parents as $parent): ?>
                                                    <option value="<?= $parent->id ?>">
                                                        <?= str_repeat('—', $parent->level_num - 1) ?> 
                                                        <?= $parent->node_name ?> (<?= $parent->node_type ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Niveau <span class="text-danger">*</span></label>
                                            <select name="node_level" class="form-control" required>
                                                <option value="3">Niveau 3 - Espace</option>
                                                <option value="4">Niveau 4 - Sous-espace</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Code Section</label>
                                            <input type="text" name="node_code" class="form-control" 
                                                   placeholder="Ex: I, II, III...">
                                            <small class="text-muted">Uniquement pour les sections principales</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Nom <span class="text-danger">*</span></label>
                                            <input type="text" name="node_name" class="form-control" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Type d'espace</label>
                                            <select name="node_type" class="form-control">
                                                <option value="industrial_space">Industriel</option>
                                                <option value="residential_space">Résidentiel</option>
                                                <option value="garage_space">Garage</option>
                                                <option value="fencing">Clôture</option>
                                                <option value="total">Total</option>
                                                <option value="floor">Étage</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Type de stockage</label>
                                            <select name="storage_type" class="form-control">
                                                <option value="standard">Standard</option>
                                                <option value="hs">HS - Étagères hautes</option>
                                                <option value="2f">2F - Deux niveaux</option>
                                                <option value="cold_room">Chambre froide</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Longueur (m)</label>
                                            <input type="number" name="length_m" class="form-control" 
                                                   step="0.01" min="0">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Largeur (m)</label>
                                            <input type="number" name="width_m" class="form-control" 
                                                   step="0.01" min="0">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Surface (m²)</label>
                                            <input type="number" name="area_m2" class="form-control" 
                                                   step="0.01" min="0" id="area">
                                            <small class="text-muted">Laissez vide pour calcul automatique</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Hauteur (m)</label>
                                            <input type="number" name="height_m" class="form-control" 
                                                   step="0.01" min="0">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Ordre d'affichage</label>
                                            <input type="number" name="sort_order" class="form-control" 
                                                   value="0" min="0">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Description</label>
                                            <textarea name="description" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <textarea name="notes" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                                <a href="<?= base_url('admin/facility') ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // Calcul automatique de la surface
    $('input[name="length_m"], input[name="width_m"]').on('input', function() {
        var length = parseFloat($('input[name="length_m"]').val()) || 0;
        var width = parseFloat($('input[name="width_m"]').val()) || 0;
        
        if(length > 0 && width > 0) {
            var area = length * width;
            $('#area').val(area.toFixed(2));
        }
    });
});
</script>