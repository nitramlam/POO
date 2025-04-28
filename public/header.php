<?php
// public/header.php : menu de navigation responsive avec Tailwind et menu burger partiel (40%)

// 1) Injecte Tailwind CDN
require_once __DIR__ . '/../classes/Tailwind.php';
echo Tailwind::includeCdn();

// 2) Récupère l'utilisateur connecté s'il existe
$userName = $_SESSION['user_name'] ?? 'Utilisateur'; // à ajuster selon ta variable de session exacte
date_default_timezone_set('Europe/Paris'); // pour être à l'heure française
$currentHour = date('H:i');
?>
<body class="bg-gray-100 min-h-screen">

<nav class="bg-blue-600 shadow-lg w-full pb-4">
  <div class="container mx-auto px-4">
    <div class="flex items-center justify-between h-16">
      
      <!-- Nom Utilisateur + Heure -->
      <div class="text-white font-bold text-base">
        👤 <?= htmlspecialchars($userName) ?> — 🕒 <?= $currentHour ?>
      </div>

      <!-- Liens Desktop -->
      <div class="hidden md:flex space-x-6">
        <a href="admin_users.php" class="text-white hover:text-blue-200 transition px-2 py-1">👥 Utilisateurs</a>
        <a href="admin_exercises.php" class="text-white hover:text-blue-200 transition px-2 py-1">🏋️ Exercices</a>
        <a href="sessions.php" class="text-white hover:text-blue-200 transition px-2 py-1">💪 Sessions</a>
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="logout.php" class="text-white hover:text-blue-200 transition px-2 py-1">🚪 Déconnexion</a>
        <?php endif; ?>
      </div>

      <!-- Bouton Mobile -->
      <div class="md:hidden">
        <button id="menu-btn" class="text-white focus:outline-none px-2 py-1">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>

    </div>

    <!-- Mobile Side Menu (40%) -->
    <div id="mobile-menu" class="fixed inset-y-0 left-0 h-full w-2/5 bg-blue-600 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 shadow-lg px-6 py-6">
      <div class="border-b border-blue-500 pb-4 mb-4 flex items-center justify-between">
        <span class="text-lg font-semibold text-white">Menu</span>
        <button id="menu-close" class="text-white focus:outline-none">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="space-y-3">
        <a href="admin_users.php" class="block text-white py-2 px-2 hover:bg-blue-500 rounded">Utilisateurs</a>
        <a href="admin_exercises.php" class="block text-white py-2 px-2 hover:bg-blue-500 rounded">Exercices</a>
        <a href="sessions.php" class="block text-white py-2 px-2 hover:bg-blue-500 rounded">Sessions</a>
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="logout.php" class="block text-white py-2 px-2 hover:bg-blue-500 rounded">Déconnexion</a>
        
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<hr class="border-gray-200 mt-4" />

<script>
  const btn = document.getElementById('menu-btn');
  const menu = document.getElementById('mobile-menu');
  const closeBtn = document.getElementById('menu-close');
  btn.addEventListener('click', () => {
    menu.classList.toggle('-translate-x-full');
  });
  closeBtn.addEventListener('click', () => {
    menu.classList.add('-translate-x-full');
  });
</script>