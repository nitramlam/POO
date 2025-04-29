<?php
ob_start();

// Démarrage de la session utilisateur
session_start();

// 1) Expiration automatique après 2 heures d'inactivité
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 2 * 3600) {
    // Nettoyage complet de la session et redirection vers la page d'accueil 
    session_unset();
    session_destroy();
    header('Location: /index.php?expired=1');
    exit;
}

// 2) Protection des pages : seules ces pages sont accessibles sans authentification
$publicPages = ['index.php'];
$currentFile = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (!in_array($currentFile, $publicPages) && empty($_SESSION['user_id'])) {
    // Redirection vers l'accueil si l'utilisateur n'est pas connecté
    header('Location: /index.php');
    exit;
}

// 3) Connexion à la base de données via PDO
require_once __DIR__ . '/../classes/Database.php';
$db   = new Database();
$conn = $db->getConnection();

// Si l'utilisateur est connecté, récupération de son nom pour l'affichage
if (!empty($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = :uid");
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $_SESSION['user_name'] = $stmt->fetchColumn() ?: '';
}

// 4) Inclusion de l'en-tête commun à toutes les pages protégées
require_once __DIR__ . '/header.php';
