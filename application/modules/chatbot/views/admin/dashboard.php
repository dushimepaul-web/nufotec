<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Utilisateurs</h5>
                    <h2><?= $stats['total_users'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Messages</h5>
                    <h2><?= $stats['total_messages'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Aujourd'hui</h5>
                    <h2><?= $stats['today_messages'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Actifs (7j)</h5>
                    <h2><?= $stats['active_users'] ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5>Derniers utilisateurs</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Téléphone</th>
                        <th>Messages</th>
                        <th>Dernière activité</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_users as $user): ?>
                    <tr>
                        <td><?= $user->phone ?></td>
                        <td><?= $user->total_messages ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($user->last_message_at)) ?></td>
                        <td><?= ucfirst($user->status) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>