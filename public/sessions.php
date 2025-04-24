<?php
// public/sessions.php

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/Session.php';

// 1) Détermination de l'ID utilisateur
if (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $userId = (int)$_GET['user_id'];
    $_SESSION['user_id'] = $userId;
} elseif (!empty($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
} else {
    header('Location: /index.php');
    exit;
}

// 2) Récupération du nom de l'utilisateur
$stmt = $conn->prepare("SELECT name FROM users WHERE id = :uid");
$stmt->execute(['uid' => $userId]);
$userName = $stmt->fetchColumn() ?: 'Utilisateur inconnu';

// 3) Suppression d’une session
if (!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM sessions WHERE id = :id AND user_id = :uid");
    $stmt->execute(['id' => $delId, 'uid' => $userId]);
    header('Location: /sessions.php');
    exit;
}

// 4) Création d’une session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $name = trim($_POST['session_name'] ?? '');
    if ($name !== '') {
        // limiter à 20 caractères
        $name = mb_substr($name, 0, 20);
        $stmt = $conn->prepare("INSERT INTO sessions (user_id, name) VALUES (:uid, :name)");
        $stmt->execute(['uid' => $userId, 'name' => $name]);
    }
    header('Location: /sessions.php');
    exit;
}

// 5) Mise à jour d’une session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id   = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['session_name'] ?? '');
    if ($id > 0 && $name !== '') {
        $name = mb_substr($name, 0, 20);
        $stmt = $conn->prepare("UPDATE sessions SET name = :name WHERE id = :id AND user_id = :uid");
        $stmt->execute(['name' => $name, 'id' => $id, 'uid' => $userId]);
    }
    header('Location: /sessions.php');
    exit;
}

// 6) Récupération des sessions de l’utilisateur
$sessions = Session::fetchByUser($conn, $userId);

// 7) Détection du mode édition
$editId = !empty($_GET['edit']) && is_numeric($_GET['edit'])
    ? (int)$_GET['edit']
    : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Sessions de <?= htmlspecialchars($userName) ?></title>
</head>
<body>
    <h1>Sessions de <?= htmlspecialchars($userName) ?></h1>

    <!-- Création de session -->
    <form method="post" action="/sessions.php" style="margin-bottom:1em;">
        <input type="hidden" name="action" value="create">
        <input
            type="text"
            name="session_name"
            placeholder="Nouveau nom de session (max 20 car.)"
            maxlength="20"
            required
        >
        <button type="submit">Ajouter</button>
    </form>

    <!-- Liste des sessions -->
    <?php if (!empty($sessions)): ?>
        <ul>
        <?php foreach ($sessions as $s): ?>
            <li style="margin-bottom:0.5em;">
                <?php if ($editId === $s->id): ?>
                    <!-- Formulaire d'édition inline -->
                    <form method="post" action="/sessions.php" style="display:inline;">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $s->id ?>">
                        <input
                            type="text"
                            name="session_name"
                            value="<?= htmlspecialchars($s->name) ?>"
                            maxlength="20"
                            required
                            style="width:200px;"
                        >
                        <button type="submit">Sauvegarder</button>
                        <a href="/sessions.php">Annuler</a>
                    </form>
                <?php else: ?>
                    <?= htmlspecialchars($s->name) ?>
                    <a href="/sessions.php?edit=<?= $s->id ?>">✏️</a>
                    <a
                        href="/sessions.php?delete=<?= $s->id ?>"
                        onclick="return confirm('Supprimer cette session ?')"
                    >🗑️</a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucune session trouvée pour <?= htmlspecialchars($userName) ?>.</p>
    <?php endif; ?>

    <p><a href="/index.php">← Retour à la sélection de l'utilisateur</a></p>
</body>
</html>