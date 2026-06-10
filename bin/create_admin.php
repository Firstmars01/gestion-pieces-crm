<?php
// bin/create_admin.php
$url = getenv('DATABASE_URL');
if (!$url) exit(0);

// Extraction des composants de l'URL
$dbopts = parse_url($url);

// FIX : Si le port n'est pas défini, on force le port 5432 par défaut
$port = isset($dbopts['port']) ? $dbopts['port'] : '5432';

$dsn = "pgsql:host={$dbopts['host']};port={$port};dbname=".ltrim($dbopts['path'],'/');

try {
    $pdo = new PDO($dsn, $dbopts['user'], $dbopts['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // On vérifie si l'admin existe déjà
    $stmt = $pdo->prepare('SELECT id FROM "user" WHERE email = ?');
    $stmt->execute(['admin@crm.com']);

    if (!$stmt->fetch()) {
        // Mot de passe "admin123" haché
        $password = password_hash('admin123', PASSWORD_ARGON2ID);

        $insert = $pdo->prepare('INSERT INTO "user" (email, nom, prenom, roles, password, actif) VALUES (?, ?, ?, ?, ?, ?)');
        $insert->execute(['admin@crm.com', 'Dupont', 'Jean', '["ROLE_ADMIN"]', $password, 'true']);
        echo "Admin créé avec succès nativement !\n";
    } else {
        echo "L'admin existe déjà, pas besoin de le recréer.\n";
    }
} catch (Exception $e) {
    echo "Erreur d'insertion : " . $e->getMessage() . "\n";
}
