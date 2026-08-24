<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">

    <h2><?php echo $article ? 'Modifier' : 'Ajouter'; ?> un article - Base de connaissances</h2>

    <?php echo form_open(current_url()); ?>

        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Titre</label>
                    <input type="text" name="title" class="form-control" required
                           value="<?php echo $article ? html_escape($article['title']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Categorie</label>
                    <input type="text" name="category" class="form-control"
                           value="<?php echo $article ? html_escape($article['category']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Contenu</label>
                    <textarea name="content" class="form-control" rows="8" required><?php
                        echo $article ? html_escape($article['content']) : '';
                    ?></textarea>
                    <small class="text-muted">Ce texte sera utilise par l'IA comme source de verite (RAG). Soyez clair et precis.</small>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="active" value="1" class="form-check-input"
                           id="activeCheck" <?php echo (!$article || $article['active']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="activeCheck">Actif</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="<?php echo site_url('admin/chatbot/kb'); ?>" class="btn btn-link">Annuler</a>
            </div>
        </div>

    <?php echo form_close(); ?>

</div>
</div>
</main>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>
