<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!$currentUser) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$commentId = (int)($_POST['comment_id'] ?? 0);

// Удаляем только свой комментарий
$stmt = $db->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
$stmt->execute([$commentId, $currentUser['id']]);

echo json_encode(['success' => $stmt->rowCount() > 0]);