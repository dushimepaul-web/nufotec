<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>
<?php include VIEWPATH.'includes/backend/Topheader.php'; ?>

<?php
$investor = $detail ?? [];
$pays_nom = '-';
if (!empty($investor['id_pays'])) {
    if (!empty($pays) && isset($pays['pays'])) {
        $pays_nom = $pays['pays'];
    } elseif (!empty($pays) && is_array($pays)) {
        foreach ($pays as $p) {
            if (isset($p['id']) && $p['id'] == $investor['id_pays']) { $pays_nom = $p['pays'] ?? $p['name'] ?? '-'; break; }
        }
    }
}
$interets = [
    'interest_equity' => 'Capitaux propres',
    'interest_debt' => 'Dette',
    'interest_blended_finance' => 'Financement mixte',
    'interest_grant' => 'Subvention',
    'interest_strategic_partnership' => 'Partenariat stratégique',
    'interest_technical_collaboration' => 'Collaboration technique',
    'interest_offtake_distribution' => 'Achat / Distribution',
];
$focus = [
    'focus_research_lab' => 'Laboratoire de recherche',
    'focus_gmp_facility' => 'Installation GMP',
    'focus_medicinal_plant' => 'Plante médicinale',
    'focus_commercialization' => 'Commercialisation',
    'focus_full_platform' => 'Plateforme complète',
];
?>

<div class="page-wrapper">
<div class="page-content">

    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Admin</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('Investors') ?>">Investisseurs</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Détail</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a class="btn btn-outline-secondary" href="<?= base_url('Investors') ?>">
                <i class="bx bx-arrow-back"></i> Retour
            </a>
        </div>
    </div>

    <hr/>

    <?php if (empty($investor)): ?>
        <div class="alert alert-warning" role="alert">
            <strong>Introuvable!</strong> Cet investisseur n'existe pas ou a été supprimé.
        </div>
    <?php else: ?>
    <div class="card">
        <div class="card-header py-3">
            <h5 class="mb-0 text-primary"><i class="bx bx-user me-2"></i>Détail de l'Investisseur</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4"><strong>Nom complet:</strong></div>
                <div class="col-md-8"><?= $investor['full_name'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Organisation:</strong></div>
                <div class="col-md-8"><?= $investor['organization'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Poste:</strong></div>
                <div class="col-md-8"><?= $investor['position_title'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Pays:</strong></div>
                <div class="col-md-8"><?= $pays_nom ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Email:</strong></div>
                <div class="col-md-8"><?= $investor['email'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Téléphone:</strong></div>
                <div class="col-md-8"><?= $investor['phone'] ?? '-' ?></div>
            </div>

            <hr>
            <h6 class="text-primary mb-3"><i class="bx bx-line-chart me-1"></i>Types d'intérêt</h6>
            <div class="row mb-3">
                <div class="col-md-12">
                    <?php $i_found = false; foreach ($interets as $cle => $label): ?>
                        <?php if (!empty($investor[$cle])): $i_found = true; ?>
                            <span class="badge bg-success me-1 mb-1"><?= $label ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!$i_found && empty($investor['interest_other'])): ?>
                        <span class="text-muted">Aucun intérêt sélectionné</span>
                    <?php endif; ?>
                    <?php if (!empty($investor['interest_other'])): ?>
                        <span class="badge bg-secondary me-1 mb-1">Autre: <?= $investor['interest_other'] ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4"><strong>Fourchette d'engagement:</strong></div>
                <div class="col-md-8"><?= $investor['commitment_range'] ?? '-' ?></div>
            </div>

            <hr>
            <h6 class="text-primary mb-3"><i class="bx bx-focus me-1"></i>Domaines d'intérêt</h6>
            <div class="row mb-3">
                <div class="col-md-12">
                    <?php $f_found = false; foreach ($focus as $cle => $label): ?>
                        <?php if (!empty($investor[$cle])): $f_found = true; ?>
                            <span class="badge bg-info text-dark me-1 mb-1"><?= $label ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!$f_found): ?>
                        <span class="text-muted">Aucun domaine sélectionné</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4"><strong>Calendrier:</strong></div>
                <div class="col-md-8"><?= $investor['timeline'] ?? '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Message stratégique:</strong></div>
                <div class="col-md-8"><?= nl2br($investor['strategic_message'] ?? '-') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Contact accepté:</strong></div>
                <div class="col-md-8">
                    <span class="badge bg-<?= !empty($investor['agree_contact']) ? 'success' : 'secondary' ?>"><?= !empty($investor['agree_contact']) ? 'Oui' : 'Non' ?></span>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Confirmation non contraignante:</strong></div>
                <div class="col-md-8">
                    <span class="badge bg-<?= !empty($investor['non_binding_confirmation']) ? 'success' : 'secondary' ?>"><?= !empty($investor['non_binding_confirmation']) ? 'Oui' : 'Non' ?></span>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Inscrit le:</strong></div>
                <div class="col-md-8"><?= !empty($investor['created_at']) ? date('d/m/Y H:i:s', strtotime($investor['created_at'])) : '-' ?></div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="<?= base_url('Investors') ?>" class="btn btn-secondary">Fermer</a>
        </div>
    </div>
    <?php endif; ?>

</div>
</div>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>