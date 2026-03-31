<?php
require_once 'config.php';

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) redirect(SITE_URL);

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$author = $stmt->fetch();

if (!$author) {
    setFlash('error', 'Пользователь не найден');
    redirect(SITE_URL);
}

$pageTitle = $author['username'];

// Статьи автора
$stmt = $db->prepare(
    "SELECT a.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon,
            (SELECT COUNT(*) FROM likes WHERE article_id = a.id) as likes_count,
            (SELECT COUNT(*) FROM comments WHERE article_id = a.id) as comments_count
     FROM articles a
     JOIN categories c ON a.category_id = c.id
     WHERE a.user_id = ? AND a.status = 'published'
     ORDER BY a.created_at DESC"
);
$stmt->execute([$userId]);
$articles = $stmt->fetchAll();

// Статистика
$totalLikes = $db->prepare("SELECT COUNT(*) FROM likes l JOIN articles a ON l.article_id = a.id WHERE a.user_id = ?");
$totalLikes->execute([$userId]);
$likesCount = $totalLikes->fetchColumn();

$supportersCount = $db->prepare("SELECT COUNT(DISTINCT supporter_id) FROM supports WHERE author_id = ? AND status = 'paid'");
$supportersCount->execute([$userId]);
$supporters = $supportersCount->fetchColumn();

// Проверка поддержки
$isSupported = $currentUser ? hasSupported($currentUser['id'], $userId) : false;
$isOwner = $currentUser && $currentUser['id'] === $userId;

require 'includes/header.php';
?>

<div class="container">
    <section class="profile-header">
        <div class="profile-avatar-wrap">
            <img src="<?= avatarUrl($author['avatar']) ?>" alt="" class="profile-avatar">
        </div>
        <div class="profile-info">
            <h1 class="profile-name"><?= e($author['username']) ?></h1>
            <?php if ($author['bio']): ?>
                <p class="profile-bio"><?= nl2br(e($author['bio'])) ?></p>
            <?php endif; ?>
            <div class="profile-stats">
                <div class="profile-stat">
                    <strong><?= count($articles) ?></strong>
                    <span>Статей</span>
                </div>
                <div class="profile-stat">
                    <strong><?= $likesCount ?></strong>
                    <span>Лайков</span>
                </div>
                <div class="profile-stat">
                    <strong><?= $supporters ?></strong>
                    <span>Подписчиков</span>
                </div>
            </div>
            <div class="profile-socials">
                <?php if ($author['social_vk']): ?>
                    <a href="<?= e($author['social_vk']) ?>" target="_blank" class="social-link">VK</a>
                <?php endif; ?>
                <?php if ($author['social_tg']): ?>
                    <a href="<?= e($author['social_tg']) ?>" target="_blank" class="social-link">Telegram</a>
                <?php endif; ?>
                <?php if ($author['social_github']): ?>
                    <a href="<?= e($author['social_github']) ?>" target="_blank" class="social-link">GitHub</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="profile-actions">
            <?php if ($isOwner): ?>
                <a href="<?= SITE_URL ?>/settings.php" class="btn btn-secondary">Настройки</a>
            <?php elseif ($currentUser && !$isSupported): ?>
                <a href="<?= SITE_URL ?>/support.php?author_id=<?= $userId ?>" class="btn btn-primary">⭐ Поддержать</a>
            <?php elseif ($isSupported): ?>
                <span class="btn btn-success-outline">✓ Вы поддерживаете</span>
            <?php endif; ?>
        </div>
    </section>

    <section class="profile-articles">
        <h2 class="section-title">Статьи автора</h2>
        <?php if (empty($articles)): ?>
            <div class="empty-state">
                <div class="empty-icon"></div>
                <h3>Статей пока нет</h3>
                <p>Автор ещё не опубликовал ни одной статьи</p>
            </div>
        <?php else: ?>
            <div class="articles-grid">
                <?php foreach ($articles as $article): ?>
                    <article class="article-card">
                        <a href="<?= SITE_URL ?>/article.php?slug=<?= $article['slug'] ?>" class="article-card-cover">
                            <?php if ($article['cover']): ?>
                                <img src="<?= coverUrl($article['cover']) ?>" alt="">
                            <?php else: ?>
                                <div class="article-card-cover-placeholder">
                                    <span><?= $article['category_icon'] ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($article['is_premium']): ?>
                                <span class="badge badge-premium">Премиум</span>
                            <?php endif; ?>
                        </a>
                        <div class="article-card-body">
                            <span class="article-card-category"><?= $article['category_icon'] ?> <?= e($article['category_name']) ?></span>
                            <h2 class="article-card-title">
                                <a href="<?= SITE_URL ?>/article.php?slug=<?= $article['slug'] ?>"><?= e($article['title']) ?></a>
                            </h2>
                            <div class="article-card-footer">
                                <span class="text-muted"><?= formatDate($article['created_at']) ?></span>
                                <div class="article-card-stats">
                                    <span>❤️ <?= $article['likes_count'] ?></span>
                                    <span>💬 <?= $article['comments_count'] ?></span>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require 'includes/footer.php'; ?>