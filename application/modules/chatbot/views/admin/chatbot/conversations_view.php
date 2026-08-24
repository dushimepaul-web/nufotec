<?php include VIEWPATH.'includes/backend/Header.php'; ?>
<?php include VIEWPATH.'includes/backend/Sidebar.php'; ?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            Conversation #<?php echo $conversation['id']; ?>
            <small class="text-muted">(<?php echo html_escape($conversation['status']); ?>)</small>
        </h2>
        <a href="<?php echo site_url('admin/chatbot/conversations'); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="card">
        <div class="card-body" style="max-height:450px; overflow-y:auto; background:#f8f9fa;">
            <?php foreach ($messages as $m): ?>
                <div class="mb-2">
                    <strong class="<?php echo ($m['sender'] === 'agent') ? 'text-success' : (($m['sender'] === 'bot') ? 'text-primary' : (($m['sender'] === 'system') ? 'text-warning' : '')); ?>">
                        <?php echo strtoupper($m['sender']); ?>:
                    </strong>
                    <?php echo nl2br(html_escape($m['message'])); ?>
                    <br><small class="text-muted"><?php echo $m['created_at']; ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($conversation['status'] !== 'closed'): ?>

        <?php if ($conversation['status'] === 'pending_human'): ?>
            <a href="<?php echo site_url('admin/chatbot/conversations/assign/' . $conversation['id']); ?>" class="btn btn-success">
                Prendre en charge cette conversation
            </a>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <?php echo form_open('admin/chatbot/conversations/reply/' . $conversation['id']); ?>
                        <div class="mb-3">
                            <textarea name="message" class="form-control" rows="3" placeholder="Votre reponse..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    <?php echo form_close(); ?>
                </div>
            </div>
        <?php endif; ?>

        <a href="<?php echo site_url('admin/chatbot/conversations/close/' . $conversation['id']); ?>"
           class="btn btn-outline-danger mt-2" onclick="return confirm('Cloturer cette conversation ?');">
            Cloturer la conversation
        </a>

    <?php endif; ?>

</div>
</div>
</main>

<?php include VIEWPATH.'includes/backend/Footer.php'; ?>
