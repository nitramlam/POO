<?php
require_once __DIR__ . '/Database.php';

class User {
    public int $id;
    public string $name;

    public function __construct(int $id, string $name) {
        $this->id = $id;
        $this->name = $name;
    }

    // Récupère tous les utilisateurs
    public static function fetchAll(PDO $conn): array {
        $stmt = $conn->query('SELECT id, name FROM users');
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User((int)$row['id'], $row['name']);
        }
        return $users;
    }

    // Crée un nouvel utilisateur et retourne son ID
    public static function create(PDO $conn, string $name) {
        $stmt = $conn->prepare('INSERT INTO users (name) VALUES (:name)');
        if ($stmt->execute(['name' => $name])) {
            return (int)$conn->lastInsertId();
        }
        return false;
    }

    // Met à jour un utilisateur existant
    public static function update(PDO $conn, int $id, string $name): bool {
        $stmt = $conn->prepare('UPDATE users SET name = :name WHERE id = :id');
        return $stmt->execute(['name' => $name, 'id' => $id]);
    }

    // Supprime un utilisateur
    public static function delete(PDO $conn, int $id): bool {
        $stmt = $conn->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    // Gère automatiquement les actions CRUD via les requêtes GET et POST
    public static function handleActions(PDO $conn): void {
        // Suppression
        if (!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
            self::delete($conn, (int)$_GET['delete']);
            header('Location: /admin_users.php#user-' . (int)$_GET['delete']);
            exit;
        }

        // Création ou mise à jour
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $name = trim($_POST['name'] ?? '');

            if ($action === 'create' && $name !== '') {
                self::create($conn, $name);
            } elseif ($action === 'update') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0 && $name !== '') {
                    self::update($conn, $id, $name);
                }
            }

            $anchor = $action === 'update' ? (int)$_POST['id'] : '';
            header('Location: /admin_users.php#user-' . $anchor);
            exit;
        }
    }

    // Retourne l'ID de l'utilisateur en mode édition, s'il existe
    public static function getEditId(): ?int {
        if (!empty($_GET['edit']) && is_numeric($_GET['edit'])) {
            return (int)$_GET['edit'];
        }
        return null;
    }
}