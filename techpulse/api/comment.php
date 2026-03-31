<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!$currentUser) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$articleId = (int)($_POST['article_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if (!$articleId || empty($content)) {
    echo json_encode(['success' => false, 'error' => 'Заполните текст комментария']);
    exit;
}

if (mb_strlen($content) > 2000) {
    echo json_encode(['success' => false, 'error' => 'Максимум 2000 символов']);
    exit;
}

$stmt = $db->prepare("INSERT INTO comments (user_id, article_id, content) VALUES (?, ?, ?)");
$stmt->execute([$currentUser['id'], $articleId, $content]);

$commentId = $db->lastInsertId();

echo json_encode([
    'success' => true,
    'comment' => [
        'id' => (int)$commentId,
        'content' => $content,
        'username' => $currentUser['username'],
        'avatar' => avatarUrl($currentUser['avatar']),
        'user_id' => $currentUser['id'],
        'date' => formatDate(date('Y-m-d H:i:s'))
    ]
]);