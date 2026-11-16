<?php include VIEWPATH.'includes/frontend/Header.php'; ?>

<style>
    .pv-breadcrumb { background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 18px 0; margin-bottom: 2rem; }
    .pv-breadcrumb .breadcrumb { margin: 0; }
    .pv-breadcrumb .breadcrumb-item a { color: #0d6efd; text-decoration: none; }
    .pv-page-title { font-size: 2.2rem; font-weight: 700; color: #212529; margin-bottom: 0.25rem; }
    .pv-page-lead { color: #6c757d; font-size: 1.1rem; }
    .pv-section { padding: 3.5rem 0; }
    .pv-section-header { margin-bottom: 2rem; text-align: center; }
    .pv-tag { display: inline-block; text-transform: uppercase; letter-spacing: 2px; font-size: .8rem; color: #0d6efd; font-weight: 600; margin-bottom: .5rem; }
    .pv-title { font-size: 1.9rem; font-weight: 700; color: #212529; }
    .pv-content { color: #495057; line-height: 1.8; }
    .pv-hero { background-color: #0b1c3d; color: #fff; padding: 5rem 0; text-align: center; position: relative; }
    .pv-hero .pv-hero-title { font-size: 2.6rem; font-weight: 700; }
    .pv-hero .pv-hero-subtitle { color: rgba(255,255,255,.85); font-size: 1.15rem; max-width: 720px; margin: .75rem auto 0; }
    .pv-card { background: #fff; border: 1px solid #e9ecef; border-radius: .75rem; padding: 1.5rem; height: 100%; box-shadow: 0 .25rem .75rem rgba(0,0,0,.03); }
    .pv-card .bi { color: #0d6efd; font-size: 1.8rem; margin-bottom: .75rem; display: inline-block; }
    .pv-card-title { font-size: 1.15rem; font-weight: 600; }
    .pv-fact { display: flex; gap: 1rem; background: #fff; border: 1px solid #e9ecef; border-radius: .75rem; padding: 1.25rem; height: 100%; }
    .pv-fact-icon { color: #0d6efd; font-size: 1.4rem; }
    .pv-fact-label { font-weight: 600; color: #212529; }
    .pv-fact-value { color: #6c757d; }
    .pv-badge { background: #f1f3f5; border: 1px solid #dee2e6; color: #212529; padding: .5rem .9rem; border-radius: 999px; white-space: normal; }
    .pv-timeline-item { position: relative; padding: 0 0 2rem 2.2rem; border-left: 3px solid #0d6efd; margin-left: .75rem; }
    .pv-timeline-item::before { content: attr(data-marker); position: absolute; left: -1.15rem; top: 0; width: 2.1rem; height: 2.1rem; border-radius: 50%; background: #0d6efd; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; }
    .pv-timeline-title { font-size: 1.15rem; font-weight: 700; }
    .pv-timeline-year { font-size: .8rem; font-weight: 700; color: #0d6efd; }
    .tinymce-content img { max-width: 100%; height: auto; }
</style>

<?php
// Fonction pour nettoyer les chemins d'images
function pv_fix_image_path($image_path) {
    if (empty($image_path)) return null;
    if (filter_var($image_path, FILTER_VALIDATE_URL)) {
        return $image_path;
    }
    $image_path = preg_replace('/\.\.\//', '', $image_path);
    $image_path = ltrim($image_path, '/');
    return base_url($image_path);
}

// Fonction pour corriger les URLs d'images dans le contenu HTML
function pv_fix_content_images($content) {
    if (empty($content)) return '';
    $base_url = rtrim(base_url(), '/');
    $content = preg_replace('/src=["\'](?:\.\.\/)+(attachments\/[^"\']+)["\']/i', 'src="' . $base_url . '/$1"', $content);
    $content = preg_replace('/href=["\'](?:\.\.\/)+(attachments\/[^"\']+)["\']/i', 'href="' . $base_url . '/$1"', $content);
    return $content;
}
?>

<div class="pv-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <?php if (!empty($breadcrumb)): foreach ($breadcrumb as $crumb): ?>
                    <?php if ($crumb !== end($breadcrumb)): ?>
                        <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['titre']) ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['titre']) ?></li>
                    <?php endif; ?>
                <?php endforeach; else: ?>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($page['titre_page'] ?? '') ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<section class="pv-section pt-0">
    <div class="container">
        <h1 class="pv-page-title"><?= htmlspecialchars($page['titre_page'] ?? '') ?></h1>
        <?php if (!empty($page['meta_description'])): ?>
            <p class="pv-page-lead"><?= htmlspecialchars($page['meta_description']) ?></p>
        <?php endif; ?>
    </div>
</section>

<?php

?>

<?php if (!empty($sections)): foreach ($sections as $section):
    if (!empty($section['est_active'] == 0)) continue;
    $type = $section['type_section'] ?? 'texte';
    $options = !empty($section['options_json']) ? json_decode($section['options_json'], true) : [];
    if (!is_array($options)) $options = [];
    $raw_content = $section['contenu_texte'] ?? '';
    $content_with_fixed_images = pv_fix_content_images($raw_content);

    switch ($type):
        case 'hero': ?>
            <section class="pv-hero <?= $section['custom_class'] ?? '' ?>"
                <?php if (!empty($section['image_url'])): ?> style="background-image:url('<?= pv_fix_image_path($section['image_url']) ?>'); background-size:cover; background-position:center;"<?php endif; ?>>
                <div class="container">
                    <h1 class="pv-hero-title"><?= htmlspecialchars($section['sous_titre'] ?: $section['titre_section']) ?></h1>
                    <?php if (!empty($raw_content)): ?>
                        <p class="pv-hero-subtitle"><?= strip_tags($raw_content) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($section['bouton_texte'])): ?>
                        <a href="<?= htmlspecialchars($section['bouton_lien'] ?? '#') ?>" class="btn btn-light btn-lg mt-4">
                            <?= htmlspecialchars($section['bouton_texte']) ?> <i class="bi bi-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </section>
        <?php break;

        case 'texte':
        case 'html': ?>
            <section class="pv-section <?= $section['custom_class'] ?? '' ?>">
                <div class="container">
                    <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                        <div class="pv-section-header">
                            <?php if (!empty($section['titre_section'])): ?><span class="pv-tag"><?= htmlspecialchars($section['titre_section']) ?></span><?php endif; ?>
                            <?php if (!empty($section['sous_titre'])): ?><h2 class="pv-title"><?= htmlspecialchars($section['sous_titre']) ?></h2><?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($section['image_url'])): ?>
                        <div class="text-center mb-4">
                            <img src="<?= pv_fix_image_path($section['image_url']) ?>" alt="<?= htmlspecialchars($section['titre_section']) ?>" class="img-fluid rounded-3">
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($content_with_fixed_images)): ?>
                        <div class="pv-content tinymce-content"><?= html_entity_decode($content_with_fixed_images, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                        <div class="text-center mt-4">
                            <a href="<?= htmlspecialchars($section['bouton_lien']) ?>" class="btn btn-primary btn-lg rounded-pill">
                                <?= htmlspecialchars($section['bouton_texte']) ?> <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php break;

        case 'image_texte':
            $image_left = empty($section['image_droite']) || $section['image_droite'] == 0;
            $has_image = !empty($section['image_url']); ?>
            <section class="pv-section <?= $section['custom_class'] ?? '' ?>">
                <div class="container">
                    <div class="row align-items-center g-5">
                        <?php if ($has_image && $image_left): ?>
                            <div class="col-lg-6"><img src="<?= pv_fix_image_path($section['image_url']) ?>" alt="<?= htmlspecialchars($section['titre_section']) ?>" class="img-fluid rounded-4 shadow"></div>
                            <div class="col-lg-6">
                        <?php elseif ($has_image): ?>
                            <div class="col-lg-6 order-lg-2"><img src="<?= pv_fix_image_path($section['image_url']) ?>" alt="<?= htmlspecialchars($section['titre_section']) ?>" class="img-fluid rounded-4 shadow"></div>
                            <div class="col-lg-6 order-lg-1">
                        <?php else: ?>
                            <div class="col-12 text-center">
                        <?php endif; ?>
                                <?php if (!empty($section['titre_section'])): ?><span class="pv-tag"><?= htmlspecialchars($section['titre_section']) ?></span><?php endif; ?>
                                <?php if (!empty($section['sous_titre'])): ?><h2 class="pv-title"><?= htmlspecialchars($section['sous_titre']) ?></h2><?php endif; ?>
                                <?php if (!empty($content_with_fixed_images)): ?><div class="pv-content tinymce-content mt-3"><?= html_entity_decode($content_with_fixed_images, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                <?php if (!empty($section['bouton_texte']) && !empty($section['bouton_lien'])): ?>
                                    <a href="<?= htmlspecialchars($section['bouton_lien']) ?>" class="btn btn-primary mt-3"><?= htmlspecialchars($section['bouton_texte']) ?></a>
                                <?php endif; ?>
                            </div>
                    </div>
                </div>
            </section>
        <?php break;

        case 'grille':
        case 'grille_card':
        case 'liste_card':
            $items = [];
            $decoded_content = html_entity_decode($raw_content, ENT_QUOTES, 'UTF-8');
            if (strpos($decoded_content, '<li>') !== false) {
                preg_match_all('/<li>(.*?)<\/li>/', $decoded_content, $matches);
                $items = $matches[1] ?? [];
            } else {
                foreach (explode("\n", strip_tags($decoded_content)) as $line) {
                    $line = trim($line);
                    if (!empty($line)) $items[] = $line;
                }
            }
            $cols = $options['columns'] ?? 3;
            $col_class = $cols == 2 ? 'col-md-6' : ($cols == 4 ? 'col-md-6 col-lg-3' : 'col-md-6 col-lg-4'); ?>
            <section class="pv-section <?= $section['custom_class'] ?? '' ?>">
                <div class="container">
                    <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                        <div class="pv-section-header">
                            <?php if (!empty($section['titre_section'])): ?><span class="pv-tag"><?= htmlspecialchars($section['titre_section']) ?></span><?php endif; ?>
                            <?php if (!empty($section['sous_titre'])): ?><h2 class="pv-title"><?= htmlspecialchars($section['sous_titre']) ?></h2><?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="row g-4">
                        <?php foreach ($items as $item):
                            if (empty(trim($item))) continue;
                            $item = html_entity_decode($item, ENT_QUOTES, 'UTF-8');
                            $clean = trim(strip_tags($item));
                            $label = ''; $value = $clean;
                            if (strpos($clean, ':') !== false) {
                                $parts = explode(':', $clean, 2);
                                $label = trim($parts[0]);
                                $value = trim($parts[1]);
                            }
                        ?>
                        <div class="<?= $col_class ?>">
                            <div class="pv-card">
                                <i class="bi bi-<?= $options['icon'] ?? 'check-circle' ?>"></i>
                                <div class="pv-card-title"><?= htmlspecialchars($label ?: $value) ?></div>
                                <?php if (!empty($label) && !empty($value)): ?>
                                    <div class="pv-text text-muted"><?= htmlspecialchars($value) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php break;

        case 'liste':
        case 'liste_inline':
            $list_items = [];
            foreach (explode("\n", strip_tags(html_entity_decode($raw_content, ENT_QUOTES, 'UTF-8'))) as $line) {
                $line = trim($line);
                if (!empty($line)) $list_items[] = $line;
            } ?>
            <section class="pv-section <?= $section['custom_class'] ?? '' ?>">
                <div class="container">
                    <?php if (!empty($section['titre_section']) || !empty($section['sous_titre'])): ?>
                        <div class="pv-section-header">
                            <?php if (!empty($section['titre_section'])): ?><span class="pv-tag"><?= htmlspecialchars($section['titre_section']) ?></span><?php endif; ?>
                            <?php if (!empty($section['sous_titre'])): ?><h2 class="pv-title"><?= htmlspecialchars($section['sous_titre']) ?></h2><?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($list_items as $item): ?>
                            <span class="pv-badge"><?= htmlspecialchars($item) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php break;

        case 'tableau': ?>
            <section class="pv-section <?= $section['custom_class'] ?? '' ?>">
                <div class="container">
                    <?php if (!empty($section['sous_titre'])): ?><h2 class="pv-title mb-4"><?= htmlspecialchars($section['sous_titre']) ?></h2><?php endif; ?>
                    <div class="table-responsive tinymce-content"><?= html_entity_decode($content_with_fixed_images, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </section>
        <?php break;

        case 'timeline':
            $timeline_items = [];
            $decoded_content = html_entity_decode($raw_content, ENT_QUOTES, 'UTF-8');
            $categories = preg_split('/\n\s*\n/', $decoded_content);
            foreach ($categories as $cat_index => $category) {
                if (empty(trim($category))) continue;
                $cat_title = '';
                $cat_items = [];
                foreach (explode("\n", trim($category)) as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    if (preg_match('/^\d+[\)\.]\s*(.+)$/', $line, $matches)) {
                        $cat_title = $matches[1];
                    } else {
                        $cat_items[] = $line;
                    }
                }
                if (!empty($cat_title)) {
                    $timeline_items[] = ['title' => $cat_title, 'items' => $cat_items, 'marker' => $cat_index + 1];
                }
            } ?>
            <section class="pv-section <?= $section['custom_class'] ?? '' ?>">
                <div class="container">
                    <?php if (!empty($section['sous_titre'])): ?><h2 class="pv-title mb-5"><?= htmlspecialchars($section['sous_titre']) ?></h2><?php endif; ?>
                    <div class="row justify-content-center">
                        <div class="col-lg-9">
                            <?php foreach ($timeline_items as $item): ?>
                                <div class="pv-timeline-item" data-marker="<?= $item['marker'] ?>">
                                    <h3 class="pv-timeline-title"><?= htmlspecialchars($item['title']) ?></h3>
                                    <?php foreach ($item['items'] as $sub_item):
                                        $sub_item = html_entity_decode($sub_item, ENT_QUOTES, 'UTF-8');
                                        $year = '';
                                        $description = $sub_item;
                                        if (preg_match('/^(\d{4}(?:–\d{4})?)[:\s]\s*(.+)/', strip_tags($sub_item), $matches)) {
                                            $year = $matches[1];
                                            $description = $matches[2];
                                        }
                                    ?>
                                        <?php if (!empty($year)): ?><span class="pv-timeline-year"><?= htmlspecialchars($year) ?></span><?php endif; ?>
                                        <div class="pv-content tinymce-content mb-2"><?= pv_fix_content_images($description) ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php break;

        default: ?>
            <section class="pv-section <?= $section['custom_class'] ?? '' ?>">
                <div class="container">
                    <?php if (!empty($section['titre_section'])): ?><h2 class="pv-title mb-3"><?= htmlspecialchars($section['titre_section']) ?></h2><?php endif; ?>
                    <?php if (!empty($raw_content)): ?><div class="pv-content tinymce-content"><?= html_entity_decode($content_with_fixed_images, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>
            </section>
    <?php endswitch;
endforeach; endif; ?>

<?php include VIEWPATH.'includes/frontend/Footer.php'; ?>