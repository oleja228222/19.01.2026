<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!$currentUser) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$articleId = (int)($_POST['article_id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');
$description = trim($_POST['description'] ?? '');

if (!$articleId || empty($reason)) {
    echo json_encode(['success' => false, 'error' => 'Укажите причину жалобы']);
    exit;
}

// Проверка: не жаловался ли уже
$stmt = $db->prepare("SELECT id FROM complaints WHERE user_id = ? AND article_id = ?");
$stmt->execute([$currentUser['id'], $articleId]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Вы уже отправляли жалобу на эту статью']);
    exit;
}

$stmt = $db->prepare("INSERT INTO complaints (user_id, article_id, reason, description) VALUES (?, ?, ?, ?)");
$stmt->execute([$currentUser['id'], $articleId, $reason, $description]);

echo json_encode(['success' => true, 'message' => 'Жалоба отправлена. Спасибо!']);