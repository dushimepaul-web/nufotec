<div class="broadcast-container">
    <div class="broadcast-header">
        <h1><i class="fas fa-bullhorn"></i> Diffusion WhatsApp</h1>
        <p>Envoyez un message à tous vos groupes et/ou contacts</p>
    </div>
    
    <div class="broadcast-stats">
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <div class="stat-number"><?= $stats['total_groups'] ?></div>
            <div class="stat-label">Groupes</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-user"></i>
            <div class="stat-number"><?= $stats['total_participants'] ?></div>
            <div class="stat-label">Participants</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-clock"></i>
            <div class="stat-number"><?= $stats['pending_messages'] ?></div>
            <div class="stat-label">En attente</div>
        </div>
    </div>
    
    <form id="broadcastForm" method="post" enctype="multipart/form-data" action="<?= site_url('Whatsapp/send_broadcast') ?>">
        <div class="broadcast-form">
            <div class="form-group">
                <label>Cible de diffusion</label>
                <div class="target-options">
                    <label class="radio-label">
                        <input type="radio" name="target_type" value="both" checked>
                        <span>Tous (Groupes + Inbox)</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="target_type" value="groups">
                        <span>Groupes uniquement</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="target_type" value="inbox">
                        <span>Inbox (messages privés)</span>
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label>Type de média</label>
                <select name="media_type" id="mediaType" onchange="toggleMediaUpload()">
                    <option value="text">Texte</option>
                    <option value="image">Image</option>
                    <option value="video">Vidéo</option>
                    <option value="audio">Audio</option>
                    <option value="document">Document</option>
                </select>
            </div>
            
            <div class="form-group" id="mediaUpload" style="display: none;">
                <label>Fichier média</label>
                <input type="file" name="media" accept="image/*,video/*,audio/*,.pdf,.doc,.docx">
            </div>
            
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" rows="5" placeholder="Votre message ici..."></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn-broadcast">
                    <i class="fas fa-paper-plane"></i> Lancer la diffusion
                </button>
            </div>
        </div>
    </form>
    
    <div class="recent-broadcasts">
        <h3>Dernières diffusions</h3>
        <div class="broadcast-list">
            <!-- Liste des diffusions récentes -->
        </div>
    </div>
</div>

<style>
.broadcast-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 30px 20px;
}

.broadcast-header {
    text-align: center;
    margin-bottom: 30px;
    color: #e9edef;
}

.broadcast-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    flex: 1;
    background: #202c33;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
}

.stat-card i {
    font-size: 30px;
    color: #00a884;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #e9edef;
    margin: 10px 0;
}

.stat-label {
    color: #8696a0;
}

.broadcast-form {
    background: #202c33;
    border-radius: 12px;
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #e9edef;
    font-weight: 500;
}

.form-group input, 
.form-group select, 
.form-group textarea {
    width: 100%;
    padding: 12px;
    background: #111b21;
    border: none;
    border-radius: 8px;
    color: #e9edef;
}

.form-group textarea {
    resize: vertical;
}

.target-options {
    display: flex;
    gap: 20px;
}

.radio-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: #e9edef;
}

.btn-broadcast {
    width: 100%;
    padding: 14px;
    background: #00a884;
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    cursor: pointer;
}

.btn-broadcast i {
    margin-right: 8px;
}
</style>

<script>
function toggleMediaUpload() {
    const mediaType = $('#mediaType').val();
    if (mediaType === 'text') {
        $('#mediaUpload').hide();
    } else {
        $('#mediaUpload').show();
    }
}
</script>