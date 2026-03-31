<?php
require_once 'config.php';
$pageTitle = 'Вход';

if ($currentUser) redirect(SITE_URL);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $result = $auth->login(
        trim($_POST['email'] ?? ''),
        $_POST['password'] ?? '',
        isset($_POST['remember'])
    );

    if ($result['success']) {
        setFlash('success', 'Добро пожаловать!');
        redirect(SITE_URL);
    } else {
        $errors[] = $result['error'];
    }
}

require 'includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1 class="auth-title">Вход в аккаунт</h1>
            <p class="auth-subtitle">Рады видеть вас снова!</p>

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
                           placeholder="user@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Пароль</label>
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="Ваш пароль" required>
                </div>

                <div class="form-row">
                    <label class="form-checkbox">
                        <input type="checkbox" name="remember" value="1">
                        <span>Запомнить меня</span>
                    </label>
                    <a href="<?= SITE_URL ?>/forgot_password.php" class="form-link">Забыли пароль?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Войти</button>
            </form>

            <p class="auth-footer">
                Нет аккаунта? <a href="<?= SITE_URL ?>/register.php">Зарегистрироваться</a>
            </p>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>