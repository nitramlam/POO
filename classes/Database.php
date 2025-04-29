<?php

class Database {
    private string $host = 'mysql-muscu33.alwaysdata.net';
    private string $db_name = 'muscu33_1';
    private string $username = 'muscu33';
    private string $password = 'musculation33';
    private ?PDO $conn = null;

    // Initialise la connexion à la base de données
    public function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    // Retourne l'objet PDO
    public function getConnection(): PDO {
        return $this->conn;
    }
}