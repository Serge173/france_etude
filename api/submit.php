<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
    }

    $data = require __DIR__ . '/../includes/data-schools.php';

    if (!verify_csrf($_POST[CSRF_TOKEN_KEY] ?? null)) {
        json_response(['success' => false, 'message' => 'Session expirée. Rechargez la page.'], 403);
    }

    // Anti-spam simple : honeypot
    if (!empty($_POST['website'])) {
        json_response(['success' => true, 'message' => 'Demande enregistrée.', 'reference' => generate_reference()]);
    }

    $required = [
        'prenom', 'nom', 'email', 'telephone', 'nationalite', 'pays_residence',
        'niveau_etudes', 'ecole', 'campus', 'domaine', 'rentree', 'langue_etudes', 'rgpd',
    ];

    foreach ($required as $field) {
        if (empty(trim((string) ($_POST[$field] ?? '')))) {
            json_response(['success' => false, 'message' => 'Veuillez remplir tous les champs obligatoires.'], 422);
        }
    }

    if (($_POST['rgpd'] ?? '') !== '1') {
        json_response(['success' => false, 'message' => 'Vous devez accepter le traitement de vos données.'], 422);
    }

    $email = strtolower(trim($_POST['email']));
    if (!validate_email($email)) {
        json_response(['success' => false, 'message' => 'Adresse email invalide.'], 422);
    }

    $ecoleCode = $_POST['ecole'];
    $campusCode = $_POST['campus'];
    $domaineCode = $_POST['domaine'];

    if (!isset($data['schools'][$ecoleCode], $data['campuses'][$campusCode], $data['domains'][$domaineCode])) {
        json_response(['success' => false, 'message' => 'Choix école, campus ou filière invalide.'], 422);
    }

    $rentree = $_POST['rentree'];
    if (!isset($data['intakes'][$rentree])) {
        json_response(['success' => false, 'message' => 'Session de rentrée invalide.'], 422);
    }

    $niveau = $_POST['niveau_etudes'];
    if (!isset($data['levels'][$niveau])) {
        json_response(['success' => false, 'message' => 'Niveau d\'études invalide.'], 422);
    }

    $prenom = sanitize_string($_POST['prenom'], 100);
    $nom = sanitize_string($_POST['nom'], 100);
    $telephone = sanitize_string($_POST['telephone'], 40);
    $whatsapp = sanitize_string($_POST['whatsapp'] ?? '', 40) ?: null;
    $dateNaissance = sanitize_string($_POST['date_naissance'] ?? '', 20) ?: null;
    $nationalite = sanitize_string($_POST['nationalite'], 80);
    $pays = sanitize_string($_POST['pays_residence'], 80);
    $langue = in_array($_POST['langue_etudes'], ['fr', 'en', 'bilingue'], true) ? $_POST['langue_etudes'] : 'fr';
    $niveauFr = sanitize_string($_POST['niveau_francais'] ?? '', 80) ?: null;
    $message = sanitize_string($_POST['message'] ?? '', 2000) ?: null;
    $demandeLogement = isset($_POST['demande_logement']) && $_POST['demande_logement'] === '1' ? 1 : 0;

    $reference = generate_reference();
    $now = date('Y-m-d H:i:s');

    $pdo = db();
    init_database($pdo);

    $stmt = $pdo->prepare('
        INSERT INTO candidatures (
            reference, prenom, nom, email, telephone, whatsapp, date_naissance,
            nationalite, pays_residence, niveau_etudes, ecole_code, ecole_libelle,
            campus_code, campus_libelle, domaine_code, domaine_libelle, rentree,
            langue_etudes, niveau_francais, message, demande_logement, statut, ip_address, user_agent, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ');

    $stmt->execute([
        $reference,
        $prenom,
        $nom,
        $email,
        $telephone,
        $whatsapp,
        $dateNaissance,
        $nationalite,
        $pays,
        $niveau,
        $ecoleCode,
        $data['schools'][$ecoleCode],
        $campusCode,
        $data['campuses'][$campusCode],
        $domaineCode,
        $data['domains'][$domaineCode],
        $rentree,
        $langue,
        $niveauFr,
        $message,
        $demandeLogement,
        'nouveau',
        $_SERVER['REMOTE_ADDR'] ?? null,
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500) ?: null,
        $now,
    ]);

    json_response([
        'success' => true,
        'message' => 'Votre candidature a bien été envoyée.' . ($demandeLogement ? ' Votre demande de logement sera traitée par notre équipe.' : '') . ' Nous vous contacterons sous 48 h ouvrées.',
        'reference' => $reference,
    ]);
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'UNIQUE')) {
        json_response(['success' => false, 'message' => 'Erreur technique, réessayez.'], 500);
    }
    json_response(['success' => false, 'message' => 'Erreur base de données. Réessayez plus tard.'], 500);
} catch (Throwable $e) {
    $msg = 'Erreur serveur. Réessayez plus tard.';
    if (str_contains($e->getMessage(), 'POSTGRES_URL') || str_contains($e->getMessage(), 'DATABASE_URL')) {
        $msg = 'Base de données non configurée. Contactez l\'administrateur du site.';
    }
    json_response(['success' => false, 'message' => $msg], 500);
}
