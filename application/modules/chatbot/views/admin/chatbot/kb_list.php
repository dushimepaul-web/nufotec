<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">

    <h2>Base de connaissances du chatbot</h2>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <a href="<?php echo site_url('admin/chatbot/kb/create'); ?>" class="btn btn-primary">+ Ajouter un article</a>
        <a href="<?php echo site_url('admin/chatbot/kb/regenerate_all_embeddings'); ?>" class="btn btn-secondary"
           onclick="return confirm('Regenerer tous les embeddings ? Cela appelle l\'API IA pour chaque article.');">
            Regenerer tous les embeddings
        </a>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Categorie</th>
                        <th>Actif</th>
                        <th>Indexe</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($articles as $a): ?>
                    <tr>
                        <td><?php echo $a['id']; ?></td>
                        <td><?php echo html_escape($a['title']); ?></td>
                        <td><?php echo html_escape($a['category']); ?></td>
                        <td><?php echo $a['active'] ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-secondary">Non</span>'; ?></td>
                        <td><?php echo !empty($a['embedding']) ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-warning">Non</span>'; ?></td>
                        <td>
                            <a href="<?php echo site_url('admin/chatbot/kb/edit/' . $a['id']); ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                            <a href="<?php echo site_url('admin/chatbot/kb/delete/' . $a['id']); ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Supprimer cet article ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($articles)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Aucun article pour le moment.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
</main>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>
