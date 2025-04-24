<?php
ob_start();
session_start();

// 1) Expiration au bout de 2h
if (isset($_SESSION['login_time']) && time() - $_SESSION['login_time'] > 2*3600) {
    session_unset();
    session_destroy();
    header('Location: /index.php?expired=1');
    exit;
}

// 2) Protection : seules index.php, login.php et logout.php sont publiques
$public = ['index.php','login.php','logout.php'];
$file   = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (!in_array($file, $public) && empty($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

// 3) Connexion PDO + récupération nom
require_once __DIR__ . '/../classes/Database.php';
$db   = new Database();
$conn = $db->getConnection();

if (!empty($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = :uid");
    $stmt->execute(['uid'=>$_SESSION['user_id']]);
    $_SESSION['user_name'] = $stmt->fetchColumn() ?: '';
}

// 4) Header commun
require_once __DIR__ . '/header.php';