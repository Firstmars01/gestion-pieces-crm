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

    $pdo->beginTransaction();

    // 1. S'assurer que le rôle ROLE_ADMIN existe
    $stmt = $pdo->prepare('SELECT id FROM role WHERE code = ?');
    $stmt->execute(['ROLE_ADMIN']);
    $roleId = $stmt->fetchColumn();

    if (!$roleId) {
        $insertRole = $pdo->prepare('INSERT INTO role (code) VALUES (?) RETURNING id');
        $insertRole->execute(['ROLE_ADMIN']);
        $roleId = $insertRole->fetchColumn();
    }

    // On vérifie si l'admin existe déjà
    $stmt = $pdo->prepare('SELECT id FROM "user" WHERE email = ?');
    $stmt->execute(['admin@crm.com']);
    $userId = $stmt->fetchColumn();

    if (!$userId) {
        // Mot de passe "admin123" haché
        $password = password_hash('admin123', PASSWORD_ARGON2ID);

        $insert = $pdo->prepare('INSERT INTO "user" (email, nom, prenom, actif, password) VALUES (?, ?, ?, ?, ?) RETURNING id');
        $insert->execute(['admin@crm.com', 'Dupont', 'Jean', true, $password]);
        $userId = $insert->fetchColumn();
        echo "Admin créé avec succès nativement !\n";
    } else {
        echo "L'admin existe déjà, pas besoin de le recréer.\n";
    }

    // 3. Lier l'utilisateur au rôle admin si nécessaire
    $link = $pdo->prepare('SELECT 1 FROM user_role WHERE user_id = ? AND role_id = ?');
    $link->execute([$userId, $roleId]);

    if (!$link->fetchColumn()) {
        $attach = $pdo->prepare('INSERT INTO user_role (user_id, role_id) VALUES (?, ?)');
        $attach->execute([$userId, $roleId]);
    }

    $pdo->commit();
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "Erreur d'insertion : " . $e->getMessage() . "\n";
}
