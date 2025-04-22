<?php
require_once __DIR__ . '/Database.php';

class User {
    public int $id;
    public string $name;

    public function __construct(int $id, string $name) {
        $this->id   = $id;
        $this->name = $name;
    }

    // Lecture : tous les users
    public static function fetchAll(PDO $conn): array {
        $stmt = $conn->query("SELECT id, name FROM users");
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User((int)$row['id'], $row['name']);
        }
        return $users;
    }

    // Création : ajoute un user, retourne son ID ou false
    public static function create(PDO $conn, string $name) {
        $stmt = $conn->prepare("INSERT INTO users (name) VALUES (:name)");
        if ($stmt->execute(['name' => $name])) {
            return (int)$conn->lastInsertId();
        }
        return false;
    }

    // Mise à jour : renvoie true/false
    public static function update(PDO $conn, int $id, string $name): bool {
        $stmt = $conn->prepare("UPDATE users SET name = :name WHERE id = :id");
        return $stmt->execute(['name' => $name, 'id' => $id]);
    }

    // Suppression : renvoie true/false
    public static function delete(PDO $conn, int $id): bool {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}