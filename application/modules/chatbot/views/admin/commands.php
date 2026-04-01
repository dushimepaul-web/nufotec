<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Ajouter une commande</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label>Commande</label>
                            <input type="text" name="command" class="form-control" placeholder="/commande" required>
                        </div>
                        <div class="form-group">
                            <label>Réponse</label>
                            <textarea name="response" class="form-control" rows="5" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" class="form-control">
                                <option value="text">Texte</option>
                                <option value="image">Image</option>
                                <option value="video">Vidéo</option>
                                <option value="document">Document</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>URL média (si applicable)</label>
                            <input type="text" name="media_url" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_active" value="1" checked> Actif
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Commandes existantes</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Commande</th>
                                <th>Type</th>
                                <th>Réponse</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($commands as $cmd): ?>
                            <tr>
                                <td><code><?= $cmd->command ?></code></td>
                                <td><?= $cmd->type ?></td>
                                <td><?= substr($cmd->response, 0, 50) ?>...</td>
                                <td>
                                    <a href="<?= site_url('chatbot/admin/delete_command/'.$cmd->id) ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Supprimer cette commande ?')">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>