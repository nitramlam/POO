<?php
require_once __DIR__ . '/Database.php';

class Exercise {
    public int $id;
    public string $name;

    public function __construct(int $id, string $name) {
        $this->id = $id;
        $this->name = $name;
    }

    // Retourne un exercice par son nom
    public static function fetchByName(PDO $conn, string $name): ?Exercise {
        $stmt = $conn->prepare("SELECT id, name FROM exercises WHERE name = :name");
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Exercise((int)$row['id'], $row['name']);
        }
        return null;
    }

    // Crée un nouvel exercice
    public static function create(PDO $conn, string $name): Exercise {
        $stmt = $conn->prepare("INSERT INTO exercises (name) VALUES (:name)");
        $stmt->execute(['name' => $name]);
        return new Exercise((int)$conn->lastInsertId(), $name);
    }

    // Retourne un exercice existant ou le crée
    public static function findOrCreate(PDO $conn, string $name): Exercise {
        $exercise = self::fetchByName($conn, $name);
        if ($exercise) {
            return $exercise;
        }
        return self::create($conn, $name);
    }

    // Associe un exercice à une session
    public static function assignToSession(
        PDO $conn,
        int $exerciseId,
        int $sessionId,
        float $weight = 0,
        int $reps = 0,
        int $sets = 0,
        float $targetWeight = 0
    ): bool {
        $stmt = $conn->prepare(
            "INSERT INTO exercises_sessions
             (session_id, exercise_id, weight, repetitions, sets, target_weight)
             VALUES (:sid, :eid, :w, :r, :s, :tw)"
        );
        return $stmt->execute([
            'sid' => $sessionId,
            'eid' => $exerciseId,
            'w' => $weight,
            'r' => $reps,
            's' => $sets,
            'tw' => $targetWeight
        ]);
    }

    // Supprime une association exercice-session
    public static function unassignFromSession(PDO $conn, int $assignmentId): bool {
        $stmt = $conn->prepare("DELETE FROM exercises_sessions WHERE id = :id");
        return $stmt->execute(['id' => $assignmentId]);
    }

    // Retourne toutes les affectations exercices/sessions par utilisateur
    public static function fetchAllAssignments(PDO $conn): array {
        $stmt = $conn->query(
            "SELECT es.id,
                    u.name AS user_name,
                    s.name AS session_name,
                    e.name AS exercise_name
             FROM exercises_sessions es
             JOIN exercises e ON es.exercise_id = e.id
             JOIN sessions s ON es.session_id = s.id
             JOIN users u ON s.user_id = u.id
             ORDER BY u.name, s.name, e.name"
        );
        $assignments = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $assignments[$row['user_name']][] = [
                'id' => (int)$row['id'],
                'session_name' => $row['session_name'],
                'exercise_name' => $row['exercise_name']
            ];
        }
        return $assignments;
    }

    // Crée un exercice et l'assigne à plusieurs sessions
    public static function createAndAssign(PDO $conn, array $data): void {
        $name = trim($data['exercise_name'] ?? '');
        $ids = array_map('intval', $data['session_ids'] ?? []);
        $weight = (float)($data['weight'] ?? 0);
        $reps = (int)($data['repetitions'] ?? 0);
        $sets = (int)($data['sets'] ?? 0);
        $target = (float)($data['target_weight'] ?? 0);

        if ($name === '' || empty($ids)) {
            return;
        }

        $exercise = self::findOrCreate($conn, $name);
        foreach ($ids as $sid) {
            self::assignToSession($conn, $exercise->id, $sid, $weight, $reps, $sets, $target);
        }
    }
}