<?php
// public/admin_users.php : gestion des utilisateurs responsive avec Tailwind

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Tailwind.php';

// CRUD
if (!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
    User::delete($conn, (int)$_GET['delete']);
    header('Location: /admin_users.php#user-' . (int)$_GET['delete']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            User::create($conn, $name);
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id > 0 && $name !== '') {
            User::update($conn, $id, $name);
        }
    }
    $anchorId = $_POST['action'] === 'update' ? $_POST['id'] : '';
    header('Location: /admin_users.php#user-' . $anchorId);
    exit;
}

// Récupération des utilisateurs
$users = User::fetchAll($conn);

// Mode édition
$editId = null;
if (!empty($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>👥 Gestion des utilisateurs</title>
  <?= Tailwind::includeCdn() ?>
</head>
<body class="bg-gray-50 min-h-screen">

<main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <!-- Formulaire d'ajout -->
  <div class="bg-white shadow rounded-lg p-6 mb-8">
    <form method="post" action="/admin_users.php" class="flex flex-col sm:flex-row sm:space-x-4 space-y-4 sm:space-y-0">
      <input type="hidden" name="action" value="create">
      <input name="name" required maxlength="20" placeholder="Nouvel utilisateur"
             class="w-full sm:flex-1 border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-300" />
      <button type="submit"
              class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-md transition flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Ajouter
      </button>
    </form>
  </div>

  <!-- Liste des utilisateurs -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($users as $u): ?>
      <div id="user-<?= $u->id ?>" class="bg-white border border-gray-200 rounded-lg p-4 space-y-4 shadow hover:shadow-lg transition">
        <?php if ($editId === $u->id): ?>
          <form method="post" action="/admin_users.php" class="space-y-3">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $u->id ?>">
            <input name="name" value="<?= htmlspecialchars($u->name) ?>" required maxlength="20"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-300" />
            <div class="flex justify-end space-x-2">
              <button type="submit"
                      class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-md transition flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </button>
              <a href="/admin_users.php#user-<?= $u->id ?>"
                 class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-md transition flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </a>
            </div>
          </form>
        <?php else: ?>
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
            <span class="font-medium text-gray-800 truncate"><?= htmlspecialchars($u->name) ?></span>
            <div class="flex space-x-2">
              <a href="/admin_users.php?edit=<?= $u->id ?>#user-<?= $u->id ?>"
                 class="bg-green-400 hover:bg-green-500 text-white p-2 rounded-md transition flex items-center justify-center"
                 title="Modifier">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a1.5 1.5 0 012.121 2.122L7 21H3v-4L16.732 3.732z" />
                </svg>
              </a>
              <a href="/admin_users.php?delete=<?= $u->id ?>#user-<?= $u->id ?>"
                 onclick="return confirm('Supprimer <?= htmlspecialchars($u->name) ?> ?')"
                 class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-md transition flex items-center justify-center"
                 title="Supprimer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

</main>
</body>
</html>