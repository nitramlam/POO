<?php
// classes/ExerciseSession.php

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

    /**
     * Récupère toutes les entrées pour une session donnée
     * @return ExerciseSession[]
     */
    public static function fetchBySession(PDO $conn, int $sessionId): array {
        $sql = "
            SELECT id, exercise_id, session_id, weight, repetitions, sets, target_weight
            FROM exercises_sessions
            WHERE session_id = :sid
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['sid' => $sessionId]);
        $list = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new ExerciseSession(
                (int)$row['id'],
                (int)$row['exercise_id'],
                (int)$row['session_id'],
                $row['weight']        !== null ? (float)$row['weight']        : null,
                $row['repetitions']   !== null ? (int)$row['repetitions']     : null,
                $row['sets']          !== null ? (int)$row['sets']            : null,
                $row['target_weight'] !== null ? (float)$row['target_weight'] : null
            );
        }
        return $list;
    }

    /**
     * Ajoute un exercice à une session
     */
    public static function addToSession(
        PDO $conn,
        int $sessionId,
        int $exerciseId,
        ?float $weight = 0,
        ?int $repetitions = 0,
        ?int $sets = 0,
        ?float $targetWeight = 0
    ): bool {
        $sql = "
            INSERT INTO exercises_sessions
            (session_id, exercise_id, weight, repetitions, sets, target_weight)
            VALUES (:sid, :eid, :w, :r, :s, :tw)
        ";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            'sid' => $sessionId,
            'eid' => $exerciseId,
            'w'   => $weight,
            'r'   => $repetitions,
            's'   => $sets,
            'tw'  => $targetWeight
        ]);
    }

    /**
     * Met à jour une entrée existante
     */
    public static function updateEntry(
        PDO $conn,
        int $id,
        float $weight,
        int $repetitions,
        int $sets,
        float $targetWeight
    ): bool {
        $sql = "
            UPDATE exercises_sessions
            SET weight        = :weight,
                repetitions   = :reps,
                sets          = :sets,
                target_weight = :tw
            WHERE id = :id
        ";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            'weight' => $weight,
            'reps'   => $repetitions,
            'sets'   => $sets,
            'tw'     => $targetWeight,
            'id'     => $id
        ]);
    }

    /**
     * Supprime une entrée existante
     */
    public static function remove(PDO $conn, int $id): bool {
        $stmt = $conn->prepare("DELETE FROM exercises_sessions WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}