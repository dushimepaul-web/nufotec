

<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>
<div class="content-wrapper">
    <style>
    /* Styles pour l'arborescence */
    .facility-tree {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f4f6f9;
        padding: 20px;
        border-radius: 8px;
        max-height: 600px;
        overflow-y: auto;
    }
    
    .tree-node {
        margin: 4px 0;
        border-radius: 6px;
        transition: all 0.2s;
        position: relative;
    }
    
    .tree-node:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    
    .node-content {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        border-left: 4px solid transparent;
        background: white;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        cursor: move;
    }
    
    .node-content.section {
        border-left-color: #ffc107;
        background: #fff9e6;
    }
    
    .node-content.industrial_space {
        border-left-color: #007bff;
    }
    
    .node-content.residential_space {
        border-left-color: #28a745;
    }
    
    .node-content.garage_space {
        border-left-color: #17a2b8;
    }
    
    .node-content.fencing {
        border-left-color: #6c757d;
    }
    
    .node-content.total {
        border-left-color: #dc3545;
        background: #f8d7da;
    }
    
    .node-indent {
        width: 100px;
        color: #6c757d;
        font-size: 12px;
    }
    
    .node-toggle {
        cursor: pointer;
        margin-right: 5px;
        color: #ffc107;
        transition: transform 0.2s;
    }
    
    .node-toggle:hover {
        transform: scale(1.2);
    }
    
    .node-icon {
        width: 35px;
        text-align: center;
        font-size: 1.2rem;
    }
    
    .node-details {
        flex: 1;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 20px;
    }
    
    .node-name {
        min-width: 250px;
        font-weight: 500;
    }
    
    .node-dimensions {
        color: #495057;
        font-size: 0.9rem;
        background: #f8f9fa;
        padding: 3px 10px;
        border-radius: 20px;
    }
    
    .node-actions {
        opacity: 0.3;
        transition: opacity 0.2s;
        white-space: nowrap;
    }
    
    .tree-node:hover .node-actions {
        opacity: 1;
    }
    
    .node-actions .btn {
        padding: 3px 8px;
        margin: 0 2px;
        border-radius: 20px;
    }
    
    .badge-storage {
        background: #17a2b8;
        color: white;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .legend {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 15px;
        background: white;
        border-radius: 8px;
        margin-top: 20px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }
    
    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
    }
    
    .search-box {
        position: relative;
        margin-bottom: 20px;
    }
    
    .search-box input {
        padding-left: 40px;
        border-radius: 30px;
        border: 2px solid #e0e0e0;
        transition: all 0.3s;
    }
    
    .search-box input:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255,193,7,0.25);
    }
    
    .search-box i {
        position: absolute;
        left: 15px;
        top: 12px;
        color: #6c757d;
    }
    
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        transition: transform 0.3s;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .stats-card.industrial {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }
    
    .stats-card.residential {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    }
    
    .stats-card.garage {
        background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%);
    }
    
    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        margin: 10px 0;
    }
    
    .modal-xl {
        max-width: 90%;
    }
    
    .dimension-group {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    
    .dimension-group label {
        font-weight: 600;
        color: #495057;
    }
    
    .drag-handle {
        cursor: move;
        color: #6c757d;
        margin-right: 10px;
    }
    
    .drag-handle:hover {
        color: #ffc107;
    }
    
    .node-highlight {
        animation: highlight 1s ease;
    }
    
    @keyframes highlight {
        0% { background: #fff3cd; }
        100% { background: transparent; }
    }
    
    .quick-actions {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
    }
    
    .quick-actions .btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin: 5px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        transition: all 0.3s;
    }
    
    .quick-actions .btn:hover {
        transform: scale(1.1);
    }
    
    @media (max-width: 768px) {
        .node-details {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
        
        .node-indent {
            width: 40px;
        }
        
        .node-name {
            min-width: auto;
        }
        
        .modal-xl {
            max-width: 95%;
            margin: 10px;
        }
    }
    </style>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-building mr-2"></i>
                        Gestion du Plan d'Aménagement
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Accueil</a></li>
                        <li class="breadcrumb-item active">Facility</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Messages Flash -->
            <div id="flash-messages"></div>

            <!-- Statistiques rapides -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="stats-card industrial">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Industriel</h6>
                                <div class="stats-number"><?= number_format($total_industrial, 0) ?> m²</div>
                                <small>Sections I à XVI</small>
                            </div>
                            <i class="fas fa-industry fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="stats-card residential">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Résidentiel</h6>
                                <div class="stats-number"><?= number_format($total_residential, 0) ?> m²</div>
                                <small>Section XVII</small>
                            </div>
                            <i class="fas fa-home fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="stats-card garage">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Garage</h6>
                                <div class="stats-number"><?= number_format($total_garage, 0) ?> m²</div>
                                <small>Section XVIII</small>
                            </div>
                            <i class="fas fa-tools fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="stats-card" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Total</h6>
                                <div class="stats-number"><?= number_format($total_industrial + $total_residential + $total_garage, 0) ?> m²</div>
                                <small>Toutes zones</small>
                            </div>
                            <i class="fas fa-calculator fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barre d'outils -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="searchInput" 
                               placeholder="Rechercher un espace...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-control" id="typeFilter">
                        <option value="all">Tous les types</option>
                        <?php foreach($node_types as $type): ?>
                            <option value="<?= $type->node_type ?>"><?= $type->node_type ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Arborescence principale -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-sitemap mr-2"></i>
                        Arborescence du Complexe Industriel
                    </h3>
                    <div class="card-tools">
                        <button class="btn btn-primary btn-sm" onclick="openAddModal()">
                            <i class="fas fa-plus"></i> Ajouter
                        </button>
                        <button class="btn btn-info btn-sm" onclick="refreshStats()">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                        <a href="<?= base_url('admin/facility/export_csv') ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Exporter
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="facility-tree" id="facilityTree">
                        <?php foreach($facility_tree as $node): ?>
                            <div class="tree-node level-<?= $node->level_num ?>" 
                                 data-id="<?= $node->id ?>"
                                 data-level="<?= $node->level_num ?>"
                                 data-parent="<?= $node->parent_id ?>"
                                 data-type="<?= $node->node_type ?>">
                                
                                <div class="node-content <?= $node->node_type ?>">
                                    <div class="node-indent">
                                        <?php for($i = 1; $i < $node->level_num; $i++): ?>
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                        <?php endfor; ?>
                                        
                                        <?php if($node->level_num > 1): ?>
                                            <i class="fas fa-chevron-right node-toggle"></i>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="drag-handle">
                                        <i class="fas fa-grip-vertical"></i>
                                    </div>
                                    
                                    <div class="node-icon">
                                        <?php if($node->node_type == 'section'): ?>
                                            <i class="fas fa-folder-open text-warning"></i>
                                        <?php elseif($node->node_type == 'industrial_space'): ?>
                                            <i class="fas fa-industry text-primary"></i>
                                        <?php elseif($node->node_type == 'residential_space'): ?>
                                            <i class="fas fa-home text-success"></i>
                                        <?php elseif($node->node_type == 'garage_space'): ?>
                                            <i class="fas fa-tools text-info"></i>
                                        <?php elseif($node->node_type == 'fencing'): ?>
                                            <i class="fas fa-border-all text-secondary"></i>
                                        <?php elseif($node->node_type == 'total'): ?>
                                            <i class="fas fa-calculator text-danger"></i>
                                        <?php elseif($node->node_type == 'floor'): ?>
                                            <i class="fas fa-layer-group text-purple"></i>
                                        <?php else: ?>
                                            <i class="fas fa-circle text-gray"></i>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="node-details">
                                        <span class="node-name">
                                            <?php if($node->node_code): ?>
                                                <span class="badge badge-secondary mr-1"><?= $node->node_code ?></span>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($node->node_name) ?>
                                            <small class="text-muted ml-2">(<?= $node->node_type ?>)</small>
                                        </span>
                                        
                                        <?php if($node->area_m2 > 0): ?>
                                            <span class="node-dimensions">
                                                <?php if($node->length_m && $node->width_m): ?>
                                                    <?= $node->length_m ?> x <?= $node->width_m ?> m |
                                                <?php endif; ?>
                                                <strong><?= number_format($node->area_m2, 1) ?> m²</strong>
                                                <?php if($node->height_m): ?>
                                                    | H: <?= $node->height_m ?> m
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if($node->storage_type && $node->storage_type != 'standard'): ?>
                                            <span class="badge-storage">
                                                <?= strtoupper($node->storage_type) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="node-actions">
                                        <button class="btn btn-sm btn-warning" onclick="openEditModal(<?= $node->id ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteNode(<?= $node->id ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Légende -->
                    <div class="legend">
                        <span class="legend-item">
                            <span class="legend-color" style="background: #ffc107;"></span>
                            Section
                        </span>
                        <span class="legend-item">
                            <span class="legend-color" style="background: #007bff;"></span>
                            Industriel
                        </span>
                        <span class="legend-item">
                            <span class="legend-color" style="background: #28a745;"></span>
                            Résidentiel
                        </span>
                        <span class="legend-item">
                            <span class="legend-color" style="background: #17a2b8;"></span>
                            Garage
                        </span>
                        <span class="legend-item">
                            <span class="legend-color" style="background: #6c757d;"></span>
                            Clôture
                        </span>
                        <span class="legend-item">
                            <span class="legend-color" style="background: #dc3545;"></span>
                            Total
                        </span>
                        <span class="legend-item">
                            <span class="legend-color" style="background: #6f42c1;"></span>
                            Étage
                        </span>
                        <span class="legend-item">
                            <i class="fas fa-chevron-right text-warning"></i>
                            Cliquer pour dérouler
                        </span>
                        <span class="legend-item">
                            <i class="fas fa-grip-vertical text-secondary"></i>
                            Glisser pour réordonner
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <button class="btn btn-primary" onclick="openAddModal()" title="Ajouter">
            <i class="fas fa-plus"></i>
        </button>
        <button class="btn btn-success" onclick="scrollToTop()" title="Haut de page">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL AJOUT -->
<!-- ============================================ -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Ajouter un nouvel espace
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Parent</label>
                                <select name="parent_id" class="form-control select2">
                                    <option value="">Racine</option>
                                    <?php foreach($parents as $parent): ?>
                                        <option value="<?= $parent->id ?>">
                                            <?= str_repeat('—', $parent->level_num - 1) ?> 
                                            <?= $parent->node_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Code Section</label>
                                <input type="text" name="node_code" class="form-control" 
                                       placeholder="Ex: I, II, III...">
                                <small class="text-muted">Pour les sections principales uniquement</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Nom <span class="text-danger">*</span></label>
                                <input type="text" name="node_name" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Niveau</label>
                                <select name="node_level" class="form-control">
                                    <option value="3">Niveau 3 - Espace</option>
                                    <option value="4">Niveau 4 - Sous-espace</option>
                                </select>
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
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type de stockage</label>
                                <select name="storage_type" class="form-control">
                                    <option value="standard">Standard</option>
                                    <option value="hs">HS - Étagères hautes</option>
                                    <option value="2f">2F - Deux niveaux</option>
                                    <option value="cold_room">Chambre froide</option>
                                </select>
                            </div>
                            
                            <div class="dimension-group">
                                <h6><i class="fas fa-ruler mr-2"></i> Dimensions</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Longueur (m)</label>
                                        <input type="number" name="length_m" class="form-control" 
                                               step="0.01" min="0" id="add_length">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Largeur (m)</label>
                                        <input type="number" name="width_m" class="form-control" 
                                               step="0.01" min="0" id="add_width">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Hauteur (m)</label>
                                        <input type="number" name="height_m" class="form-control" 
                                               step="0.01" min="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Surface (m²)</label>
                                <input type="number" name="area_m2" class="form-control" 
                                       step="0.01" min="0" id="add_area">
                                <small class="text-muted">Laissez vide pour calcul auto</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Ordre d'affichage</label>
                                <input type="number" name="sort_order" class="form-control" value="0" min="0">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL MODIFICATION -->
<!-- ============================================ -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>
                    Modifier l'espace
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editForm">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <!-- Même structure que le modal d'ajout -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Parent</label>
                                <select name="parent_id" id="edit_parent_id" class="form-control select2">
                                    <option value="">Racine</option>
                                    <?php foreach($parents as $parent): ?>
                                        <option value="<?= $parent->id ?>">
                                            <?= str_repeat('—', $parent->level_num - 1) ?> 
                                            <?= $parent->node_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Code Section</label>
                                <input type="text" name="node_code" id="edit_node_code" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>Nom <span class="text-danger">*</span></label>
                                <input type="text" name="node_name" id="edit_node_name" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Type d'espace</label>
                                <select name="node_type" id="edit_node_type" class="form-control">
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
                                <select name="storage_type" id="edit_storage_type" class="form-control">
                                    <option value="standard">Standard</option>
                                    <option value="hs">HS - Étagères hautes</option>
                                    <option value="2f">2F - Deux niveaux</option>
                                    <option value="cold_room">Chambre froide</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="dimension-group">
                                <h6><i class="fas fa-ruler mr-2"></i> Dimensions</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Longueur (m)</label>
                                        <input type="number" name="length_m" id="edit_length_m" 
                                               class="form-control" step="0.01" min="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Largeur (m)</label>
                                        <input type="number" name="width_m" id="edit_width_m" 
                                               class="form-control" step="0.01" min="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Hauteur (m)</label>
                                        <input type="number" name="height_m" id="edit_height_m" 
                                               class="form-control" step="0.01" min="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Surface (m²)</label>
                                <input type="number" name="area_m2" id="edit_area_m2" 
                                       class="form-control" step="0.01" min="0">
                            </div>
                            
                            <div class="form-group">
                                <label>Ordre d'affichage</label>
                                <input type="number" name="sort_order" id="edit_sort_order" 
                                       class="form-control" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" id="edit_notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL DÉTAILS -->
<!-- ============================================ -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Détails de l'espace
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Nom</th>
                        <td id="detail_name"></td>
                    </tr>
                    <tr>
                        <th>Code</th>
                        <td id="detail_code"></td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td id="detail_type"></td>
                    </tr>
                    <tr>
                        <th>Stockage</th>
                        <td id="detail_storage"></td>
                    </tr>
                    <tr>
                        <th>Dimensions</th>
                        <td id="detail_dimensions"></td>
                    </tr>
                    <tr>
                        <th>Surface</th>
                        <td id="detail_area"></td>
                    </tr>
                    <tr>
                        <th>Hauteur</th>
                        <td id="detail_height"></td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td id="detail_description"></td>
                    </tr>
                    <tr>
                        <th>Notes</th>
                        <td id="detail_notes"></td>
                    </tr>
                    <tr>
                        <th>Créé le</th>
                        <td id="detail_created"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL CONFIRMATION SUPPRESSION -->
<!-- ============================================ -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Confirmer la suppression
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cet espace ?</p>
                <p class="text-danger"><strong>Cette action est irréversible !</strong></p>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="delete_id">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL STATISTIQUES -->
<!-- ============================================ -->
<div class="modal fade" id="statsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Statistiques détaillées
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="statsChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm" id="statsTable">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Nombre</th>
                                    <th>Surface</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation
    $('.select2').select2({width: '100%'});
    
    // Toggle des sous-éléments
    $('.node-toggle').click(function() {
        const $this = $(this);
        const $node = $this.closest('.tree-node');
        const level = $node.data('level');
        
        $node.nextUntil('.level-' + (level + 1)).slideToggle();
        $this.toggleClass('fa-chevron-right fa-chevron-down');
    });
    
    // Drag & drop pour réordonner
    $("#facilityTree").sortable({
        handle: '.drag-handle',
        items: '.tree-node',
        placeholder: 'tree-node-placeholder',
        update: function() {
            const items = [];
            $('.tree-node').each(function() {
                items.push($(this).data('id'));
            });
            
            $.ajax({
                url: '<?= base_url('admin/facility/ajax_reorder') ?>',
                method: 'POST',
                data: {items: items},
                success: function(response) {
                    if(response.status === 'success') {
                        showNotification('Ordre mis à jour', 'success');
                    }
                }
            });
        }
    });
    
    // Recherche en temps réel
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 500);
    });
    
    $('#typeFilter').change(performSearch);
    
    // Calcul auto surface dans modal d'ajout
    $('#add_length, #add_width').on('input', function() {
        const length = parseFloat($('#add_length').val()) || 0;
        const width = parseFloat($('#add_width').val()) || 0;
        
        if(length > 0 && width > 0) {
            $('#add_area').val((length * width).toFixed(2));
        }
    });
    
    // Soumission formulaire ajout
    $('#addForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= base_url('admin/facility/ajax_add') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    $('#addModal').modal('hide');
                    showNotification(response.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(response.message, 'error');
                }
            }
        });
    });
    
    // Soumission formulaire modification
    $('#editForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= base_url('admin/facility/ajax_update') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    $('#editModal').modal('hide');
                    showNotification(response.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(response.message, 'error');
                }
            }
        });
    });
});

