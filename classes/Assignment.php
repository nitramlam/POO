<?php
require_once __DIR__ . '/Database.php';

class Assignment {
    /**
     * Récupère les affectations groupées par utilisateur
     * @return array<string, array<array{id:int, session_name:string, exercise_name:string}>>
     */
    public static function fetchGroupedByUser(PDO $conn): array {
        $sql = <<<SQL
SELECT
    u.name         AS user_name,
    s.name         AS session_name,
    e.name         AS exercise_name,
    es.id          AS assignment_id
FROM exercises_sessions es
JOIN sessions s   ON es.session_id   = s.id
JOIN users u      ON s.user_id        = u.id
JOIN exercises e  ON es.exercise_id   = e.id
ORDER BY u.name, s.name, e.name
SQL;
        $stmt = $conn->query($sql);
        $groups = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = $row['user_name'];
            if (!isset($groups[$user])) {
                $groups[$user] = [];
            }
            $groups[$user][] = [
                'id'            => (int)$row['assignment_id'],
                'session_name'  => $row['session_name'],
                'exercise_name' => $row['exercise_name'],
            ];
        }
        return $groups;
    }
}
