<div class="groups-management">
    <div class="page-header">
        <h1><i class="fas fa-users"></i> Gestion des Groupes</h1>
        <button class="btn-primary" onclick="syncGroups()">
            <i class="fas fa-sync-alt"></i> Synchroniser
        </button>
    </div>
    
    <div class="groups-grid">
        <?php foreach ($groups as $group): ?>
        <div class="group-card">
            <div class="group-avatar">
                <i class="fas fa-users"></i>
            </div>
            <div class="group-info">
                <h3><?= htmlspecialchars($group->nom ?? 'Groupe sans nom') ?></h3>
                <p class="group-id"><?= $group->groupe_id ?></p>
                <div class="group-stats">
                    <span><i class="fas fa-user"></i> <?= $group->participant_count ?? 0 ?> participants</span>
                    <span><i class="fas fa-comment"></i> <?= $group->message_count ?? 0 ?> messages</span>
                </div>
            </div>
            <div class="group-actions">
                <label class="switch">
                    <input type="checkbox" <?= $group->actif ? 'checked' : '' ?> 
                           onchange="toggleGroup('<?= $group->groupe_id ?>', this.checked)">
                    <span class="slider"></span>
                </label>
                <button class="btn-icon" onclick="viewGroup('<?= $group->groupe_id ?>')" title="Voir">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn-icon" onclick="deleteGroup('<?= $group->groupe_id ?>')" title="Supprimer">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.groups-management {
    padding: 20px;
    background: #0a0a0a;
    min-height: 100vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    color: #e9edef;
}

.groups-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 15px;
}

.group-card {
    background: #202c33;
    border-radius: 12px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.group-avatar {
    width: 60px;
    height: 60px;
    background: #00a884;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.group-avatar i {
    font-size: 30px;
    color: white;
}

.group-info {
    flex: 1;
}

.group-info h3 {
    color: #e9edef;
    margin-bottom: 5px;
}

.group-id {
    color: #8696a0;
    font-size: 12px;
    margin-bottom: 8px;
}

.group-stats {
    display: flex;
    gap: 15px;
    font-size: 12px;
    color: #8696a0;
}

.group-stats i {
    margin-right: 5px;
}

.group-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

/* Switch toggle */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #2a3942;
    transition: 0.3s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #00a884;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.btn-primary {
    background: #00a884;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    color: white;
    cursor: pointer;
}
</style>

<script>
function syncGroups() {
    window.location.href = '<?= site_url("Whatsapp/sync_groups") ?>';
}

function toggleGroup(groupId, active) {
    $.post('<?= site_url("Whatsapp/toggle_group") ?>/' + groupId, {actif: active ? 1 : 0});
}

function viewGroup(groupId) {
    window.location.href = '<?= site_url("Whatsapp/participants") ?>/' + groupId;
}

function deleteGroup(groupId) {
    if (confirm('Supprimer ce groupe ?')) {
        window.location.href = '<?= site_url("Whatsapp/delete_group") ?>/' + groupId;
    }
}
</script>