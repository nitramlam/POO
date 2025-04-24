<?php
// public/admin_exercises.php

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/ExerciseSession.php';

// 1) Suppression d’une affectation unique
if (!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
    ExerciseSession::remove($conn, (int)$_GET['delete']);
    header('Location: /admin_exercises.php');
    exit;
}

// 2) Ajout d’un exercice à plusieurs sessions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $sessionIds   = $_POST['session_ids']   ?? [];
    $exerciseName = trim($_POST['exercise_name'] ?? '');

    // Par défaut, éviter les nulls en mettant 0
    $weight       = $_POST['weight']        !== '' ? (float)$_POST['weight']        : 0.0;
    $reps         = $_POST['repetitions']   !== '' ? (int)  $_POST['repetitions']     : 0;
    $sets         = $_POST['sets']          !== '' ? (int)  $_POST['sets']            : 0;
    $target       = $_POST['target_weight'] !== '' ? (float)$_POST['target_weight'] : 0.0;

    if ($exerciseName !== '' && count($sessionIds) > 0) {
        // Cherche ou crée l'exercice
        $stmt = $conn->prepare("SELECT id FROM exercises WHERE name = :name");
        $stmt->execute(['name' => $exerciseName]);
        $eid = $stmt->fetchColumn();
        if (!$eid) {
            $ins = $conn->prepare("INSERT INTO exercises (name) VALUES (:name)");
            $ins->execute(['name' => $exerciseName]);
            $eid = (int)$conn->lastInsertId();
        }
        // Ajoute pour chaque session cochée
        foreach ($sessionIds as $sid) {
            if (is_numeric($sid)) {
                ExerciseSession::addToSession(
                    $conn,
                    (int)$sid,
                    $eid,
                    $weight,
                    $reps,
                    $sets,
                    $target
                );
            }
        }
    }
    header('Location: /admin_exercises.php');
    exit;
}

// 3) Récupération de toutes les sessions avec leur utilisateur
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

// 4) Récupération des affectations existantes
$data = [];
$sql2 = "
  SELECT es.id,
         u.name   AS user_name,
         s.name   AS session_name,
         e.name   AS exercise_name,
         es.weight, es.repetitions, es.sets, es.target_weight
    FROM exercises_sessions es
    JOIN exercises e  ON es.exercise_id = e.id
    JOIN sessions s   ON es.session_id  = s.id
    JOIN users u      ON s.user_id       = u.id
   ORDER BY u.name, s.name, e.name
";
foreach ($conn->query($sql2) as $r) {
    $data[] = [
        htmlspecialchars($r['user_name']),
        htmlspecialchars($r['session_name']),
        htmlspecialchars($r['exercise_name']),
        htmlspecialchars($r['weight']        ?? '0'),
        htmlspecialchars($r['repetitions']   ?? '0'),
        htmlspecialchars($r['sets']          ?? '0'),
        htmlspecialchars($r['target_weight'] ?? '0'),
        "<a href=\"/admin_exercises.php?delete={$r['id']}\" onclick=\"return confirm('Supprimer cette affectation ?')\">🗑️</a>"
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>🏋️ Admin Exercices</title>
</head>
<body>
  <!-- header inclus via init.php -->

  <h1>Affecter un exercice à plusieurs sessions</h1>

  <form method="post" action="/admin_exercises.php">
    <input type="hidden" name="action" value="create">

    <div>
      <label for="exercise_name">Nom de l'exercice :</label><br>
      <input
        type="text"
        id="exercise_name"
        name="exercise_name"
        maxlength="20"
        required
      >
    </div>

    <fieldset style="margin-top:1em;">
      <legend>Choisir les sessions :</legend>
      <?php foreach ($sessions as $s): ?>
        <label style="display:block; margin:4px 0;">
          <input
            type="checkbox"
            name="session_ids[]"
            value="<?= $s['id'] ?>"
          >
          <?= htmlspecialchars($s['label']) ?>
        </label>
      <?php endforeach; ?>
    </fieldset>

    <div style="margin-top:1em;">
      <label for="weight">Poids :</label><br>
      <input
        type="number"
        id="weight"
        name="weight"
        step="0.01"
        max="999.99"
        value="0"
      >
    </div>
    <div>
      <label for="repetitions">Répétitions :</label><br>
      <input
        type="number"
        id="repetitions"
        name="repetitions"
        max="999"
        value="0"
      >
    </div>
    <div>
      <label for="sets">Séries :</label><br>
      <input
        type="number"
        id="sets"
        name="sets"
        max="999"
        value="0"
      >
    </div>
    <div>
      <label for="target_weight">Objectif poids :</label><br>
      <input
        type="number"
        id="target_weight"
        name="target_weight"
        step="0.01"
        max="999.99"
        value="0"
      >
    </div>

    <div style="margin-top:1em;">
      <button type="submit">Ajouter</button>
    </div>
  </form>

  <hr>

  <h2>Exercices assignés</h2>
  <?php if ($data): ?>
    <table border="1" cellpadding="5" cellspacing="0">
      <thead>
        <tr>
          <th>Utilisateur</th>
          <th>Session</th>
          <th>Exercice</th>
          <th>Poids</th>
          <th>Rép.</th>
          <th>Sér.</th>
          <th>Cible</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data as $row): ?>
          <tr>
            <?php foreach ($row as $cell): ?>
              <td><?= $cell ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Aucune affectation d'exercice.</p>
  <?php endif; ?>

  <p><a href="/admin_users.php">← Gérer les utilisateurs</a></p>
  <p><a href="/index.php">← Accueil</a></p>
</body>
</html>