// ============================================
// FONCTIONS
// ============================================

// Ouvrir modal ajout
function openAddModal() {
    $('#addForm')[0].reset();
    $('#addModal').modal('show');
}

// Ouvrir modal modification
function openEditModal(id) {
    $.ajax({
        url: '<?= base_url('admin/facility/ajax_get/') ?>' + id,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.status === 'success') {
                const data = response.data;
                
                $('#edit_id').val(data.id);
                $('#edit_parent_id').val(data.parent_id);
                $('#edit_node_code').val(data.node_code);
                $('#edit_node_name').val(data.node_name);
                $('#edit_node_type').val(data.node_type);
                $('#edit_storage_type').val(data.storage_type || 'standard');
                $('#edit_length_m').val(data.length_m);
                $('#edit_width_m').val(data.width_m);
                $('#edit_height_m').val(data.height_m);
                $('#edit_area_m2').val(data.area_m2);
                $('#edit_sort_order').val(data.sort_order);
                $('#edit_description').val(data.description);
                $('#edit_notes').val(data.notes);
                
                $('#editModal').modal('show');
            }
        }
    });
}

// Supprimer un noeud
function deleteNode(id) {
    $('#delete_id').val(id);
    $('#deleteModal').modal('show');
}

function confirmDelete() {
    const id = $('#delete_id').val();
    
    $.ajax({
        url: '<?= base_url('admin/facility/ajax_delete/') ?>' + id,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#deleteModal').modal('hide');
            
            if(response.status === 'success') {
                showNotification(response.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(response.message, 'error');
            }
        }
    });
}

