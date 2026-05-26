<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (DB_DRIVER === 'mysql') {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            DB_MYSQL_HOST,
            DB_MYSQL_NAME
        );
        $pdo = new PDO($dsn, DB_MYSQL_USER, DB_MYSQL_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } else {
        $dir = dirname(DB_SQLITE_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $pdo = new PDO('sqlite:' . DB_SQLITE_PATH, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
    }

    return $pdo;
}

function init_database(PDO $pdo): void
{
    $isSqlite = DB_DRIVER === 'sqlite';

    $autoIncrement = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
    $datetime = $isSqlite ? 'TEXT' : 'DATETIME';
    $text = $isSqlite ? 'TEXT' : 'TEXT';
    $varchar = fn(int $n) => $isSqlite ? 'TEXT' : "VARCHAR({$n})";
    $logementType = $isSqlite ? 'INTEGER' : 'TINYINT(1)';

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id {$autoIncrement},
            email {$varchar(191)} NOT NULL UNIQUE,
            password_hash {$varchar(255)} NOT NULL,
            name {$varchar(120)} NOT NULL,
            created_at {$datetime} NOT NULL
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS candidatures (
            id {$autoIncrement},
            reference {$varchar(32)} NOT NULL UNIQUE,
            prenom {$varchar(100)} NOT NULL,
            nom {$varchar(100)} NOT NULL,
            email {$varchar(191)} NOT NULL,
            telephone {$varchar(40)} NOT NULL,
            whatsapp {$varchar(40)} DEFAULT NULL,
            date_naissance {$varchar(20)} DEFAULT NULL,
            nationalite {$varchar(80)} NOT NULL,
            pays_residence {$varchar(80)} NOT NULL,
            niveau_etudes {$varchar(40)} NOT NULL,
            ecole_code {$varchar(40)} NOT NULL,
            ecole_libelle {$text} NOT NULL,
            campus_code {$varchar(40)} NOT NULL,
            campus_libelle {$varchar(80)} NOT NULL,
            domaine_code {$varchar(60)} NOT NULL,
            domaine_libelle {$varchar(120)} NOT NULL,
            rentree {$varchar(40)} NOT NULL,
            langue_etudes {$varchar(20)} NOT NULL,
            niveau_francais {$varchar(80)} DEFAULT NULL,
            message {$text} DEFAULT NULL,
            demande_logement {$logementType} NOT NULL DEFAULT 0,
            statut {$varchar(30)} NOT NULL DEFAULT 'nouveau',
            ip_address {$varchar(45)} DEFAULT NULL,
            user_agent {$text} DEFAULT NULL,
            created_at {$datetime} NOT NULL,
            updated_at {$datetime} DEFAULT NULL
        )
    ");

    migrate_database($pdo);

    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_candidatures_email ON candidatures(email)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_candidatures_statut ON candidatures(statut)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_candidatures_created ON candidatures(created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_candidatures_logement ON candidatures(demande_logement)");
}

function migrate_database(PDO $pdo): void
{
    $isSqlite = DB_DRIVER === 'sqlite';

    if ($isSqlite) {
        $cols = $pdo->query('PRAGMA table_info(candidatures)')->fetchAll();
        $names = array_column($cols, 'name');
        if (!in_array('demande_logement', $names, true)) {
            $pdo->exec('ALTER TABLE candidatures ADD COLUMN demande_logement INTEGER NOT NULL DEFAULT 0');
        }
    } else {
        $stmt = $pdo->query("SHOW COLUMNS FROM candidatures LIKE 'demande_logement'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec('ALTER TABLE candidatures ADD COLUMN demande_logement TINYINT(1) NOT NULL DEFAULT 0');
        }
    }
}
