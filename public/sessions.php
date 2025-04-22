<?php
// public/sessions.php

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/Session.php';

// détermination de l'ID utilisateur
if (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $userId = (int)$_GET['user_id'];
    $_SESSION['user_id'] = $userId;
} elseif (!empty($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
} else {
    header('Location: /index.php');
    exit;
}

// gestion du formulaire d'ajout de session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['session_name'])) {
    $name = trim($_POST['session_name']);
    if ($name !== '') {
        Session::create($conn, $userId, $name);
    }
    header('Location: /sessions.php');
    exit;
}

// récupération des sessions
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

    <!-- form ajout session -->
    <form method="post" action="/sessions.php">
        <input type="text" name="session_name" placeholder="Nouveau nom de session" required>
        <button type="submit">Ajouter une session</button>
    </form>

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

    <p><a href="/index.php">← Retour à la sélection de l'utilisateur</a></p>
</body>
</html>