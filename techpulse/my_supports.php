<?php
require_once 'config.php';
$pageTitle = 'Мои поддержки';

if (!$currentUser) redirect(SITE_URL . '/login.php');

$stmt = $db->prepare(
    "SELECT s.*, u.id as author_id, u.username, u.avatar
     FROM supports s
     JOIN users u ON s.author_id = u.id
     WHERE s.supporter_id = ? AND s.status = 'paid'
     ORDER BY s.paid_at DESC"
);
$stmt->execute([$currentUser['id']]);
$supports = $stmt->fetchAll();

require 'includes/header.php';
?>

<div class="container">
    <h1 class="page-title">Мои поддержки</h1>

    <?php if (empty($supports)): ?>
        <div class="empty-state">
            <div class="empty-icon">💝</div>
            <h3>Вы ещё никого не поддержали</h3>
            <p>Поддержите авторов, чтобы получить доступ к их премиум-контенту</p>
            <a href="<?= SITE_URL ?>" class="btn btn-primary">Найти авторов</a>
        </div>
    <?php else: ?>
        <div class="supports-grid">
            <?php foreach ($supports as $s): ?>
                <div class="support-item">
                    <img src="<?= avatarUrl($s['avatar']) ?>" alt="" class="avatar-md">
                    <div class="support-item-info">
                        <h3><?= e($s['username']) ?></h3>
                        <p class="text-muted">
                            Поддержка: <?= number_format($s['amount'], 0, ',', ' ') ?> ₽
                        </p>
                        <p class="text-muted text-sm">
                            <?= formatDate($s['paid_at']) ?>
                        </p>
                    </div>
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= $s['author_id'] ?>" class="btn btn-secondary btn-sm">
                        Перейти →
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>