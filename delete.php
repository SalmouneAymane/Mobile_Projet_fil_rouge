<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$courseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$courseId) {
    header('Location: index.php');
    exit;
}

$statement = $pdo->prepare('DELETE FROM courses WHERE id = :id');
$statement->execute(['id' => $courseId]);

header('Location: index.php?message=deleted');
exit;
