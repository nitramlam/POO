<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';

// 1) Connexion à la base
$db   = new Database();
$conn = $db->getConnection();

// 2) Chargement des utilisateurs
$users = User::fetchAll($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choix de l'utilisateur</title>
</head>
<body>
    <h1>Choisis ton profil</h1>
    <?php if (!empty($users)): ?>
        <ul>
            <?php foreach ($users as $u): ?>
                <li>
                    <a href="sessions.php?user_id=<?= htmlspecialchars($u->id) ?>">
                        <?= htmlspecialchars($u->name) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun utilisateur trouvé.</p>
    <?php endif; ?>
</body>
</html>