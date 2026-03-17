<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Authentification consultation</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 300px; }
        h2 { text-align: center; color: #075e54; }
        input[type=password] { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 20px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #075e54; color: white; border: none; border-radius: 20px; cursor: pointer; font-size: 16px; }
        button:hover { background: #054c44; }
        .error { color: red; text-align: center; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔐 Consultation sécurisée</h2>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="error"><?= $this->session->flashdata('error') ?></div>
        <?php endif; ?>
        <form method="post" action="<?= site_url('Joinconsultation/verify') ?>">
            <input type="hidden" name="room_id" value="<?= $room_id ?>">
            <input type="hidden" name="user_id" value="<?= $user_id ?>">
            <input type="password" name="password" placeholder="Votre mot de passe" required>
            <button type="submit">Rejoindre la consultation</button>
        </form>
    </div>
</body>
</html>