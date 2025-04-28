<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Tailwind.php';

// 1) Récupère l'utilisateur connecté
$userId = Session::getCurrentUserId();
if (!$userId) {
    header('Location: /index.php');
    exit;
}

// 2) Récupère son nom
$userName = Session::getCurrentUserName($conn, $userId);

// 3) Gère les actions create / update / delete
Session::handleActions($conn, $userId);

// 4) Récupère les sessions de l'utilisateur
$sessions = Session::fetchByUser($conn, $userId);
?>

<!-- et ensuite ton HTML habituel pour afficher la page -->
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes Sessions</title>
  <?= Tailwind::includeCdn() ?>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">

  <header class="bg-white shadow">
    <div class="container mx-auto px-4 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center space-x-2 mb-4 sm:mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <h1 class="text-2xl font-bold text-gray-800">Mes Sessions</h1>
      </div>
      <form method="post" action="/sessions.php" class="w-full sm:w-auto flex flex-col sm:flex-row sm:space-x-2">
        <input type="hidden" name="action" value="create">
        <input type="text" name="session_name" maxlength="20" placeholder="Nouvelle session" required
          class="w-full sm:w-auto flex-1 border border-gray-300 rounded-md px-3 py-2 mb-2 sm:mb-0 focus:outline-none focus:ring-2 focus:ring-blue-400" />
          <button type="submit"
  class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs py-2 px-4 rounded-md shadow transition transform hover:scale-90">
  + Ajouter
</button>
      </form>
    </div>
  </header>

  <main class="container mx-auto flex-1 px-4 py-6">
    <?php if (!empty($sessions)): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
  <?php foreach ($sessions as $s): ?>
    <div class="bg-white rounded-lg shadow hover:shadow-lg transition flex flex-col">
      
      <div class="bg-blue-600 text-white px-4 py-3 rounded-t-lg flex items-center relative">
        <span class="font-semibold truncate mx-auto"><?= htmlspecialchars($s->name) ?></span>
        
        <a href="sessions.php?delete=<?= $s->id ?>" onclick="return confirm('Supprimer cette session ?')"
           class="absolute right-4 text-red-200 hover:text-red-400">
          🗑️
        </a>
      </div>
      
      <div class="p-4 flex-grow flex items-center justify-center">
        <a href="session_details.php?session_id=<?= $s->id ?>" class="text-blue-600 hover:underline">
          Détails
        </a>
      </div>
      
    </div>
  <?php endforeach; ?>
</div>
    <?php else: ?>
      <p class="text-center text-gray-500">Aucune session trouvée pour <span
          class="font-medium"><?= htmlspecialchars($userName) ?></span>.</p>
    <?php endif; ?>
  </main>

</body>

</html>