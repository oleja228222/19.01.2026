<?php
require_once 'config.php';
$pageTitle = 'Новый пароль';

if ($currentUser) redirect(SITE_URL);
if (!isset($_SESSION['reset_email'])) redirect(SITE_URL . '/forgot_password.php');

$email = $_SESSION['reset_email'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $result = $auth->resetPassword(
        $email,
        trim($_POST['code'] ?? ''),
        $_POST['password'] ?? ''
    );

    if ($result['success']) {
        unset($_SESSION['reset_email']);
        setFlash('success', $result['message']);
        redirect(SITE_URL . '/login.php');
    } else {
        $errors[] = $result['error'];
    }
}

require 'includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1 class="auth-title">Введите код</h1>
            <p class="auth-subtitle">Код отправлен на <?= e($email) ?></p>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $err): ?>
                        <p><?= e($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label class="form-label" for="code">Код подтверждения</label>
                    <input type="text" id="code" name="code" class="form-input form-input-code"
                           placeholder="000000" maxlength="6" required pattern="\d{6}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Новый пароль</label>
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="Минимум 6 символов" required minlength="6">
                </div>

                <button type="submit" class="btn btn-primary btn-full">Сменить пароль</button>
            </form>

            <p class="auth-footer">
                <a href="<?= SITE_URL ?>/forgot_password.php">Отправить код повторно</a>
            </p>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>