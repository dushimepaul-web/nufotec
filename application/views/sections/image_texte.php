<section class="section-image-texte py-5 <?= $section['custom_class'] ?>">
    <div class="container">
        <div class="row align-items-center g-5">
            <?php 
            $image_col = $section['image_droite'] ? 'order-lg-2' : '';
            $text_col = $section['image_droite'] ? '' : 'order-lg-2';
            ?>
            
            <div class="col-lg-6 <?= $image_col ?>">
                <?php if ($section['image_url']): ?>
                    <img src="<?= base_url($section['image_url']) ?>" 
                         alt="<?= htmlspecialchars($section['titre_section'] ?: '') ?>"
                         class="img-fluid rounded-4 shadow-lg">
                <?php endif; ?>
            </div>
            
            <div class="col-lg-6 <?= $text_col ?>">
                <?php if ($section['titre_section']): ?>
                    <h2 class="fw-bold mb-3"><?= htmlspecialchars($section['titre_section']) ?></h2>
                <?php endif; ?>
                
                <?php if ($section['sous_titre']): ?>
                    <p class="lead text-muted mb-4"><?= htmlspecialchars($section['sous_titre']) ?></p>
                <?php endif; ?>
                
                <?php if ($section['contenu_texte']): ?>
                    <div class="content-text mb-4">
                        <?= $section['contenu_texte'] ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($section['bouton_texte'] && $section['bouton_lien']): ?>
                    <a href="<?= base_url($section['bouton_lien']) ?>" class="btn btn-outline-primary">
                        <?= htmlspecialchars($section['bouton_texte']) ?>
                        <i class="bx bx-right-arrow-alt ms-2"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>