// Recherche
function performSearch() {
    const keyword = $('#searchInput').val();
    const type = $('#typeFilter').val();
    
    $.ajax({
        url: '<?= base_url('admin/facility/ajax_search') ?>',
        method: 'POST',
        data: {keyword: keyword, type: type},
        dataType: 'json',
        success: function(response) {
            if(response.status === 'success') {
                updateTreeWithResults(response.data);
            }
        }
    });
}

// Mettre à jour l'arbre avec les résultats
function updateTreeWithResults(results) {
    // Cacher tous les noeuds
    $('.tree-node').hide();
    
    // Afficher les résultats et leurs parents
    results.forEach(function(item) {
        let node = $(`.tree-node[data-id="${item.id}"]`);
        node.show();
        
        // Afficher tous les parents
        let parentId = item.parent_id;
        while(parentId) {
            let parent = $(`.tree-node[data-id="${parentId}"]`);
            parent.show();
            parentId = parent.data('parent');
        }
    });
}

// Rafraîchir les statistiques
function refreshStats() {
    $.ajax({
        url: '<?= base_url('admin/facility/ajax_stats') ?>',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.status === 'success') {
                updateStatsDisplay(response.data);
            }
        }
    });
}

// Afficher les statistiques
function showStats() {
    refreshStats();
    $('#statsModal').modal('show');
}

