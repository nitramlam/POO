<?php

// Gère l'authentification basique via paramètre GET et démarre la session

// Démarre la session pour stocker l'identifiant de l'utilisateur
session_start();

// Vérifie la présence et la validité du paramètre user_id
if (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    // Stocke l'ID utilisateur et l'heure de connexion en session
    $_SESSION['user_id']    = (int)$_GET['user_id'];
    $_SESSION['login_time'] = time();

    // Redirige vers la page des sessions après connexion
    header('Location: /sessions.php');
    exit;
}

// Si le paramètre est manquant ou invalide, redirige vers l'accueil
header('Location: /index.php');
exit;
