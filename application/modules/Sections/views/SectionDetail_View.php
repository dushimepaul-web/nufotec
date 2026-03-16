<div class="section-preview border rounded p-4">
    <h5 class="mb-3 text-primary">Aperçu de la section</h5>
    
    <?php if (!empty($titre)): ?>
        <h2 class="mb-3"><?= htmlspecialchars($titre) ?></h2>
    <?php endif; ?>
    
    <?php if (!empty($sous_titre)): ?>
        <h4 class="text-muted mb-4"><?= htmlspecialchars($sous_titre) ?></h4>
    <?php endif; ?>
    
    <div class="row align-items-center">
        <?php if (!empty($image) && !$image_droite): ?>
            <div class="col-md-6 mb-3">
                <img src="<?= base_url($image) ?>" class="img-fluid rounded" style="max-height: 300px; object-fit: cover;">
            </div>
            <div class="col-md-6">
                <div class="content-preview"><?= $contenu ?></div>
            </div>
        <?php elseif (!empty($image) && $image_droite): ?>
            <div class="col-md-6">
                <div class="content-preview"><?= $contenu ?></div>
            </div>
            <div class="col-md-6 mb-3">
                <img src="<?= base_url($image) ?>" class="img-fluid rounded" style="max-height: 300px; object-fit: cover;">
            </div>
        <?php else: ?>
            <div class="col-12">
                <div class="content-preview"><?= $contenu ?></div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($bouton_texte)): ?>
        <div class="mt-4">
            <a href="<?= htmlspecialchars($bouton_lien ?? '#') ?>" class="btn btn-primary">
                <?= htmlspecialchars($bouton_texte) ?>
            </a>
        </div>
    <?php endif; ?>
    
    <small class="text-muted d-block mt-3">
        <i class="bx bx-info-circle"></i> Ceci est un aperçu. Le rendu final peut varier selon le thème.
    </small>
</div>

<style>
.content-preview {
    max-height: 300px;
    overflow-y: auto;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}
</style>