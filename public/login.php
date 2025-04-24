<?php
// login.php
session_start();

if (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $_SESSION['user_id']    = (int)$_GET['user_id'];
    $_SESSION['login_time'] = time();
    header('Location: /sessions.php');
    exit;
}

// en cas de requête invalide, on retourne à l’accueil
header('Location: /index.php');
exit;