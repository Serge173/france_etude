<?php
declare(strict_types=1);

start_session();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminEmail = $_SESSION['admin_email'] ?? '';
$initial = mb_strtoupper(mb_substr($adminName, 0, 1));
$currentPage = $currentPage ?? '';
?>
<header class="admin-bar">
    <div class="container admin-bar-inner">
        <a href="dashboard.php" class="admin-brand">
            <img src="<?= e(url_path(APP_LOGO)) ?>" alt="" class="admin-brand-logo" width="36" height="36" decoding="async">
            <span><?= e(APP_NAME) ?> — Admin</span>
        </a>
        <nav class="admin-nav">
            <a href="dashboard.php" class="<?= $currentPage === 'dashboard' ? 'is-active' : '' ?>">Candidatures</a>
            <?php if (!empty($exportQuery)): ?>
            <a href="export.php?<?= e($exportQuery) ?>">Exporter CSV</a>
            <?php endif; ?>
            <a href="profil.php" class="btn-profile <?= $currentPage === 'profil' ? 'is-active' : '' ?>" title="Mon profil">
                <span class="profile-avatar" aria-hidden="true"><?= e($initial) ?></span>
                <span class="profile-label"><?= e($adminName) ?></span>
            </a>
            <a href="logout.php" class="admin-logout">Déconnexion</a>
        </nav>
    </div>
</header>
