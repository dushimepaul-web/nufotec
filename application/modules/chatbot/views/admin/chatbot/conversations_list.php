<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">

    <h2>Conversations - Transfert humain</h2>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">En attente d'un agent (<?php echo count($pending); ?>)</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>#</th><th>Visiteur</th><th>Activite</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pending as $c): ?>
                            <tr>
                                <td><?php echo $c['id']; ?></td>
                                <td><?php echo html_escape($c['user_name'] ?: 'Anonyme'); ?></td>
                                <td><small><?php echo $c['updated_at']; ?></small></td>
                                <td>
                                    <a class="btn btn-sm btn-success" href="<?php echo site_url('admin/chatbot/conversations/assign/' . $c['id']); ?>">Prendre</a>
                                    <a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('admin/chatbot/conversations/view/' . $c['id']); ?>">Voir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pending)): ?>
                            <tr><td colspan="4" class="text-center text-muted">Aucune conversation en attente.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">En cours avec un agent (<?php echo count($active); ?>)</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>#</th><th>Visiteur</th><th>Activite</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($active as $c): ?>
                            <tr>
                                <td><?php echo $c['id']; ?></td>
                                <td><?php echo html_escape($c['user_name'] ?: 'Anonyme'); ?></td>
                                <td><small><?php echo $c['updated_at']; ?></small></td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="<?php echo site_url('admin/chatbot/conversations/view/' . $c['id']); ?>">Repondre</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($active)): ?>
                            <tr><td colspan="4" class="text-center text-muted">Aucune conversation active.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</main>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>
