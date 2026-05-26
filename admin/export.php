<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin();

$pdo = db();
$data = require __DIR__ . '/../includes/data-schools.php';
$statuts = ['nouveau', 'en_cours', 'contacte', 'inscrit', 'refuse', 'archive'];

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

$sql = 'SELECT * FROM candidatures WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$filename = 'candidatures_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 Excel

fputcsv($out, [
    'Référence', 'Prénom', 'Nom', 'Email', 'Téléphone', 'WhatsApp',
    'Naissance', 'Nationalité', 'Pays', 'Niveau', 'École', 'Campus',
    'Filière', 'Rentrée', 'Langue', 'Niveau FR', 'Demande logement', 'Message', 'Statut', 'Date',
], ';');

foreach ($rows as $r) {
    fputcsv($out, [
        $r['reference'],
        $r['prenom'],
        $r['nom'],
        $r['email'],
        $r['telephone'],
        $r['whatsapp'],
        $r['date_naissance'],
        $r['nationalite'],
        $r['pays_residence'],
        $r['niveau_etudes'],
        $r['ecole_libelle'],
        $r['campus_libelle'],
        $r['domaine_libelle'],
        $r['rentree'],
        $r['langue_etudes'],
        $r['niveau_francais'],
        !empty($r['demande_logement']) ? 'Oui' : 'Non',
        $r['message'],
        $r['statut'],
        $r['created_at'],
    ], ';');
}

fclose($out);
exit;
