<?php

// Gère la déconnexion de l'utilisateur et redirige vers la page d'accueil

// Démarrage de la session pour accéder aux variables de session
session_start();

// Suppression de toutes les variables de session en mémoire
session_unset();

// Destruction complète de la session côté serveur
session_destroy();

// Redirection vers la page de connexion ou d'accueil
header('Location: /index.php');
exit;
