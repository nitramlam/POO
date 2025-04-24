<?php
// public/sessions.php : liste des sessions stylisée avec Tailwind

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Tailwind.php';

// 1) Détermination de l'utilisateur
if (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $userId = (int)$_GET['user_id'];
    $_SESSION['user_id'] = $userId;
} elseif (!empty($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
} else {
    header('Location: /index.php'); exit;
}

// 2) Récupération du nom de l'utilisateur
$stmt = $conn->prepare("SELECT name FROM users WHERE id = :uid");
$stmt->execute(['uid' => $userId]);
$userName = $stmt->fetchColumn() ?: 'Utilisateur';

// 3) Supprimer une session
if (!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM sessions WHERE id = :id AND user_id = :uid");
    $stmt->execute(['id' => $delId, 'uid' => $userId]);
    header('Location: /sessions.php'); exit;
}

// 4) Créer une session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $name = trim($_POST['session_name'] ?? '');
    if ($name !== '') {
        $name = mb_substr($name, 0, 20);
        $stmt = $conn->prepare("INSERT INTO sessions (user_id, name) VALUES (:uid, :name)");
        $stmt->execute(['uid' => $userId, 'name' => $name]);
    }
    header('Location: /sessions.php'); exit;
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
    header('Location: /sessions.php'); exit;
}

// 6) Récupération des sessions
$sessions = Session::fetchByUser($conn, $userId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes Sessions</title>
  <?= Tailwind::includeCdn() ?>
</head>
<body class="bg-gray-50 min-h-screen">



  <main class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center space-x-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <h2 class="text-2xl font-bold text-gray-800">Mes Sessions</h2>
      </div>
      <form method="post" action="/sessions.php" class="flex space-x-2">
        <input type="hidden" name="action" value="create">
        <input name="session_name" maxlength="20" placeholder="Nouvelle session" required
               class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
          + Nouvelle session
        </button>
      </form>
    </div>

    <?php if (!empty($sessions)): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($sessions as $s): ?>
          <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
            <div class="bg-blue-600 text-white px-4 py-3 rounded-t-lg flex items-center space-x-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 3v12M18 3v12M3 7h18M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z" />
              </svg>
              <span class="font-semibold"><?= htmlspecialchars($s->name) ?></span>
            </div>
            <div class="p-4 flex justify-between items-center">
              <a href="session_details.php?session_id=<?= $s->id ?>"
                 class="text-blue-600 hover:underline font-medium">
                Voir les exercices →
              </a>
              <a href="sessions.php?delete=<?= $s->id ?>"
                 onclick="return confirm('Supprimer cette session ?')"
                 class="text-red-600 hover:text-red-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22" />
                </svg>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-gray-500">Aucune session trouvée.</p>
    <?php endif; ?>

  </main>

</body>
</html>