// Mettre à jour l'affichage des stats
function updateStatsDisplay(stats) {
    // Mettre à jour les cartes
    $('.stats-card.industrial .stats-number').text(stats.total_industrial.toLocaleString() + ' m²');
    $('.stats-card.residential .stats-number').text(stats.total_residential.toLocaleString() + ' m²');
    $('.stats-card.garage .stats-number').text(stats.total_garage.toLocaleString() + ' m²');
    
    // Mettre à jour le tableau
    let html = '';
    stats.by_type.forEach(function(item) {
        html += `<tr>
            <td>${item.node_type}</td>
            <td>${item.count}</td>
            <td>${parseFloat(item.total_area).toLocaleString()} m²</td>
        </tr>`;
    });
    $('#statsTable tbody').html(html);
    
    // Créer le graphique
    createStatsChart(stats);
}

// Créer le graphique
function createStatsChart(stats) {
    const ctx = document.getElementById('statsChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: stats.by_type.map(item => item.node_type),
            datasets: [{
                data: stats.by_type.map(item => item.total_area),
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#17a2b8', '#6c757d', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Notification
function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const html = `<div class="alert ${alertClass} alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        ${message}
    </div>`;
    
    $('#flash-messages').html(html);
    
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 3000);
}

// Scroll to top
function scrollToTop() {
    $('html, body').animate({scrollTop: 0}, 500);
}

// Exporter
function exportData() {
    window.location.href = '<?= base_url('admin/facility/export_csv') ?>';
}
</script>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>