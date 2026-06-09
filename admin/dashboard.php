<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin();

$pdo = db();
init_database($pdo);

$data = require __DIR__ . '/../includes/data-schools.php';
$statuts = ['nouveau', 'en_cours', 'contacte', 'inscrit', 'refuse', 'archive'];

// Mise à jour statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf($_POST[CSRF_TOKEN_KEY] ?? null)) {
        flash('error', 'Session expirée.');
        redirect('dashboard.php');
    }

    if ($_POST['action'] === 'update_statut') {
        $id = (int) ($_POST['id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        if ($id > 0 && in_array($statut, $statuts, true)) {
            $upd = $pdo->prepare('UPDATE candidatures SET statut = ?, updated_at = ? WHERE id = ?');
            $upd->execute([$statut, date('Y-m-d H:i:s'), $id]);
            flash('success', 'Statut mis à jour.');
        }
        redirect('dashboard.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
    }
}

$filterStatut = $_GET['statut'] ?? '';
$filterEcole = $_GET['ecole'] ?? '';
$filterLogement = $_GET['logement'] ?? '';
$search = trim($_GET['q'] ?? '');

$where = ['1=1'];
$params = [];

if ($filterStatut && in_array($filterStatut, $statuts, true)) {
    $where[] = 'statut = ?';
    $params[] = $filterStatut;
}
if ($filterEcole && isset($data['schools'][$filterEcole])) {
    $where[] = 'ecole_code = ?';
    $params[] = $filterEcole;
}
if ($search !== '') {
    $where[] = '(prenom LIKE ? OR nom LIKE ? OR email LIKE ? OR telephone LIKE ? OR reference LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like, $like);
}
if ($filterLogement === '1') {
    $where[] = 'demande_logement = 1';
}

$sql = 'SELECT * FROM candidatures WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC LIMIT 500';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$candidatures = $stmt->fetchAll();

$counts = $pdo->query("
    SELECT statut, COUNT(*) as c FROM candidatures GROUP BY statut
")->fetchAll(PDO::FETCH_KEY_PAIR);

$total = (int) $pdo->query('SELECT COUNT(*) FROM candidatures')->fetchColumn();
$totalLogement = (int) $pdo->query('SELECT COUNT(*) FROM candidatures WHERE demande_logement = 1')->fetchColumn();

$detailId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$detail = null;
if ($detailId > 0) {
    $d = $pdo->prepare('SELECT * FROM candidatures WHERE id = ?');
    $d->execute([$detailId]);
    $detail = $d->fetch() ?: null;
}

$success = flash('success');
$error = flash('error');
$currentPage = 'dashboard';
$exportQuery = http_build_query(array_filter([
    'q' => $search,
    'statut' => $filterStatut,
    'ecole' => $filterEcole,
    'logement' => $filterLogement,
]));

function statut_label(string $s): string
{
    return match ($s) {
        'nouveau' => 'Nouveau',
        'en_cours' => 'En cours',
        'contacte' => 'Contacté',
        'inscrit' => 'Inscrit',
        'refuse' => 'Refusé',
        'archive' => 'Archivé',
        default => $s,
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatures — Admin <?= e(APP_NAME) ?></title>
    <link rel="icon" href="<?= e(url_path(APP_LOGO)) ?>" type="image/png">
    <link rel="stylesheet" href="<?= e(url_path('assets/css/style.css')) ?>">
</head>
<body class="page-admin">
<?php require __DIR__ . '/../includes/admin-nav.php'; ?>

    <main class="container admin-main">
        <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

        <div class="admin-stats">
            <div class="stat-card"><span class="stat-num"><?= $total ?></span><span>Total candidatures</span></div>
            <?php foreach (['nouveau', 'en_cours', 'inscrit'] as $s): ?>
            <div class="stat-card"><span class="stat-num"><?= (int) ($counts[$s] ?? 0) ?></span><span><?= e(statut_label($s)) ?></span></div>
            <?php endforeach; ?>
            <div class="stat-card stat-card-logement"><span class="stat-num"><?= $totalLogement ?></span><span>Demandes logement</span></div>
        </div>

        <form class="admin-filters form-inline" method="get">
            <input type="search" name="q" placeholder="Rechercher nom, email, tél., réf." value="<?= e($search) ?>">
            <select name="statut">
                <option value="">Tous statuts</option>
                <?php foreach ($statuts as $s): ?>
                <option value="<?= e($s) ?>" <?= $filterStatut === $s ? 'selected' : '' ?>><?= e(statut_label($s)) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="ecole">
                <option value="">Toutes écoles</option>
                <?php foreach ($data['schools'] as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= $filterEcole === $k ? 'selected' : '' ?>><?= e($k) ?></option>
                <?php endforeach; ?>
            </select>
            <label class="filter-checkbox">
                <input type="checkbox" name="logement" value="1" <?= $filterLogement === '1' ? 'checked' : '' ?>>
                Logement uniquement
            </label>
            <button type="submit" class="btn btn-outline">Filtrer</button>
            <a href="dashboard.php" class="btn btn-ghost">Réinitialiser</a>
        </form>

        <div class="admin-layout">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Réf.</th>
                            <th>Candidat</th>
                            <th>Contact</th>
                            <th>École / Campus</th>
                            <th>Logement</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($candidatures)): ?>
                        <tr><td colspan="8" class="empty">Aucune candidature trouvée.</td></tr>
                        <?php else: ?>
                        <?php foreach ($candidatures as $c): ?>
                        <tr class="<?= $detailId === (int) $c['id'] ? 'row-active' : '' ?>">
                            <td><code><?= e($c['reference']) ?></code></td>
                            <td><?= e($c['prenom'] . ' ' . $c['nom']) ?></td>
                            <td>
                                <a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a><br>
                                <small><?= e($c['telephone']) ?></small>
                                <?php if ($c['whatsapp']): ?><br><small>WA: <?= e($c['whatsapp']) ?></small><?php endif; ?>
                            </td>
                            <td>
                                <strong><?= e($c['ecole_code']) ?></strong><br>
                                <small><?= e($c['campus_libelle']) ?></small>
                            </td>
                            <td>
                                <?php if (!empty($c['demande_logement'])): ?>
                                <span class="badge badge-logement">Oui</span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e(date('d/m/Y H:i', strtotime($c['created_at']))) ?></td>
                            <td><span class="badge badge-<?= e($c['statut']) ?>"><?= e(statut_label($c['statut'])) ?></span></td>
                            <td><a href="?id=<?= (int) $c['id'] ?>&<?= e(http_build_query(array_filter(['q' => $search, 'statut' => $filterStatut, 'ecole' => $filterEcole, 'logement' => $filterLogement]))) ?>">Voir</a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($detail): ?>
            <aside class="admin-detail">
                <h2>Dossier <?= e($detail['reference']) ?></h2>
                <dl class="detail-list">
                    <dt>Nom</dt><dd><?= e($detail['prenom'] . ' ' . $detail['nom']) ?></dd>
                    <dt>Email</dt><dd><a href="mailto:<?= e($detail['email']) ?>"><?= e($detail['email']) ?></a></dd>
                    <dt>Téléphone</dt><dd><a href="tel:<?= e(preg_replace('/\s+/', '', $detail['telephone'])) ?>"><?= e($detail['telephone']) ?></a></dd>
                    <?php if ($detail['whatsapp']): ?>
                    <dt>WhatsApp</dt><dd><?= e($detail['whatsapp']) ?></dd>
                    <?php endif; ?>
                    <dt>Naissance</dt><dd><?= e($detail['date_naissance'] ?: '—') ?></dd>
                    <dt>Nationalité</dt><dd><?= e($detail['nationalite']) ?></dd>
                    <dt>Pays</dt><dd><?= e($detail['pays_residence']) ?></dd>
                    <dt>Niveau</dt><dd><?= e($detail['niveau_etudes']) ?></dd>
                    <dt>École</dt><dd><?= e($detail['ecole_libelle']) ?></dd>
                    <dt>Campus</dt><dd><?= e($detail['campus_libelle']) ?></dd>
                    <dt>Logement</dt><dd><?= !empty($detail['demande_logement']) ? 'Oui — demande enregistrée' : 'Non' ?></dd>
                    <dt>Filière</dt><dd><?= e($detail['domaine_libelle']) ?></dd>
                    <dt>Rentrée</dt><dd><?= e($detail['rentree']) ?></dd>
                    <dt>Langue</dt><dd><?= e($detail['langue_etudes']) ?></dd>
                    <dt>Français</dt><dd><?= e($detail['niveau_francais'] ?: '—') ?></dd>
                    <dt>Message</dt><dd><?= nl2br(e($detail['message'] ?: '—')) ?></dd>
                    <dt>Créé le</dt><dd><?= e($detail['created_at']) ?></dd>
                </dl>
                <form method="post" class="form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_statut">
                    <input type="hidden" name="id" value="<?= (int) $detail['id'] ?>">
                    <label>Statut du dossier
                        <select name="statut">
                            <?php foreach ($statuts as $s): ?>
                            <option value="<?= e($s) ?>" <?= $detail['statut'] === $s ? 'selected' : '' ?>><?= e(statut_label($s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
                <p><a href="dashboard.php">Fermer le détail</a></p>
            </aside>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
