<?php
require_once 'config.php';
$pageTitle = 'Регистрация';

if ($currentUser) redirect(SITE_URL);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $result = $auth->register(
        trim($_POST['username'] ?? ''),
        trim($_POST['email'] ?? ''),
        $_POST['password'] ?? ''
    );

    if ($result['success']) {
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
            <h1 class="auth-title">Создать аккаунт</h1>
            <p class="auth-subtitle">Присоединяйтесь к сообществу TechPulse</p>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $err): ?>
                        <p><?= e($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label class="form-label" for="username">Имя пользователя</label>
                    <input type="text" id="username" name="username" class="form-input"
                           placeholder="tech_user" value="<?= e($_POST['username'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input"
                           placeholder="user@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Пароль</label>
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="Минимум 6 символов" required minlength="6">
                </div>

                <button type="submit" class="btn btn-primary btn-full">Зарегистрироваться</button>
            </form>

            <p class="auth-footer">
                Уже есть аккаунт? <a href="<?= SITE_URL ?>/login.php">Войти</a>
            </p>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>