<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!$currentUser) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$articleId = (int)($_POST['article_id'] ?? 0);

if (!$articleId) {
    echo json_encode(['success' => false, 'error' => 'Не указана статья']);
    exit;
}

// Проверяем, лайкнул ли уже
$stmt = $db->prepare("SELECT id FROM likes WHERE user_id = ? AND article_id = ?");
$stmt->execute([$currentUser['id'], $articleId]);
$existing = $stmt->fetch();

if ($existing) {
    // Убираем лайк
    $db->prepare("DELETE FROM likes WHERE id = ?")->execute([$existing['id']]);
    $liked = false;
} else {
    // Ставим лайк
    $db->prepare("INSERT INTO likes (user_id, article_id) VALUES (?, ?)")
       ->execute([$currentUser['id'], $articleId]);
    $liked = true;
}

// Новый счётчик
$stmt = $db->prepare("SELECT COUNT(*) FROM likes WHERE article_id = ?");
$stmt->execute([$articleId]);
$count = $stmt->fetchColumn();

echo json_encode(['success' => true, 'liked' => $liked, 'count' => (int)$count]);