<?php
// public/header.php : menu de navigation commun avec animations Tailwind

// 1) Injecte Tailwind CDN
require_once __DIR__ . '/../classes/Tailwind.php';
echo Tailwind::includeCdn();
?>
<body class="bg-gray-100 min-h-screen">

<nav class="bg-blue-600 shadow-lg">
  <div class="container mx-auto flex items-center justify-between p-4">
    <!-- Logo / Titre -->
    <a href="index.php" class="text-white font-bold text-xl transform hover:scale-105 transition duration-200">
      💪 MuscuApp
    </a>

    <!-- Liens de navigation -->
    <div class="flex space-x-6">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="logout.php" class="text-white px-3 py-2 rounded hover:bg-blue-700 transform hover:scale-110 transition duration-200">
          🚪 Déconnexion
        </a>
      <?php else: ?>
        <a href="index.php" class="text-white px-3 py-2 rounded hover:bg-blue-700 transform hover:scale-110 transition duration-200">
          🏠 Accueil
        </a>
      <?php endif; ?>

      <a href="admin_users.php" class="text-white px-3 py-2 rounded hover:bg-blue-700 transform hover:scale-110 transition duration-200">
        👥 Utilisateurs
      </a>
      <a href="admin_exercises.php" class="text-white px-3 py-2 rounded hover:bg-blue-700 transform hover:scale-110 transition duration-200">
        🏋️ Exercices
      </a>
      <a href="sessions.php" class="text-white px-3 py-2 rounded hover:bg-blue-700 transform hover:scale-110 transition duration-200">
        💪 Sessions
      </a>
    </div>

    <!-- Infos utilisateur + date -->
    <div class="flex items-center space-x-4">
      <?php if (!empty($_SESSION['user_name'])): ?>
        <span class="text-white text-sm">
          <?= htmlspecialchars($_SESSION['user_name']) ?>
        </span>
      <?php endif; ?>
      <span class="text-white text-sm">
        <?= date('d/m/Y H:i') ?>
      </span>
    </div>
  </div>
</nav>
<hr class="border-gray-200 mb-6" />
