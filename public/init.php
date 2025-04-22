<?php
// Démarre le buffer pour empêcher l’envoi immédiat de la sortie
ob_start();

// 1) Connexion à la base
require_once __DIR__ . '/../classes/Database.php';
$db   = new Database();
$conn = $db->getConnection();

// 2) Menu de navigation commun
require_once __DIR__ . '/header.php';