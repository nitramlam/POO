<?php
// public/admin_exercises.php

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/ExerciseSession.php';
require_once __DIR__ . '/../classes/Tailwind.php';

// 1) Suppression d’une affectation unique
if (!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
    ExerciseSession::remove($conn, (int)$_GET['delete']);
    header('Location: /admin_exercises.php');
    exit;
}

// 2) Création : exercice à plusieurs sessions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $sessionIds   = $_POST['session_ids']   ?? [];
    $exerciseName = trim($_POST['exercise_name'] ?? '');
    // no nulls → 0
    $weight       = $_POST['weight']        !== '' ? (float)$_POST['weight']        : 0.0;
    $reps         = $_POST['repetitions']   !== '' ? (int)  $_POST['repetitions']     : 0;
    $sets         = $_POST['sets']          !== '' ? (int)  $_POST['sets']            : 0;
    $target       = $_POST['target_weight'] !== '' ? (float)$_POST['target_weight'] : 0.0;

    if ($exerciseName !== '' && count($sessionIds) > 0) {
        // find or insert exercise
        $stmt = $conn->prepare("SELECT id FROM exercises WHERE name = :name");
        $stmt->execute(['name'=>$exerciseName]);
        $eid = $stmt->fetchColumn();
        if (!$eid) {
            $ins = $conn->prepare("INSERT INTO exercises (name) VALUES (:name)");
            $ins->execute(['name'=>$exerciseName]);
            $eid = (int)$conn->lastInsertId();
        }
        // add to each session
        foreach ($sessionIds as $sid) {
            if (is_numeric($sid)) {
                ExerciseSession::addToSession(
                    $conn,(int)$sid,$eid,$weight,$reps,$sets,$target
                );
            }
        }
    }
    header('Location: /admin_exercises.php');
    exit;
}

// 3) Fetch all sessions with their users
$sessions = [];
$sql = "
  SELECT s.id,
         u.name AS user_name,
         s.name AS session_name
    FROM sessions s
    JOIN users u ON s.user_id = u.id
   ORDER BY u.name, s.name
";
foreach ($conn->query($sql) as $row) {
    $sessions[] = [
        'id'    => $row['id'],
        'label' => "{$row['user_name']} — {$row['session_name']}"
    ];
}

// 4) Fetch all assignments and group by user
$assign = [];
$sql2 = "
  SELECT es.id,
         u.name         AS user_name,
         s.name         AS session_name,
         e.name         AS exercise_name,
         es.weight, es.repetitions, es.sets, es.target_weight
    FROM exercises_sessions es
    JOIN exercises e  ON es.exercise_id = e.id
    JOIN sessions s   ON es.session_id  = s.id
    JOIN users u      ON s.user_id       = u.id
   ORDER BY u.name, s.name, e.name
";
foreach ($conn->query($sql2) as $r) {
    $assign[$r['user_name']][] = [
        'id'            => $r['id'],
        'session_name'  => $r['session_name'],
        'exercise_name' => $r['exercise_name']
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>🏋️ Gestion des Exercices</title>
  <?= Tailwind::includeCdn() ?>
</head>
<body class="bg-gray-50 min-h-screen">
  <main class="max-w-5xl mx-auto p-6 space-y-8">

    <!-- En-tête -->
    <div class="bg-white rounded-lg shadow p-6 text-center">
      <h1 class="text-2xl font-bold text-blue-800 flex items-center justify-center space-x-2">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-6 w-6 text-blue-600"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <span>Gestion des Exercices</span>
      </h1>
      <p class="text-blue-600">Ajouter des exercices aux sessions</p>
    </div>

    <!-- Formulaire de création -->
    <div class="bg-white rounded-lg shadow p-6 space-y-6">
      <h2 class="text-xl font-semibold text-gray-800 flex items-center space-x-2">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-5 w-5 text-blue-600"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4v16m8-8H4"/>
        </svg>
        <span>Créer un nouvel exercice</span>
      </h2>
      <form method="post" action="/admin_exercises.php" class="space-y-4">
        <input type="hidden" name="action" value="create">
        <!-- Exo name -->
        <input type="text"
               name="exercise_name"
               maxlength="50"
               placeholder="Ex: Développé couché"
               required
               class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-blue-300 focus:border-blue-300" />
        <!-- Sessions grid -->
        <div>
          <h3 class="text-gray-700 font-medium mb-2 flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5 text-blue-600"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2v-7H3v7a2 2 0 002 2z"/>
            </svg>
            <span>Sélectionnez les sessions</span>
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($sessions as $s): ?>
              <label class="flex items-center space-x-2 p-4 border border-gray-200 rounded-lg hover:shadow">
                <input type="checkbox" name="session_ids[]" value="<?= $s['id'] ?>"
                       class="h-5 w-5 text-blue-600 border-gray-300 rounded" />
                <div>
                  <p class="font-medium text-gray-800"><?= htmlspecialchars($s['label']) ?></p>
                </div>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <button type="submit"
                class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-md transition">
          + Créer et ajouter aux sessions
        </button>
      </form>
    </div>

    <!-- Répartition des exercices -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="bg-blue-600 text-white px-6 py-3 flex items-center space-x-2">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-5 w-5"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <span class="font-semibold">Répartition des exercices</span>
      </div>

      <div class="p-6 space-y-4">
        <?php foreach ($assign as $user => $list): ?>
          <details class="border border-gray-200 rounded-lg">
            <summary class="cursor-pointer px-4 py-2 font-medium text-gray-800 flex justify-between items-center">
              <span><?= htmlspecialchars($user) ?></span>
              <svg xmlns="http://www.w3.org/2000/svg"
                   class="h-4 w-4 text-gray-600 transform transition-transform group-open:rotate-180"
                   fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"/>
              </svg>
            </summary>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4">
              <?php foreach ($list as $item): ?>
                <div class="border border-gray-200 rounded-lg p-4 flex flex-col space-y-2">
                  <h3 class="font-medium text-gray-800 flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5 text-blue-600"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                    <span><?= htmlspecialchars($item['exercise_name']) ?></span>
                  </h3>
                  <p class="text-gray-600 flex items-center space-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 7v4m0 0v4m0-4h18"/>
                    </svg>
                    <span><?= htmlspecialchars($item['session_name']) ?></span>
                  </p>
                </div>
              <?php endforeach; ?>
            </div>
          </details>
        <?php endforeach; ?>
      </div>
    </div>

  </main>
</body>
</html>