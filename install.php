<?php
/**
 * Installation initiale — à supprimer ou protéger après déploiement.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$message = '';
$error = '';
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? 'Administrateur');

    if (!validate_email($email) || strlen($password) < 8) {
        $error = 'Email valide et mot de passe (8 caractères minimum) requis.';
    } else {
        try {
            $pdo = db();
            init_database($pdo);

            $stmt = $pdo->query('SELECT COUNT(*) FROM admins');
            if ((int) $stmt->fetchColumn() > 0) {
                $error = 'Un administrateur existe déjà. Utilisez la page de connexion admin.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $now = date('Y-m-d H:i:s');
                $insert = $pdo->prepare(
                    'INSERT INTO admins (email, password_hash, name, created_at) VALUES (?, ?, ?, ?)'
                );
                $insert->execute([$email, $hash, $name, $now]);
                $message = 'Installation réussie. Connectez-vous sur /admin/ puis supprimez install.php.';
                $done = true;
            }
        } catch (Throwable $e) {
            $error = 'Erreur : ' . $e->getMessage();
        }
    }
} else {
    try {
        $pdo = db();
        init_database($pdo);
    } catch (Throwable $e) {
        $error = 'Base de données : ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation — <?= e(APP_NAME) ?></title>
    <link rel="icon" href="<?= e(APP_LOGO) ?>" type="image/png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="page-install">
    <main class="install-card">
        <img src="<?= e(APP_LOGO) ?>" alt="" class="auth-logo" width="120" height="120">
        <h1>Installation <?= e(APP_NAME) ?></h1>
        <p>Créez le compte administrateur pour gérer les candidatures.</p>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
        <?php if (!$done): ?>
        <form method="post" class="form">
            <label>Nom <input type="text" name="name" value="Administrateur" required></label>
            <label>Email admin <input type="email" name="email" required autocomplete="username"></label>
            <label>Mot de passe <input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
            <button type="submit" class="btn btn-primary">Installer</button>
        </form>
        <?php else: ?>
        <p><a href="admin/" class="btn btn-primary">Aller à l'administration</a></p>
        <?php endif; ?>
    </main>
</body>
</html>
