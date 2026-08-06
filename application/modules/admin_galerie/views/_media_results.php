<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('format_duration')) {
    function format_duration($seconds) {
        if (empty($seconds) || $seconds <= 0) return '0:00';
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
    }
}
if (!function_exists('format_bytes')) {
    function format_bytes($bytes, $decimals = 2) {
        if (empty($bytes) || $bytes === 0) return '0 B';
        $k = 1024; $sizes = ['B','KB','MB','GB','TB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), $decimals) . ' ' . $sizes[$i];
    }
}
if (!function_exists('get_type_badge')) {
    function get_type_badge($type) {
        $map = [
            'audio'    => ['label' => 'Audio', 'class' => 'success', 'icon' => 'bx bx-music'],
            'video'    => ['label' => 'Vidéo', 'class' => 'danger', 'icon' => 'bx bx-video'],
            'image'    => ['label' => 'Image', 'class' => 'primary', 'icon' => 'bx bx-image'],
            'document' => ['label' => 'Document', 'class' => 'warning', 'icon' => 'bx bx-file'],
            'link'     => ['label' => 'Lien', 'class' => 'info', 'icon' => 'bx bx-link'],
        ];
        return $map[$type] ?? ['label' => 'Média', 'class' => 'secondary', 'icon' => 'bx bx-file'];
    }
}
?>

<!-- Media Grid -->
<div class="row g-3">
    <?php if (empty($medias)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bx bx-folder-open fs-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">Aucun média trouvé</h5>
                    <p class="text-muted"><?= !empty($search_file) ? 'Aucun résultat pour « ' . e($search_file) . ' ».' : 'Cliquez sur "Ajouter un média" pour commencer.' ?></p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($medias as $media): ?>
            <?php $badge = get_type_badge($media['type']); ?>
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card media-card border-0 shadow-sm h-100 position-relative">
                    <span class="type-badge badge bg-<?= $badge['class'] ?>"><i class="<?= $badge['icon'] ?> me-1"></i><?= $badge['label'] ?></span>
                    <div class="card-img-top d-flex align-items-center justify-content-center" style="height:160px;">
                        <?php if (!empty($media['miniature'])): ?>
                            <?php
                            $miniature = $media['miniature'] ?? '';
                            $thumb_src = (strpos($miniature, 'http') === 0) ? $miniature : base_url($miniature);
                            $type = $media['type'] ?? '';
                            $placeholder = in_array($type, ['audio','video','image','document','link'])
                                ? base_url('assets/images/' . $type . '-placeholder.jpg')
                                : base_url('assets/images/default-thumbnail.jpg');
                            ?>
                            <img src="<?= e($thumb_src) ?>" alt="<?= e($media['titre']) ?>" onerror="this.src='<?= $placeholder ?>'" style="width:100%;height:100%;object-fit:cover;">
                        <?php elseif ($media['type'] === 'link' && !empty($media['lien'])): ?>
                            <i class="bx bx-link fs-1 text-muted"></i>
                        <?php else: ?>
                            <i class="<?= $badge['icon'] ?> fs-1 text-muted"></i>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title text-truncate mb-1"><?= e($media['titre'] ?? 'Sans titre') ?></h6>
                        <small class="text-muted d-block text-truncate"><?= e($media['credits'] ?? '') ?></small>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <?php if (in_array($media['type'], ['audio', 'video']) && !empty($media['duree'])): ?>
                                <small class="text-muted"><i class="bx bx-time"></i> <?= format_duration($media['duree']) ?></small>
                            <?php endif; ?>
                            <?php if (!empty($media['taille'])): ?>
                                <small class="text-muted"><i class="bx bx-data"></i> <?= format_bytes($media['taille']) ?></small>
                            <?php endif; ?>
                            <span class="badge bg-<?= $media['est_actif'] ? 'success' : 'secondary' ?> ms-auto" style="font-size:8px;width:8px;height:8px;border-radius:50%;padding:0;"></span>
                        </div>
                        <div class="d-flex gap-1 mt-2">
                            <button class="btn btn-sm btn-outline-primary edit-media" data-id="<?= (int)$media['id_media'] ?>" data-type="<?= e($media['type']) ?>" data-bs-toggle="modal" data-bs-target="#editModal"><i class="bx bx-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger delete-media" data-id="<?= (int)$media['id_media'] ?>" data-bs-toggle="modal" data-bs-target="#deleteModal"><i class="bx bx-trash"></i></button>
                            <button class="btn btn-sm btn-outline-info view-media" data-id="<?= (int)$media['id_media'] ?>" data-type="<?= e($media['type']) ?>" data-bs-toggle="modal" data-bs-target="#viewModal"><i class="bx bx-<?= in_array($media['type'], ['audio','video']) ? 'play-circle' : ($media['type'] === 'image' ? 'image' : ($media['type'] === 'document' ? 'file' : 'link')) ?>"></i></button>
                            <div class="form-check form-switch ms-auto">
                                <input class="form-check-input toggle-field" type="checkbox" data-id="<?= (int)$media['id_media'] ?>" data-field="is_for_website" <?= $media['is_for_website'] ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (isset($pagination) && $pagination['pages'] > 1): ?>
<div class="d-flex justify-content-between align-items-center mt-4">
    <small class="text-muted"><?= $pagination['total'] ?> média(s) — Page <?= $pagination['page'] ?> / <?= $pagination['pages'] ?></small>
    <?php $search_qs = !empty($search_file) ? '&search_file=' . urlencode($search_file) : ''; ?>
    <nav>
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= base_url('admin/media/index/' . ($current_type ?? '') . '?page=' . ($pagination['page'] - 1) . $search_qs) ?>">Précédent</a>
            </li>
            <?php for ($p = max(1, $pagination['page'] - 2); $p <= min($pagination['pages'], $pagination['page'] + 2); $p++): ?>
            <li class="page-item <?= $p === $pagination['page'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= base_url('admin/media/index/' . ($current_type ?? '') . '?page=' . $p . $search_qs) ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $pagination['page'] >= $pagination['pages'] ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= base_url('admin/media/index/' . ($current_type ?? '') . '?page=' . ($pagination['page'] + 1) . $search_qs) ?>">Suivant</a>
            </li>
        </ul>
    </nav>
</div>
<?php endif; ?>
