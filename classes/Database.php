<?php
class Database {
    private string $host = 'mysql-muscu33.alwaysdata.net'; // <- hôte AlwaysData
    private string $db_name = 'muscu33_1';                 // <- nom de la base
    private string $username = 'muscu33';                  // <- utilisateur AlwaysData
    private string $password = 'musculation33';            // <- mot de passe
    private ?PDO $conn = null;

    public function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public function getConnection(): PDO {
        return $this->conn;
    }
}