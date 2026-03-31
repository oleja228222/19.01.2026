<?php if (!defined('DB_HOST')) { require_once __DIR__ . '/../config.php'; } ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="site-url" content="<?= SITE_URL ?>">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>
</head>
<body>
    <!-- Toast-уведомления -->
    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
        <div class="toast toast-<?= $flash['type'] ?>" id="toast">
            <span><?= e($flash['message']) ?></span>
            <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
        </div>
    <?php endif; ?>

    <!-- Шапка -->
    <header class="header">
        <div class="container header-inner">
            <a href="<?= SITE_URL ?>" class="logo">
                <span class="logo-icon">⚡</span>
                <span class="logo-text"><?= SITE_NAME ?></span>
            </a>

            <nav class="nav">
                <a href="<?= SITE_URL ?>" class="nav-link">Главная</a>
                <?php if ($currentUser): ?>
                    <a href="<?= SITE_URL ?>/create_article.php" class="nav-link">Написать</a>
                    <a href="<?= SITE_URL ?>/my_supports.php" class="nav-link">Мои поддержки</a>
                    <div class="nav-user">
                        <button class="nav-user-btn" id="userMenuBtn">
                            <img src="<?= avatarUrl($currentUser['avatar']) ?>" alt="" class="nav-avatar">
                            <span><?= e($currentUser['username']) ?></span>
                            <span class="chevron">▾</span>
                        </button>
                        <div class="dropdown" id="userDropdown">
                            <a href="<?= SITE_URL ?>/profile.php?id=<?= $currentUser['id'] ?>" class="dropdown-item">Мой профиль</a>
                            <a href="<?= SITE_URL ?>/settings.php" class="dropdown-item">Настройки</a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= SITE_URL ?>/logout.php" class="dropdown-item dropdown-item-danger">Выйти</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/login.php" class="nav-link">Войти</a>
                    <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-sm">Регистрация</a>
                <?php endif; ?>
            </nav>

            <button class="burger" id="burgerBtn">☰</button>
        </div>
    </header>

    <main class="main">