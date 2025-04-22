<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/ExerciseSession.php';

// 1) Détection des actions CRUD
// 1a. Suppression
if (!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
    ExerciseSession::remove($conn, (int)$_GET['delete']);
    $sid = isset($_GET['session_id']) ? '&session_id=' . (int)$_GET['session_id'] : '';
    header('Location: /admin_exercises.php?' . ltrim($sid, '&'));
    exit;
}

// 1b. Création
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $sid  = (int)($_POST['session_id'] ?? 0);
    $name = trim($_POST['exercise_name'] ?? '');
    $w    = $_POST['weight']        !== '' ? (float)$_POST['weight']        : null;
    $r    = $_POST['repetitions']   !== '' ? (int)$_POST['repetitions']     : null;
    $s    = $_POST['sets']          !== '' ? (int)$_POST['sets']            : null;
    $tw   = $_POST['target_weight'] !== '' ? (float)$_POST['target_weight'] : null;

    if ($sid > 0 && $name !== '') {
        // 1. Cherche ou crée l'exercice dans la table exercises
        $stmt = $conn->prepare("SELECT id FROM exercises WHERE name = :name");
        $stmt->execute(['name' => $name]);
        $eid = $stmt->fetchColumn();
        if (!$eid) {
            $ins = $conn->prepare("INSERT INTO exercises (name) VALUES (:name)");
            $ins->execute(['name' => $name]);
            $eid = (int)$conn->lastInsertId();
        }

        // 2. Ajout dans exercises_sessions
        ExerciseSession::addToSession($conn, $sid, $eid, $w, $r, $s, $tw);
    }

    header('Location: /admin_exercises.php?session_id=' . $sid);
    exit;
}

// 2) Récupération des sessions disponibles
$sessions = [];
foreach ($conn->query("SELECT id, name FROM sessions") as $row) {
    $sessions[(int)$row['id']] = $row['name'];
}

// 3) Session sélectionnée
$selected = (!empty($_GET['session_id']) && is_numeric($_GET['session_id']))
    ? (int)$_GET['session_id']
    : null;

// 4) Récupération des exercices de la session
$entries = $selected
    ? ExerciseSession::fetchBySession($conn, $selected)
    : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🏋️ Gestion des exercices</title>
</head>
<body>
    <h1>🏋️ Gestion des exercices</h1>

    <!-- Sélecteur de session -->
    <form method="get" action="/admin_exercises.php">
        <label for="session_id">Choisir une session :</label>
        <select name="session_id" id="session_id" required>
            <option value="">-- Sélectionnez --</option>
            <?php foreach ($sessions as $id => $name): ?>
                <option value="<?= $id ?>"<?= $selected === $id ? ' selected' : '' ?>>
                    <?= htmlspecialchars($name) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Voir</button>
    </form>

    <?php if ($selected): ?>
        <hr>
        <h2>Ajouter un exercice à « <?= htmlspecialchars($sessions[$selected]) ?> »</h2>
        <form method="post" action="/admin_exercises.php">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="session_id" value="<?= $selected ?>">

            <div>
                <label for="exercise_name">Nom de l'exercice :</label>
                <input type="text" id="exercise_name" name="exercise_name" required>
            </div>
            <div>
                <label for="weight">Poids :</label>
                <input type="text" id="weight" name="weight">
            </div>
            <div>
                <label for="repetitions">Répétitions :</label>
                <input type="number" id="repetitions" name="repetitions">
            </div>
            <div>
                <label for="sets">Séries :</label>
                <input type="number" id="sets" name="sets">
            </div>
            <div>
                <label for="target_weight">Objectif poids :</label>
                <input type="text" id="target_weight" name="target_weight">
            </div>
            <button type="submit">Ajouter</button>
        </form>

        <hr>
        <h2>Exercices existants</h2>
        <?php if (!empty($entries)): ?>
            <table border="1" cellpadding="5">
                <thead>
                    <tr>
                        <th>ID lien</th>
                        <th>Nom Exercice</th>
                        <th>Poids</th>
                        <th>Répétitions</th>
                        <th>Séries</th>
                        <th>Objectif</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($entries as $e): 
                    // Récupération du nom de l'exercice
                    $stmt = $conn->prepare("SELECT name FROM exercises WHERE id = :eid");
                    $stmt->execute(['eid' => $e->exercise_id]);
                    $exoName = $stmt->fetchColumn() ?: 'Inconnu';
                ?>
                    <tr>
                        <td><?= $e->id ?></td>
                        <td><?= htmlspecialchars($exoName) ?></td>
                        <td><?= htmlspecialchars($e->weight ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($e->repetitions ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($e->sets ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($e->target_weight ?? 'N/A') ?></td>
                        <td>
                            <a href="/admin_exercises.php?delete=<?= $e->id ?>&amp;session_id=<?= $selected ?>"
                               onclick="return confirm('Supprimer cet exercice de la session ?')">
                                🗑️
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun exercice associé à cette session.</p>
        <?php endif; ?>
    <?php endif; ?>

    <p><a href="/admin_users.php">← Gérer les utilisateurs</a></p>
    <p><a href="/">← Accueil</a></p>
</body>
</html>