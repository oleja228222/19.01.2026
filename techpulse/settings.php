<?php
require_once 'config.php';
$pageTitle = 'Настройки профиля';

if (!$currentUser) redirect(SITE_URL . '/login.php');

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $username = trim($_POST['username'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $socialVk = trim($_POST['social_vk'] ?? '');
        $socialTg = trim($_POST['social_tg'] ?? '');
        $socialGithub = trim($_POST['social_github'] ?? '');

        // Валидация имени
        if (strlen($username) < 3) {
            $errors[] = 'Имя пользователя должно содержать от 3 символов';
        } else {
            // Проверка уникальности
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $currentUser['id']]);
            if ($stmt->fetch()) {
                $errors[] = 'Такое имя пользователя уже занято';
            }
        }

        // Загрузка аватара
        $avatarName = $currentUser['avatar'];
        if (!empty($_FILES['avatar']['name'])) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $file = $_FILES['avatar'];

            if (!in_array($file['type'], $allowed)) {
                $errors[] = 'Допустимые форматы: JPG, PNG, WebP, GIF';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $errors[] = 'Максимальный размер аватара: 2 МБ';
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $avatarName = 'avatar_' . $currentUser['id'] . '_' . time() . '.' . $ext;
                $dest = UPLOAD_DIR . 'avatars/' . $avatarName;

                if (!is_dir(UPLOAD_DIR . 'avatars/')) {
                    mkdir(UPLOAD_DIR . 'avatars/', 0777, true);
                }

                // Удаление старого аватара
                if ($currentUser['avatar'] && file_exists(UPLOAD_DIR . 'avatars/' . $currentUser['avatar'])) {
                    unlink(UPLOAD_DIR . 'avatars/' . $currentUser['avatar']);
                }

                move_uploaded_file($file['tmp_name'], $dest);
            }
        }

        if (empty($errors)) {
            $stmt = $db->prepare(
                "UPDATE users SET username = ?, bio = ?, avatar = ?, social_vk = ?, social_tg = ?, social_github = ? WHERE id = ?"
            );
            $stmt->execute([$username, $bio, $avatarName, $socialVk, $socialTg, $socialGithub, $currentUser['id']]);

            $_SESSION['username'] = $username;
            $_SESSION['avatar'] = $avatarName;

            setFlash('success', 'Профиль обновлён');
            redirect(SITE_URL . '/settings.php');
        }
    }

    if ($action === 'password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPass, $currentUser['password'])) {
            $errors[] = 'Текущий пароль неверен';
        } elseif (strlen($newPass) < 6) {
            $errors[] = 'Новый пароль — минимум 6 символов';
        } elseif ($newPass !== $confirmPass) {
            $errors[] = 'Пароли не совпадают';
        } else {
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $currentUser['id']]);

            setFlash('success', 'Пароль изменён');
            redirect(SITE_URL . '/settings.php');
        }
    }
}

// Обновим данные пользователя
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$currentUser['id']]);
$currentUser = $stmt->fetch();

require 'includes/header.php';
?>

<div class="container">
    <div class="settings-wrapper">
        <h1 class="page-title">Настройки профиля</h1>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <p><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Основная информация -->
        <div class="settings-card">
            <h2 class="settings-card-title">Основная информация</h2>
            <form method="POST" enctype="multipart/form-data" class="settings-form">
                <input type="hidden" name="action" value="profile">

                <div class="form-group">
                    <label class="form-label">Аватар</label>
                    <div class="avatar-upload">
                        <img src="<?= avatarUrl($currentUser['avatar']) ?>" alt="" class="avatar-preview" id="avatarPreview">
                        <div class="avatar-upload-actions">
                            <label class="btn btn-secondary btn-sm">
                                Выбрать фото
                                <input type="file" name="avatar" accept="image/*" hidden id="avatarInput">
                            </label>
                            <span class="text-muted text-sm">JPG, PNG, WebP. Макс. 2 МБ</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="username">Имя пользователя</label>
                    <input type="text" id="username" name="username" class="form-input"
                           value="<?= e($currentUser['username']) ?>" required minlength="3">
                </div>

                <div class="form-group">
                    <label class="form-label" for="bio">О себе</label>
                    <textarea id="bio" name="bio" class="form-textarea" rows="4"
                              placeholder="Расскажите о себе..."><?= e($currentUser['bio'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="social_vk">VK</label>
                    <input type="url" id="social_vk" name="social_vk" class="form-input"
                           placeholder="https://vk.com/username" value="<?= e($currentUser['social_vk'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="social_tg">Telegram</label>
                    <input type="url" id="social_tg" name="social_tg" class="form-input"
                           placeholder="https://t.me/username" value="<?= e($currentUser['social_tg'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="social_github">GitHub</label>
                    <input type="url" id="social_github" name="social_github" class="form-input"
                           placeholder="https://github.com/username" value="<?= e($currentUser['social_github'] ?? '') ?>">
                </div>

                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>

        <!-- Смена пароля -->
        <div class="settings-card">
            <h2 class="settings-card-title">Смена пароля</h2>
            <form method="POST" class="settings-form">
                <input type="hidden" name="action" value="password">

                <div class="form-group">
                    <label class="form-label" for="current_password">Текущий пароль</label>
                    <input type="password" id="current_password" name="current_password" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password">Новый пароль</label>
                    <input type="password" id="new_password" name="new_password" class="form-input" required minlength="6">
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Повторите пароль</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                </div>

                <button type="submit" class="btn btn-primary">Изменить пароль</button>
            </form>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>