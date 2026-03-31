<?php
require_once 'config.php';

if (!$currentUser) redirect(SITE_URL . '/login.php');

$supportId = (int)($_GET['support_id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM supports WHERE id = ? AND supporter_id = ?");
$stmt->execute([$supportId, $currentUser['id']]);
$support = $stmt->fetch();

if (!$support) {
    setFlash('error', 'Платёж не найден');
    redirect(SITE_URL);
}

$paymentStatus = 'error';

// Проверяем статус платежа в ЮKassa
if ($support['payment_id']) {
    $ch = curl_init('https://api.yookassa.ru/v3/payments/' . $support['payment_id']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => YOKASSA_SHOP_ID . ':' . YOKASSA_SECRET_KEY,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['status'])) {
        if ($result['status'] === 'succeeded') {
            $paymentStatus = 'paid';
            $db->prepare("UPDATE supports SET status = 'paid', paid_at = NOW() WHERE id = ?")
               ->execute([$supportId]);
        } elseif ($result['status'] === 'canceled') {
            $paymentStatus = 'cancelled';
            $db->prepare("UPDATE supports SET status = 'cancelled' WHERE id = ?")
               ->execute([$supportId]);
        } elseif ($result['status'] === 'pending') {
            $paymentStatus = 'pending';
        }
    }
}

// Данные автора
$stmt = $db->prepare("SELECT id, username, avatar FROM users WHERE id = ?");
$stmt->execute([$support['author_id']]);
$author = $stmt->fetch();

$pageTitle = 'Результат оплаты';
require 'includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper">
        <div class="auth-card text-center">
            <?php if ($paymentStatus === 'paid'): ?>
                <div class="status-icon status-success">✓</div>
                <h1 class="auth-title">Оплата прошла успешно!</h1>
                <p class="auth-subtitle">
                    Вы поддержали автора <strong><?= e($author['username']) ?></strong>
                    на <?= number_format($support['amount'], 0, ',', ' ') ?> ₽
                </p>
                <p class="text-muted">Теперь вам доступны все премиум-статьи этого автора</p>
                <div class="mt-3">
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= $author['id'] ?>" class="btn btn-primary">Перейти к автору</a>
                    <a href="<?= SITE_URL ?>" class="btn btn-secondary">На главную</a>
                </div>
            <?php elseif ($paymentStatus === 'pending'): ?>
                <div class="status-icon status-warning">⏳</div>
                <h1 class="auth-title">Обработка платежа</h1>
                <p class="auth-subtitle">Платёж обрабатывается. Обновите страницу через минуту.</p>
                <a href="" class="btn btn-primary">Обновить</a>
            <?php else: ?>
                <div class="status-icon status-error">✕</div>
                <h1 class="auth-title">Ошибка оплаты</h1>
                <p class="auth-subtitle">Платёж был отменён или произошла ошибка</p>
                <div class="mt-3">
                    <a href="<?= SITE_URL ?>/support.php?author_id=<?= $author['id'] ?>" class="btn btn-primary">Попробовать снова</a>
                    <a href="<?= SITE_URL ?>" class="btn btn-secondary">На главную</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>