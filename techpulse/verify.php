<?php
require_once 'config.php';
$pageTitle = 'Подтверждение email';

$token = $_GET['token'] ?? '';
$success = false;

if ($token) {
    $auth = new Auth();
    $success = $auth->verify($token);
}

require 'includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper">
        <div class="auth-card text-center">
            <?php if ($success): ?>
                <div class="status-icon status-success">✓</div>
                <h1 class="auth-title">Email подтверждён!</h1>
                <p class="auth-subtitle">Теперь вы можете войти в аккаунт</p>
                <a href="<?= SITE_URL ?>/login.php" class="btn btn-primary">Войти</a>
            <?php else: ?>
                <div class="status-icon status-error">✕</div>
                <h1 class="auth-title">Ошибка подтверждения</h1>
                <p class="auth-subtitle">Ссылка недействительна или уже использована</p>
                <a href="<?= SITE_URL ?>" class="btn btn-secondary">На главную</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>