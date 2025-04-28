<?php
// public/session_details.php

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Exercises.php';
require_once __DIR__ . '/../classes/ExerciseSession.php';
require_once __DIR__ . '/../classes/Tailwind.php';

// 1) Récupération de l'ID de session
$sessionId = Session::getCurrentSessionId();
if ($sessionId === null) {
    header('Location: /sessions.php');
    exit;
}

// 2) Récupère nom de session et utilisateur
[$sessionName, $userName] = Session::getSessionInfo($conn, $sessionId);

// 3) Gère actions create, update, delete pour les entrées
ExerciseSession::handleEntryActions($conn, $sessionId, $_REQUEST);

// 4) Récupération des entrées avec noms d'exercices
$entries = ExerciseSession::fetchBySessionWithNames($conn, $sessionId);

// 5) Mode édition inline
$editId = Session::getEditEntryId();
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
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
        Exercices « <?= htmlspecialchars($sessionName) ?> »
      </h1>
      <p class="text-gray-600">Utilisateur : <?= htmlspecialchars($userName) ?></p>
    </div>

    <!-- Formulaire d'ajout -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <form method="post" action="?session_id=<?= $sessionId ?>" class="space-y-4">
        <input type="hidden" name="action" value="create">
        <label class="block text-gray-700 font-medium">Nom de l’exercice</label>
        <input type="text" name="exercise_name" maxlength="50" required class="mt-1 w-full border-gray-300 rounded-md focus:ring-blue-300 focus:border-blue-300" />
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div><label class="block text-gray-700">Poids (kg)</label><input type="number" name="weight" step="0.01" max="999" value="0" class="mt-1 w-full border-gray-300 rounded-md focus:ring-blue-300" /></div>
          <div><label class="block text-gray-700">Objectif (kg)</label><input type="number" name="target_weight" step="0.01" max="999" value="0" class="mt-1 w-full border-gray-300 rounded-md focus:ring-blue-300" /></div>
          <div><label class="block text-gray-700">Répétitions</label><input type="number" name="repetitions" max="999" value="0" class="mt-1 w-full border-gray-300 rounded-md focus:ring-blue-300" /></div>
          <div><label class="block text-gray-700">Séries</label><input type="number" name="sets" max="999" value="0" class="mt-1 w-full border-gray-300 rounded-md focus:ring-blue-300" /></div>
        </div>
        <button type="submit" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-md transition">Ajouter l’exercice</button>
      </form>
    </div>

    <!-- Liste des exercices -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php foreach ($entries as $e): ?>
        <div id="entry-<?= $e['id'] ?>" class="bg-white rounded-lg shadow overflow-hidden">
          <?php if ($editId === $e['id']): ?>
            <form method="post" action="?session_id=<?= $sessionId ?>" class="p-6 space-y-4">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="entry_id" value="<?= $e['id'] ?>">
              <h2 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($e['exercise_name']) ?></h2>
              <div class="grid grid-cols-2 gap-4">
                <input type="number" name="weight" step="0.01" max="999" value="<?= htmlspecialchars($e['weight']) ?>" class="w-full border-gray-300 rounded-md focus:ring-blue-300" />
                <input type="number" name="target_weight" step="0.01" max="999" value="<?= htmlspecialchars($e['target_weight']) ?>" class="w-full border-gray-300 rounded-md focus:ring-blue-300" />
                <input type="number" name="repetitions" max="999" value="<?= htmlspecialchars($e['repetitions']) ?>" class="w-full border-gray-300 rounded-md focus:ring-blue-300" />
                <input type="number" name="sets" max="999" value="<?= htmlspecialchars($e['sets']) ?>" class="w-full border-gray-300 rounded-md focus:ring-blue-300" />
              </div>
              <div class="flex justify-end space-x-4">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition">💾 Sauvegarder</button>
                <a href="?session_id=<?= $sessionId ?>" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md transition">✖️ Annuler</a>
              </div>
            </form>
          <?php else: ?>
            <div class="bg-blue-600 text-white px-4 py-2 flex items-center">
              <span class="font-semibold text-lg"><?= htmlspecialchars($e['exercise_name']) ?></span>
            </div>
            <div class="p-6 space-y-2">
              <p class="text-gray-700"><strong>Poids:</strong> <?= htmlspecialchars($e['weight']) ?> kg &nbsp;|&nbsp;<strong>Objectif:</strong> <?= htmlspecialchars($e['target_weight']) ?> kg</p>
              <p class="text-gray-700"><strong>Répétitions:</strong> <?= htmlspecialchars($e['repetitions']) ?> &nbsp;|&nbsp;<strong>Séries:</strong> <?= htmlspecialchars($e['sets']) ?></p>
              <div class="mt-4 flex justify-between">
                <a href="?session_id=<?= $sessionId ?>&edit=<?= $e['id'] ?>" class="text-blue-600 text-xl">✏️</a>
                <a href="?session_id=<?= $sessionId ?>&delete=<?= $e['id'] ?>" onclick="return confirm('Supprimer cet exercice ?')" class="text-red-600 text-xl">🗑️</a>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-8">
      <a href="/sessions.php" class="inline-block text-blue-600 hover:underline">← Retour aux sessions</a>
    </div>
  </main>
</body>
</html>
