<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../classes/ExerciseSession.php';

// 1) Récupération de l'ID de session
if (empty($_GET['session_id']) || !is_numeric($_GET['session_id'])) {
    header('Location: /sessions.php');
    exit;
}
$sessionId = (int) $_GET['session_id'];

// 2) Récupération du nom de la session
$stmt = $conn->prepare("SELECT name FROM sessions WHERE id = :sid");
$stmt->execute(['sid' => $sessionId]);
$sessionName = $stmt->fetchColumn() ?: 'Inconnue';

// 3) Récupération des exercices liés à la session
$exercises = ExerciseSession::fetchBySession($conn, $sessionId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Exercices de la session « <?= htmlspecialchars($sessionName) ?> »</title>
</head>
<body>
    <h1>Exercices de la session « <?= htmlspecialchars($sessionName) ?> »</h1>
    <?php if (!empty($exercises)): ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>ID Exo</th>
                    <th>Poids</th>
                    <th>Répétitions</th>
                    <th>Séries</th>
                    <th>Objectif Poids</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exercises as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e->exercise_id) ?></td>
                    <td><?= htmlspecialchars($e->weight   ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($e->repetitions ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($e->sets     ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($e->target_weight ?? 'N/A') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun exercice trouvé pour cette session.</p>
    <?php endif; ?>

    <p><a href="/sessions.php">← Retour aux sessions</a></p>
    <p><a href="/index.php">← Retour à la sélection d’utilisateur</a></p>
</body>
</html>