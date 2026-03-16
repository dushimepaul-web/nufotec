<?php if (isset($categorie_info) && $categorie_info && isset($workflow) && !empty($workflow)): ?>
<div class="container">
    <div class="workflow-section">
        <div class="workflow-header">
            <div class="workflow-title-wrapper">
                <span class="workflow-badge">PROCESSUS</span>
                <h3 class="workflow-title">Notre processus pour <?php echo $categorie_info->nom_categorie; ?></h3>
            </div>
            <p class="workflow-subtitle">Découvrez les étapes de notre engagement qualité</p>
        </div>
        <div class="workflow-timeline">
            <?php foreach ($workflow as $index => $etape): ?>
            <div class="workflow-step">
                <div class="step-number <?php echo $index === 0 ? 'active' : ($index === count($workflow)-1 ? 'last' : ''); ?>">
                    <span><?php echo $etape->etape_ordre; ?></span>
                </div>
                <div class="step-content">
                    <h4 class="step-title">
                        <?php echo $etape->nom_etape; ?>
                        <?php if ($etape->notification_email == 1): ?>
                        <span class="notification-badge" title="Notification par email">
                            <i class="bi bi-envelope-fill"></i>
                        </span>
                        <?php endif; ?>
                    </h4>
                    <?php if (!empty($etape->description_etape)): ?>
                    <p class="step-description"><?php echo $etape->description_etape; ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>