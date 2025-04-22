<?php
ob_start();
session_start();

// Connexion PDO
require_once __DIR__ . '/../classes/Database.php';
$db   = new Database();
$conn = $db->getConnection();

// Menu de navigation
require_once __DIR__ . '/header.php';