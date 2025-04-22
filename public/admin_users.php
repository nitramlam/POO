<?php
// 1) Initialisation (connexion + menu)
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/User.php';

// 2) Traitement des actions CRUD
// 2a. Suppression si ?delete=ID
if (!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
    User::delete($conn, (int)$_GET['delete']);
    header('Location: /admin_users.php');
    exit;
}

// 2b. Création
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        User::create($conn, $name);
    }
    header('Location: /admin_users.php');
    exit;
}

// 2c. Mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id   = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if ($id > 0 && $name !== '') {
        User::update($conn, $id, $name);
    }
    header('Location: /admin_users.php');
    exit;
}

// 3) Récupération de tous les utilisateurs
$users = User::fetchAll($conn);

// 4) Vérifier si on est en mode édition pour un utilisateur
$editId = !empty($_GET['edit']) && is_numeric($_GET['edit'])
    ? (int)$_GET['edit']
    : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🛠️ Gestion des utilisateurs</title>
</head>
<body>
    <h1>👥 Gestion des utilisateurs</h1>

    <!-- Formulaire de création -->
    <h2>Ajouter un utilisateur</h2>
    <form action="/admin_users.php" method="post">
        <input type="hidden" name="action" value="create">
        <input type="text" name="name" placeholder="Nom de l'utilisateur" required>
        <button type="submit">Créer</button>
    </form>

    <hr>

    <!-- Liste des utilisateurs -->
    <h2>Utilisateurs existants</h2>
    <?php if (!empty($users)): ?>
        <ul>
            <?php foreach ($users as $u): ?>
                <li>
                    <?php if ($editId === $u->id): ?>
                        <!-- Formulaire d'édition inline -->
                        <form action="/admin_users.php" method="post" style="display:inline;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($u->id) ?>">
                            <input type="text" name="name" value="<?= htmlspecialchars($u->name) ?>" required>
                            <button type="submit">Sauvegarder</button>
                            <a href="/admin_users.php">Annuler</a>
                        </form>
                    <?php else: ?>
                        <?= htmlspecialchars($u->name) ?>
                        <a href="/admin_users.php?edit=<?= $u->id ?>">✏️</a>
                        <a href="/admin_users.php?delete=<?= $u->id ?>"
                           onclick="return confirm('Supprimer <?= htmlspecialchars($u->name) ?> ?')">🗑️</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun utilisateur enregistré.</p>
    <?php endif; ?>
</body>
</html>