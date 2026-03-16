<!-- application/views/search/index.php -->
<div class="container mt-4">
    <h1>Résultats de recherche pour "<?= htmlspecialchars($this->input->get('q')) ?>"</h1>

    <?php if (empty($results)): ?>
        <p>Aucun résultat trouvé.</p>
    <?php else: ?>
        <?php foreach ($results as $type => $items): ?>
            <?php if (!empty($items)): ?>
                <h2><?= ucfirst($type) ?></h2>
                <div class="list-group mb-4">
                    <?php foreach ($items as $item): ?>
                        <a href="<?= base_url($item['type'] . '/detail/' . $item['slug']) ?>" class="list-group-item list-group-item-action">
                            <h5 class="mb-1"><?= htmlspecialchars($item['titre']) ?></h5>
                            <p class="mb-1"><?= htmlspecialchars($item['extrait'] ?? '') ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>