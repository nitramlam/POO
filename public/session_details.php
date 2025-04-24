<?php
// public/session_details.php

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/ExerciseSession.php';
require_once __DIR__ . '/../classes/Tailwind.php';

// 1) Récupération de l'ID de session
if (empty($_GET['session_id']) || !is_numeric($_GET['session_id'])) {
    header('Location: /sessions.php');
    exit;
}
$sessionId = (int) $_GET['session_id'];

// 2) Nom session + user
$stmtS = $conn->prepare("
    SELECT s.name AS session_name, u.name AS user_name
      FROM sessions s
      JOIN users u ON s.user_id = u.id
     WHERE s.id = :sid
");
$stmtS->execute(['sid' => $sessionId]);
$row = $stmtS->fetch(PDO::FETCH_ASSOC);
$sessionName = $row['session_name'] ?: 'Inconnue';
$userName    = $row['user_name']    ?: 'Utilisateur inconnu';

// 3a) Suppression d’une entrée
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    ExerciseSession::remove($conn, (int)$_GET['delete']);
    header("Location: /session_details.php?session_id={$sessionId}");
    exit;
}

// 3b) Création d’une entrée
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
        ExerciseSession::addToSession(
            $conn, $sessionId, $eid, $weight, $reps, $sets, $target
        );
    }
    header("Location: /session_details.php?session_id={$sessionId}");
    exit;
}

// 3c) Mise à jour d’une entrée
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $entryId = (int)($_POST['entry_id'] ?? 0);
    $weight  = is_numeric($_POST['weight'])        ? (float)$_POST['weight']        : 0;
    $reps    = is_numeric($_POST['repetitions'])   ? (int)  $_POST['repetitions']   : 0;
    $sets    = is_numeric($_POST['sets'])          ? (int)  $_POST['sets']          : 0;
    $target  = is_numeric($_POST['target_weight']) ? (float)$_POST['target_weight'] : 0;

    if ($entryId > 0) {
        // Direct SQL update since on a pas updateEntry()
        $upd = $conn->prepare("
            UPDATE exercises_sessions
               SET weight = :w,
                   repetitions = :r,
                   sets = :s,
                   target_weight = :tw
             WHERE id = :id
        ");
        $upd->execute([
            'w'  => $weight,
            'r'  => $reps,
            's'  => $sets,
            'tw' => $target,
            'id' => $entryId,
        ]);
    }
    header("Location: /session_details.php?session_id={$sessionId}");
    exit;
}

// 4) Récupération des entrées
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
  <title>Session « <?= htmlspecialchars($sessionName) ?> »</title>
  <?= Tailwind::includeCdn() ?>
</head>
<body class="bg-gray-50 min-h-screen">
  <main class="max-w-5xl mx-auto p-6">

    <!-- En-tête -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h1 class="text-2xl font-bold text-gray-800 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-6 w-6 text-blue-600 mr-2"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
        Exercices « <?= htmlspecialchars($sessionName) ?> »
      </h1>
      
    </div>

    <!-- Formulaire d'ajout -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <form method="post" action="?session_id=<?= $sessionId ?>" class="space-y-4">
        <input type="hidden" name="action" value="create">
        <div>
          <label class="block text-gray-700 font-medium">Nom de l’exercice</label>
          <input type="text"
                 name="exercise_name"
                 maxlength="50"
                 required
                 class="mt-1 w-full border-gray-300 rounded-md focus:ring-blue-300 focus:border-blue-300" />
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-gray-700">Poids (kg)</label>
            <input type="number" name="weight" step="0.01" max="999" value="0"
                   class="mt-1 w-full border-gray-300 rounded-md focus:ring-blue-300" />
          </div>
          <div>
            <label class="block text-gray-700">Objectif (kg)</label>
            <input type="number" name="target_weight" step="0.01" max="999" value="0"
                   class="mt-1 w-full border-gray-300 rounded-md focus:ring-blue-300" />
          </div>
          <div>
            <label class="block text-gray-700">Répétitions</label>
            <input type="number" name="repetitions" max="999" value="0"
                   class="mt-1 w-full border-gray-300 rounded-md focus:ring-blue-300" />
          </div>
          <div>
            <label class="block text-gray-700">Séries</label>
            <input type="number" name="sets" max="999" value="0"
                   class="mt-1 w-full border-gray-300 rounded-md focus:ring-blue-300" />
          </div>
        </div>
        <button type="submit"
                class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-md transition">
          Ajouter l’exercice
        </button>
      </form>
    </div>

    <!-- Cards des exercices -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php foreach ($entries as $e): ?>
        <div id="entry-<?= $e['id'] ?>" class="bg-white rounded-lg shadow overflow-hidden">
          <?php if ($editId === $e['id']): ?>
            <form method="post" action="?session_id=<?= $sessionId ?>" class="p-6 space-y-4">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="entry_id" value="<?= $e['id'] ?>">
              <h2 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($e['exercise_name']) ?></h2>
              <div class="grid grid-cols-2 gap-4">
                <input type="number" name="weight" step="0.01" max="999"
                       value="<?= htmlspecialchars($e['weight']) ?>"
                       class="border-gray-300 rounded-md focus:ring-blue-300 w-full" />
                <input type="number" name="target_weight" step="0.01" max="999"
                       value="<?= htmlspecialchars($e['target_weight']) ?>"
                       class="border-gray-300 rounded-md focus:ring-blue-300 w-full" />
                <input type="number" name="repetitions" max="999"
                       value="<?= htmlspecialchars($e['repetitions']) ?>"
                       class="border-gray-300 rounded-md focus:ring-blue-300 w-full" />
                <input type="number" name="sets" max="999"
                       value="<?= htmlspecialchars($e['sets']) ?>"
                       class="border-gray-300 rounded-md focus:ring-blue-300 w-full" />
              </div>
              <div class="flex justify-end space-x-4">
                <button type="submit"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition">
                  💾 Sauvegarder
                </button>
                <a href="?session_id=<?= $sessionId ?>"
                   class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md transition">
                  ✖️ Annuler
                </a>
              </div>
            </form>
          <?php else: ?>
            <div class="bg-blue-600 text-white px-4 py-2 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg"
                   class="h-5 w-5 mr-2"
                   fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 12h14M12 5l7 7-7 7" />
              </svg>
              <span class="font-semibold text-lg"><?= htmlspecialchars($e['exercise_name']) ?></span>
            </div>
            <div class="p-6 space-y-2">
              <p class="text-gray-700">
                <strong>Poids:</strong> <?= htmlspecialchars($e['weight']) ?> kg &nbsp;|&nbsp;
                <strong>Objectif:</strong> <?= htmlspecialchars($e['target_weight']) ?> kg
              </p>
              <p class="text-gray-700">
                <strong>Répétitions:</strong> <?= htmlspecialchars($e['repetitions']) ?> &nbsp;|&nbsp;
                <strong>Séries:</strong> <?= htmlspecialchars($e['sets']) ?>
              </p>
              <div class="mt-4 flex justify-between items-center">
                <a href="?session_id=<?= $sessionId ?>&edit=<?= $e['id'] ?>"
                   class="text-blue-600 hover:text-blue-800 text-xl">✏️</a>
                <a href="?session_id=<?= $sessionId ?>&delete=<?= $e['id'] ?>"
                   onclick="return confirm('Supprimer cet exercice ?')"
                   class="text-red-600 hover:text-red-800 text-xl">🗑️</a>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-8">
      <a href="/sessions.php" class="inline-block text-blue-600 hover:underline">
        ← Retour aux sessions
      </a>
    </div>
  </main>
</body>
</html>