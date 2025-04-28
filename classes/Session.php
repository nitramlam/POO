<?php
// classes/Session.php

require_once __DIR__ . '/Database.php';

class Session {
    public int $id;
    public int $user_id;
    public string $name;

    public function __construct(int $id, int $user_id, string $name) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->name = $name;
    }

    // 1) Récupère toutes les sessions
    public static function fetchByUser(PDO $conn, int $userId): array {
        $stmt = $conn->prepare("SELECT id, user_id, name FROM sessions WHERE user_id = :uid");
        $stmt->execute(['uid' => $userId]);
        $sessions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sessions[] = new Session(
                (int)$row['id'],
                (int)$row['user_id'],
                $row['name']
            );
        }
        return $sessions;
    }

    // 2) Crée une session
    public static function create(PDO $conn, int $userId, string $name): bool {
        $stmt = $conn->prepare("INSERT INTO sessions (user_id, name) VALUES (:uid, :name)");
        return $stmt->execute(['uid' => $userId, 'name' => $name]);
    }

    // 3) Supprime une session
    public static function delete(PDO $conn, int $sessionId, int $userId): bool {
        $stmt = $conn->prepare("DELETE FROM sessions WHERE id = :id AND user_id = :uid");
        return $stmt->execute(['id' => $sessionId, 'uid' => $userId]);
    }

    // 4) Met à jour une session
    public static function update(PDO $conn, int $sessionId, int $userId, string $name): bool {
        $stmt = $conn->prepare("UPDATE sessions SET name = :name WHERE id = :id AND user_id = :uid");
        return $stmt->execute(['name' => $name, 'id' => $sessionId, 'uid' => $userId]);
    }

    // 5) Détermine l'utilisateur connecté
    public static function getCurrentUserId(): ?int {
        if (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
            $_SESSION['user_id'] = (int)$_GET['user_id'];
        }
        return $_SESSION['user_id'] ?? null;
    }

    // 6) Récupère son nom
    public static function getCurrentUserName(PDO $conn, int $userId): string {
        $stmt = $conn->prepare("SELECT name FROM users WHERE id = :uid");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchColumn() ?: 'Utilisateur';
    }

    // 7) Gère toutes les actions CRUD d'une session
    public static function handleActions(PDO $conn, int $userId): void {
        if (!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
            self::delete($conn, (int)$_GET['delete'], $userId);
            header('Location: /sessions.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $name = trim($_POST['session_name'] ?? '');

            if ($action === 'create' && $name !== '') {
                self::create($conn, $userId, mb_substr($name, 0, 20));
            } elseif ($action === 'update') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0 && $name !== '') {
                    self::update($conn, $id, $userId, mb_substr($name, 0, 20));
                }
            }
            header('Location: /sessions.php');
            exit;
        }
    }
}