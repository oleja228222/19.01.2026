<?php
require_once 'config.php';
$pageTitle = 'Восстановление пароля';

if ($currentUser) redirect(SITE_URL);

$errors = [];
$codeSent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $result = $auth->requestPasswordReset(trim($_POST['email'] ?? ''));

    if ($result['success']) {
        $_SESSION['reset_email'] = trim($_POST['email']);
        setFlash('success', $result['message']);
        redirect(SITE_URL . '/reset_password.php');
    } else {
        $errors[] = $result['error'];
    }
}

require 'includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1 class="auth-title">Восстановление пароля</h1>
            <p class="auth-subtitle">Введите email для получения кода</p>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $err): ?>
                        <p><?= e($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input"
                           placeholder="user@example.com" required>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Отправить код</button>
            </form>

            <p class="auth-footer">
                <a href="<?= SITE_URL ?>/login.php">← Вернуться к входу</a>
            </p>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>