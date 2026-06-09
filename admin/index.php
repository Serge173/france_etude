<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

start_session();

if (is_admin_logged_in()) {
    redirect('dashboard.php');
}

$error = flash('error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST[CSRF_TOKEN_KEY] ?? null)) {
        flash('error', 'Session expirée. Réessayez.');
        redirect('index.php');
    }

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    try {
        $pdo = db();
        init_database($pdo);
        $stmt = $pdo->prepare('SELECT id, password_hash, name FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            set_admin_auth((int) $admin['id'], $admin['name'], $email);
            redirect('dashboard.php');
        }
        flash('error', 'Identifiants incorrects.');
        redirect('index.php');
    } catch (Throwable $e) {
        flash('error', 'Erreur de connexion. Vérifiez que install.php a été exécuté.');
        redirect('index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration — <?= e(APP_NAME) ?></title>
    <link rel="icon" href="<?= e(url_path(APP_LOGO)) ?>" type="image/png">
    <link rel="stylesheet" href="<?= e(url_path('assets/css/style.css')) ?>">
</head>
<body class="page-admin-auth">
    <main class="admin-auth-card">
        <img src="<?= e(url_path(APP_LOGO)) ?>" alt="" class="auth-logo" width="120" height="120">
        <h1>Administration</h1>
        <p>Connectez-vous pour gérer les candidatures.</p>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <label>Email<input type="email" name="email" required autocomplete="username"></label>
            <label>Mot de passe<input type="password" name="password" required autocomplete="current-password"></label>
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
        <p class="admin-auth-back"><a href="<?= e(url_path('/')) ?>">← Retour au site</a></p>
    </main>
</body>
</html>
