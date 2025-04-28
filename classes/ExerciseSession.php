<?php
require_once __DIR__ . '/Database.php';

class ExerciseSession {
    public int $id;
    public int $exercise_id;
    public int $session_id;
    public ?float $weight;
    public ?int $repetitions;
    public ?int $sets;
    public ?float $target_weight;

    public function __construct(
        int $id,
        int $exercise_id,
        int $session_id,
        ?float $weight,
        ?int $repetitions,
        ?int $sets,
        ?float $target_weight
    ) {
        $this->id            = $id;
        $this->exercise_id   = $exercise_id;
        $this->session_id    = $session_id;
        $this->weight        = $weight;
        $this->repetitions   = $repetitions;
        $this->sets          = $sets;
        $this->target_weight = $target_weight;
    }

    // fetch entries
    public static function fetchBySession(PDO $conn, int $sessionId): array {
        $stmt = $conn->prepare(
            'SELECT id, exercise_id, session_id, weight, repetitions, sets, target_weight
             FROM exercises_sessions WHERE session_id = :sid'
        );
        $stmt->execute(['sid' => $sessionId]);
        $list = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new ExerciseSession(
                (int)$row['id'],
                (int)$row['exercise_id'],
                (int)$row['session_id'],
                $row['weight'] !== null ? (float)$row['weight'] : null,
                $row['repetitions'] !== null ? (int)$row['repetitions'] : null,
                $row['sets'] !== null ? (int)$row['sets'] : null,
                $row['target_weight'] !== null ? (float)$row['target_weight'] : null
            );
        }
        return $list;
    }

    // add entry
    public static function addToSession(PDO $conn, int $sessionId, int $exerciseId, float $weight = 0, int $repetitions = 0, int $sets = 0, float $targetWeight = 0): bool {
        $stmt = $conn->prepare(
            'INSERT INTO exercises_sessions (session_id, exercise_id, weight, repetitions, sets, target_weight)
             VALUES (:sid, :eid, :w, :r, :s, :tw)'
        );
        return $stmt->execute([
            'sid' => $sessionId,
            'eid' => $exerciseId,
            'w'   => $weight,
            'r'   => $repetitions,
            's'   => $sets,
            'tw'  => $targetWeight
        ]);
    }

    // update entry
    public static function updateEntry(PDO $conn, int $id, float $weight, int $repetitions, int $sets, float $targetWeight): bool {
        $stmt = $conn->prepare(
            'UPDATE exercises_sessions
             SET weight = :w, repetitions = :r, sets = :s, target_weight = :tw
             WHERE id = :id'
        );
        return $stmt->execute([
            'w'   => $weight,
            'r'   => $repetitions,
            's'   => $sets,
            'tw'  => $targetWeight,
            'id'  => $id
        ]);
    }

    // delete entry
    public static function remove(PDO $conn, int $id): bool {
        $stmt = $conn->prepare('DELETE FROM exercises_sessions WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    // handle create/update/delete
    public static function handleEntryActions(PDO $conn, int $sessionId, array $request): void {
        if (!empty($request['delete']) && is_numeric($request['delete'])) {
            self::remove($conn, (int)$request['delete']);
            header("Location: /session_details.php?session_id={$sessionId}"); exit;
        }
        if (($request['action'] ?? '') === 'create') {
            $exercise = Exercise::findOrCreate($conn, trim($request['exercise_name'] ?? ''));
            self::addToSession(
                $conn, $sessionId, $exercise->id,
                (float)($request['weight'] ?? 0), (int)($request['repetitions'] ?? 0),
                (int)($request['sets'] ?? 0), (float)($request['target_weight'] ?? 0)
            );
            header("Location: /session_details.php?session_id={$sessionId}"); exit;
        }
        if (($request['action'] ?? '') === 'update' && !empty($request['entry_id'])) {
            self::updateEntry(
                $conn, (int)$request['entry_id'],
                (float)($request['weight'] ?? 0), (int)($request['repetitions'] ?? 0),
                (int)($request['sets'] ?? 0), (float)($request['target_weight'] ?? 0)
            );
            header("Location: /session_details.php?session_id={$sessionId}"); exit;
        }
    }

    // fetch entries with exercise names
    public static function fetchBySessionWithNames(PDO $conn, int $sessionId): array {
        $stmt = $conn->prepare(
            'SELECT es.id, e.name AS exercise_name, es.weight, es.repetitions, es.sets, es.target_weight
             FROM exercises_sessions es
             JOIN exercises e ON es.exercise_id = e.id
             WHERE es.session_id = :sid
             ORDER BY e.name'
        );
        $stmt->execute(['sid' => $sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
