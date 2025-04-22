<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/Session.php';

// 1) On récupère l’ID utilisateur depuis le paramètre GET
if (empty($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    die('ID utilisateur invalide.');
}
$userId = (int) $_GET['user_id'];

// 2) Connexion à la base
$db   = new Database();
$conn = $db->getConnection();

// 3) Récupération des sessions pour cet utilisateur
$sessions = Session::fetchByUser($conn, $userId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Sessions de l'utilisateur <?= htmlspecialchars($userId) ?></title>
</head>
<body>
    <h1>Sessions de l'utilisateur <?= htmlspecialchars($userId) ?></h1>
    <?php if (!empty($sessions)): ?>
        <ul>
            <?php foreach ($sessions as $s): ?>
                <li>
                    <a href="session_details.php?session_id=<?= htmlspecialchars($s->id) ?>">
                        <?= htmlspecialchars($s->name) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucune session trouvée pour cet utilisateur.</p>
    <?php endif; ?>
    <p><a href="index.php">← Retour à la sélection de l'utilisateur</a></p>
</body>
</html>