<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin();

$pdo = db();
init_database($pdo);

$adminId = (int) ($_SESSION['admin_id'] ?? 0);
$stmt = $pdo->prepare('SELECT id, email, name, created_at FROM admins WHERE id = ? LIMIT 1');
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    flash('error', 'Compte introuvable.');
    redirect('logout.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST[CSRF_TOKEN_KEY] ?? null)) {
        flash('error', 'Session expirée. Réessayez.');
        redirect('profil.php');
    }

    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $hashStmt = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ?');
    $hashStmt->execute([$adminId]);
    $row = $hashStmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
        flash('error', 'Mot de passe actuel incorrect.');
        redirect('profil.php');
    }

    if (strlen($new) < 8) {
        flash('error', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
        redirect('profil.php');
    }

    if ($new !== $confirm) {
        flash('error', 'La confirmation ne correspond pas au nouveau mot de passe.');
        redirect('profil.php');
    }

    if (password_verify($new, $row['password_hash'])) {
        flash('error', 'Le nouveau mot de passe doit être différent de l\'ancien.');
        redirect('profil.php');
    }

    $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
        ->execute([password_hash($new, PASSWORD_DEFAULT), $adminId]);

    set_admin_auth($adminId, $admin['name'], $admin['email']);
    flash('success', 'Mot de passe modifié avec succès.');
    redirect('profil.php');
}

$success = flash('success');
$error = flash('error');
$currentPage = 'profil';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil — Admin <?= e(APP_NAME) ?></title>
    <link rel="icon" href="<?= e(url_path(APP_LOGO)) ?>" type="image/png">
    <link rel="stylesheet" href="<?= e(url_path('assets/css/style.css')) ?>">
</head>
<body class="page-admin">
<?php require __DIR__ . '/../includes/admin-nav.php'; ?>

<main class="container admin-main">
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <div class="profil-layout">
        <section class="profil-card">
            <h1>Mon profil</h1>
            <dl class="detail-list profil-info">
                <dt>Nom</dt><dd><?= e($admin['name']) ?></dd>
                <dt>Email</dt><dd><?= e($admin['email']) ?></dd>
                <dt>Compte créé le</dt><dd><?= e(date('d/m/Y', strtotime($admin['created_at']))) ?></dd>
            </dl>
            <p><a href="dashboard.php">← Retour aux candidatures</a></p>
        </section>

        <section class="profil-card">
            <h2>Changer le mot de passe</h2>
            <form method="post" class="form profil-form">
                <?= csrf_field() ?>
                <label>Mot de passe actuel *
                    <input type="password" name="current_password" required autocomplete="current-password">
                </label>
                <label>Nouveau mot de passe * <small>(8 caractères minimum)</small>
                    <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
                </label>
                <label>Confirmer le nouveau mot de passe *
                    <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
                </label>
                <button type="submit" class="btn btn-primary">Enregistrer le nouveau mot de passe</button>
            </form>
        </section>
    </div>
</main>
</body>
</html>
