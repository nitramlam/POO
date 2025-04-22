<?php
// classes/Session.php

require_once __DIR__ . '/Database.php';

class Session {
    public int $id;
    public int $user_id;
    public string $name;

    public function __construct(int $id, int $user_id, string $name) {
        $this->id      = $id;
        $this->user_id = $user_id;
        $this->name    = $name;
    }

    public static function fetchByUser(PDO $conn, int $userId): array {
        $sql  = "SELECT id, user_id, name FROM sessions WHERE user_id = :uid";
        $stmt = $conn->prepare($sql);
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

    public static function create(PDO $conn, int $userId, string $name): bool {
        $stmt = $conn->prepare(
            "INSERT INTO sessions (user_id, name) VALUES (:uid, :name)"
        );
        return $stmt->execute(['uid' => $userId, 'name' => $name]);
    }
}