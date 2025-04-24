<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/ExerciseSession.php';

// 1) Récupération de l'ID de session
if (empty($_GET['session_id']) || !is_numeric($_GET['session_id'])) {
    header('Location: /sessions.php');
    exit;
}
$sessionId = (int) $_GET['session_id'];

// 2) Récupération du nom de la session et de l’utilisateur
$stmtS = $conn->prepare("
    SELECT s.name AS session_name, u.name AS user_name
      FROM sessions s
      JOIN users u ON s.user_id = u.id
     WHERE s.id = :sid
");
$stmtS->execute(['sid' => $sessionId]);
$row = $stmtS->fetch(PDO::FETCH_ASSOC);
$sessionName = $row['session_name'] ?? 'Inconnue';
$userName    = $row['user_name']    ?? 'Utilisateur inconnu';

// --- 3) CRUD local ---
// 3a) Suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    ExerciseSession::remove($conn, (int)$_GET['delete']);
    header("Location: /session_details.php?session_id={$sessionId}");
    exit;
}

// 3b) Création
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $exerciseName = trim($_POST['exercise_name'] ?? '');
    $weight       = is_numeric($_POST['weight'])        ? (float)$_POST['weight']        : 0;
    $reps         = is_numeric($_POST['repetitions'])   ? (int)  $_POST['repetitions']   : 0;
    $sets         = is_numeric($_POST['sets'])          ? (int)  $_POST['sets']          : 0;
    $target       = is_numeric($_POST['target_weight']) ? (float)$_POST['target_weight'] : 0;

    if ($exerciseName !== '') {
        // Cherche ou crée l'exercice
        $stmtE = $conn->prepare("SELECT id FROM exercises WHERE name = :name");
        $stmtE->execute(['name' => $exerciseName]);
        $eid = $stmtE->fetchColumn();
        if (!$eid) {
            $ins = $conn->prepare("INSERT INTO exercises (name) VALUES (:name)");
            $ins->execute(['name' => $exerciseName]);
            $eid = (int)$conn->lastInsertId();
        }
        // Ajoute l’entrée
        ExerciseSession::addToSession(
            $conn,
            $sessionId,
            $eid,
            $weight,
            $reps,
            $sets,
            $target
        );
    }
    header("Location: /session_details.php?session_id={$sessionId}");
    exit;
}

// 3c) Mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $entryId = (int)($_POST['entry_id'] ?? 0);
    $weight  = is_numeric($_POST['weight'])        ? (float)$_POST['weight']        : 0;
    $reps    = is_numeric($_POST['repetitions'])   ? (int)  $_POST['repetitions']   : 0;
    $sets    = is_numeric($_POST['sets'])          ? (int)  $_POST['sets']          : 0;
    $target  = is_numeric($_POST['target_weight']) ? (float)$_POST['target_weight'] : 0;

    if ($entryId > 0) {
        ExerciseSession::updateEntry(
            $conn,
            $entryId,
            $weight,
            $reps,
            $sets,
            $target
        );
    }
    header("Location: /session_details.php?session_id={$sessionId}");
    exit;
}

// 4) Récupération des entrées de la session
$sql = "
  SELECT es.id,
         e.name        AS exercise_name,
         es.weight,
         es.repetitions,
         es.sets,
         es.target_weight
    FROM exercises_sessions es
    JOIN exercises e ON es.exercise_id = e.id
   WHERE es.session_id = :sid
   ORDER BY e.name
";
$stmt = $conn->prepare($sql);
$stmt->execute(['sid' => $sessionId]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5) Mode édition inline
$editId = !empty($_GET['edit']) && is_numeric($_GET['edit'])
    ? (int)$_GET['edit']
    : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Session « <?= htmlspecialchars($sessionName) ?> » de <?= htmlspecialchars($userName) ?></title>
</head>
<body>
    <h1>Session « <?= htmlspecialchars($sessionName) ?> »</h1>
    <p>Utilisateur : <?= htmlspecialchars($userName) ?></p>

    <!-- Formulaire d’ajout -->
    <h2>Ajouter un exercice</h2>
    <form method="post" action="/session_details.php?session_id=<?= $sessionId ?>">
        <input type="hidden" name="action" value="create">
        <div>
            <label>Nom de l’exo :
                <input type="text" name="exercise_name" required maxlength="50">
            </label>
        </div>
        <div>
            <label>Poids (kg) :<input type="number" name="weight" step="0.01" value="0"></label>
            <label>Répétitions :<input type="number" name="repetitions" value="0"></label>
            <label>Séries :<input type="number" name="sets" value="0"></label>
            <label>Objectif (kg) :<input type="number" name="target_weight" step="0.01" value="0"></label>
        </div>
        <button type="submit">Ajouter</button>
    </form>

    <!-- Table des exercices -->
    <h2>Exercices enregistrés</h2>
    <?php if (empty($entries)): ?>
        <p>Aucun exercice pour cette session.</p>
    <?php else: ?>
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>Exercice</th><th>Poids</th><th>Rép.</th>
                    <th>Sér.</th><th>Cible</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($entries as $e): ?>
                <?php if ($editId === $e['id']): // édition inline ?>
                    <tr>
                    <form method="post" action="/session_details.php?session_id=<?= $sessionId ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="entry_id" value="<?= $e['id'] ?>">
                        <td><?= htmlspecialchars($e['exercise_name']) ?></td>
                        <td><input type="number" name="weight"        step="0.01" value="<?= htmlspecialchars($e['weight']) ?>"></td>
                        <td><input type="number" name="repetitions"                value="<?= htmlspecialchars($e['repetitions']) ?>"></td>
                        <td><input type="number" name="sets"                       value="<?= htmlspecialchars($e['sets']) ?>"></td>
                        <td><input type="number" name="target_weight" step="0.01" value="<?= htmlspecialchars($e['target_weight']) ?>"></td>
                        <td>
                            <button type="submit">💾</button>
                            <a href="/session_details.php?session_id=<?= $sessionId ?>">✖️ Annuler</a>
                        </td>
                    </form>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td><?= htmlspecialchars($e['exercise_name']) ?></td>
                        <td><?= htmlspecialchars($e['weight']) ?></td>
                        <td><?= htmlspecialchars($e['repetitions']) ?></td>
                        <td><?= htmlspecialchars($e['sets']) ?></td>
                        <td><?= htmlspecialchars($e['target_weight']) ?></td>
                        <td>
                            <a href="/session_details.php?session_id=<?= $sessionId ?>&edit=<?= $e['id'] ?>">✏️</a>
                            <a href="/session_details.php?session_id=<?= $sessionId ?>&delete=<?= $e['id'] ?>"
                               onclick="return confirm('Supprimer cet exercice ?')">🗑️</a>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="/sessions.php">← Retour aux sessions</a></p>
    <p><a href="/index.php">← Sélection d’utilisateur</a></p>
</body>
</html>