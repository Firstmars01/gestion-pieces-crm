<?php
// bin/create_admin.php
$url = getenv('DATABASE_URL');
if (!$url) exit(0);

// Extraction des composants de l'URL
$dbopts = parse_url($url);
$dsn = "pgsql:host={$dbopts['host']};port={$dbopts['port']};dbname=".ltrim($dbopts['path'],'/');

try {
    $pdo = new PDO($dsn, $dbopts['user'], $dbopts['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // On vérifie si l'admin existe déjà
    $stmt = $pdo->prepare('SELECT id FROM "user" WHERE email = ?');
    $stmt->execute(['admin@crm.com']);

    if (!$stmt->fetch()) {
        // Mot de passe "admin123" haché au format standard de Symfony (bcrypt/argon2id)
        // Note : Symfony 6/7 utilise généralement le hachage natif de PHP sous le capot
        $password = password_hash('admin123', PASSWORD_ARGON2ID);

        $insert = $pdo->prepare('INSERT INTO "user" (email, nom, prenom, roles, password, actif) VALUES (?, ?, ?, ?, ?, ?)');
        $insert->execute(['admin@crm.com', 'Dupont', 'Jean', '["ROLE_ADMIN"]', $password, 'true']);
        echo "Admin créé avec succès nativment !\n";
    }
} catch (Exception $e) {
    echo "Erreur d'insertion : " . $e->getMessage() . "\n";
}
