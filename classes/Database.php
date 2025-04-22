<?php
class Database {
    private string $host = 'db';
    private string $db_name = 'musculation_db';
    private string $username = 'muscu_user';
    private string $password = 'muscu_pass';
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