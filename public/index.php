<?php

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Tailwind.php';

// 1) Connexion à la base de données via PDO
$db    = new Database();
$conn  = $db->getConnection();

// 2) Récupération de la liste des utilisateurs pour le formulaire de connexion
$users = User::fetchAll($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion</title>
  <?= Tailwind::includeCdn() ?>
</head>
<body class="bg-gradient-to-br from-blue-100 to-blue-50 min-h-screen flex items-center justify-center p-6">

  <!-- Conteneur principal centré -->
  <div class="max-w-sm w-full bg-white rounded-2xl shadow-xl overflow-hidden">
    <!-- En-tête de la carte de connexion -->
    <div class="bg-blue-600 p-6 text-center">
      <div class="flex justify-center mb-4">
        <!-- Icône utilisateur -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A8 8 0 0112 15a8 8 0 016.879 2.804M12 11a4 4 0 100-8 4 4 0 000 8z" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold text-white">Connexion</h2>
      <p class="text-blue-200 mt-1">Sélectionnez votre profil</p>
    </div>

    <!-- Formulaire de connexion : méthode GET vers login.php -->
    <form method="get" action="login.php" class="p-6 space-y-6">
      <!-- Sélecteur de profil utilisateur -->
      <div>
        <label for="user_id" class="block text-gray-700 font-medium mb-2">Utilisateur</label>
        <select id="user_id" name="user_id" required
                class="w-full border border-gray-300 rounded-md p-3 focus:outline-none focus:ring-2 focus:ring-blue-300">
          <option value="" disabled selected>-- Choisir un profil --</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= htmlspecialchars($u->id) ?>"><?= htmlspecialchars($u->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Bouton de soumission du formulaire -->
      <div>
        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-md shadow-md transition duration-200">
          <span class="inline-flex items-center justify-center">
            <!-- Icône de flèche de connexion -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7l7 7-7 7" />
            </svg>
            Se connecter
          </span>
        </button>
      </div>
    </form>
  </div>

</body>
</html>
