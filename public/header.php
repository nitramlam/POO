<?php
// Affiche l’utilisateur connecté
if (!empty($_SESSION['user_name'])): ?>
  <div style="background:#f5f5f5;padding:5px 10px;text-align:right;font-size:0.9em;">
    Connecté en tant que <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
  </div>
<?php endif; ?>

<nav style="background:#eee;padding:10px;">
  <?php if (isset($_SESSION['user_id'])): ?>
    <a href="logout.php"           style="margin-right:15px;">🚪 Déconnexion</a>
  <?php else: ?>
    <a href="index.php"            style="margin-right:15px;">🏠 Accueil</a>
  <?php endif; ?>

  <a href="admin_users.php"       style="margin-right:15px;">👥 Gérer les utilisateurs</a>
  <a href="admin_exercises.php"   style="margin-right:15px;">🏋️ Gérer les exercices</a>
  <a href="sessions.php"          style="margin-right:15px;">💪 Mes sessions</a>
</nav>
<hr>