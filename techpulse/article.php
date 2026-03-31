<?php
require_once 'config.php';

$slug = $_GET['slug'] ?? '';
$stmt = $db->prepare(
    "SELECT a.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon,
            u.id as author_id, u.username, u.avatar as author_avatar, u.bio as author_bio
     FROM articles a
     JOIN categories c ON a.category_id = c.id
     JOIN users u ON a.user_id = u.id
     WHERE a.slug = ?"
);
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    setFlash('error', 'Статья не найдена');
    redirect(SITE_URL);
}

$pageTitle = $article['title'];

// Увеличение просмотров
$db->prepare("UPDATE articles SET views = views + 1 WHERE id = ?")->execute([$article['id']]);

// Проверка доступа к премиум-контенту
$hasAccess = true;
$isOwner = $currentUser && $currentUser['id'] === $article['author_id'];
if ($article['is_premium'] && !$isOwner) {
    if (!$currentUser) {
        $hasAccess = false;
    } else {
        $hasAccess = hasSupported($currentUser['id'], $article['author_id']);
    }
}

// Количество лайков
$likesStmt = $db->prepare("SELECT COUNT(*) FROM likes WHERE article_id = ?");
$likesStmt->execute([$article['id']]);
$likesCount = $likesStmt->fetchColumn();

// Пользователь лайкнул?
$userLiked = false;
if ($currentUser) {
    $likeCheck = $db->prepare("SELECT id FROM likes WHERE user_id = ? AND article_id = ?");
    $likeCheck->execute([$currentUser['id'], $article['id']]);
    $userLiked = (bool)$likeCheck->fetch();
}

// Комментарии
$commentsStmt = $db->prepare(
    "SELECT cm.*, u.username, u.avatar
     FROM comments cm
     JOIN users u ON cm.user_id = u.id
     WHERE cm.article_id = ?
     ORDER BY cm.created_at DESC"
);
$commentsStmt->execute([$article['id']]);
$comments = $commentsStmt->fetchAll();

require 'includes/header.php';
?>

<article class="article-page">
    <!-- Шапка статьи -->
    <header class="article-header">
        <div class="container container-narrow">
            <a href="<?= SITE_URL ?>/?category=<?= $article['category_slug'] ?>" class="article-category-link">
                <?= $article['category_icon'] ?> <?= e($article['category_name']) ?>
            </a>
            <h1 class="article-title"><?= e($article['title']) ?></h1>
            <div class="article-meta">
                <a href="<?= SITE_URL ?>/profile.php?id=<?= $article['author_id'] ?>" class="article-author-link">
                    <img src="<?= avatarUrl($article['author_avatar']) ?>" alt="" class="avatar-sm">
                    <span><?= e($article['username']) ?></span>
                </a>
                <span class="article-date"><?= formatDate($article['created_at']) ?></span>
                <span class="article-views">👁 <?= $article['views'] ?></span>
                <?php if ($article['is_premium']): ?>
                    <span class="badge badge-premium">Премиум</span>
                <?php endif; ?>
            </div>
            <?php if ($isOwner): ?>
                <div class="article-owner-actions">
                    <a href="<?= SITE_URL ?>/edit_article.php?id=<?= $article['id'] ?>" class="btn btn-secondary btn-sm">✏️ Редактировать</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Обложка -->
    <?php if ($article['cover']): ?>
        <div class="article-cover">
            <div class="container container-narrow">
                <img src="<?= coverUrl($article['cover']) ?>" alt="" class="article-cover-img">
            </div>
        </div>
    <?php endif; ?>

    <!-- Контент -->
    <div class="container container-narrow">
        <?php if ($hasAccess): ?>
            <div class="article-content">
                <?= nl2br(e($article['content'])) ?>
            </div>

            <!-- Лайки и действия -->
            <div class="article-actions">
                <button class="like-btn <?= $userLiked ? 'liked' : '' ?>"
                        id="likeBtn"
                        data-article-id="<?= $article['id'] ?>"
                        <?= !$currentUser ? 'disabled title="Войдите, чтобы лайкнуть"' : '' ?>>
                    <span class="like-icon"><?= $userLiked ? '❤️' : '🤍' ?></span>
                    <span class="like-count" id="likeCount"><?= $likesCount ?></span>
                </button>

                <?php if ($currentUser && !$isOwner): ?>
                    <button class="btn btn-secondary btn-sm" onclick="openComplaintModal()">
                        🚩 Пожаловаться
                    </button>
                <?php endif; ?>
            </div>

            <!-- Блок автора -->
            <div class="article-author-card">
                <img src="<?= avatarUrl($article['author_avatar']) ?>" alt="" class="avatar-lg">
                <div class="article-author-info">
                    <h3><?= e($article['username']) ?></h3>
                    <?php if ($article['author_bio']): ?>
                        <p><?= e(mb_substr($article['author_bio'], 0, 150)) ?></p>
                    <?php endif; ?>
                    <div class="article-author-actions">
                        <a href="<?= SITE_URL ?>/profile.php?id=<?= $article['author_id'] ?>" class="btn btn-secondary btn-sm">Профиль</a>
                        <?php if ($currentUser && !$isOwner && !hasSupported($currentUser['id'], $article['author_id'])): ?>
                            <a href="<?= SITE_URL ?>/support.php?author_id=<?= $article['author_id'] ?>" class="btn btn-primary btn-sm">⭐ Поддержать</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Комментарии -->
            <section class="comments-section">
                <h2 class="comments-title">💬 Комментарии (<?= count($comments) ?>)</h2>

                <?php if ($currentUser): ?>
                    <form class="comment-form" id="commentForm" data-article-id="<?= $article['id'] ?>">
                        <div class="comment-form-inner">
                            <img src="<?= avatarUrl($currentUser['avatar']) ?>" alt="" class="avatar-sm">
                            <textarea name="content" class="form-textarea" rows="3"
                                      placeholder="Напишите комментарий..." required id="commentText"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Отправить</button>
                    </form>
                <?php else: ?>
                    <div class="comment-login-prompt">
                        <p><a href="<?= SITE_URL ?>/login.php">Войдите</a>, чтобы оставить комментарий</p>
                    </div>
                <?php endif; ?>

                <div class="comments-list" id="commentsList">
                    <?php if (empty($comments)): ?>
                        <p class="text-muted text-center" id="noComments">Пока нет комментариев. Будьте первым!</p>
                    <?php endif; ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment" id="comment-<?= $comment['id'] ?>">
                            <div class="comment-header">
                                <a href="<?= SITE_URL ?>/profile.php?id=<?= $comment['user_id'] ?>" class="comment-author">
                                    <img src="<?= avatarUrl($comment['avatar']) ?>" alt="" class="avatar-xs">
                                    <strong><?= e($comment['username']) ?></strong>
                                </a>
                                <span class="comment-date"><?= formatDate($comment['created_at']) ?></span>
                            </div>
                            <p class="comment-text"><?= nl2br(e($comment['content'])) ?></p>
                            <?php if ($currentUser && $currentUser['id'] === $comment['user_id']): ?>
                                <button class="comment-delete" onclick="deleteComment(<?= $comment['id'] ?>)">Удалить</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        <?php else: ?>
            <!-- Премиум-заглушка -->
            <div class="premium-wall">
                <div class="premium-wall-icon">🔒</div>
                <h2>Это премиум-материал</h2>
                <p>Поддержите автора, чтобы получить постоянный доступ ко всем его приватным статьям</p>
                <?php if ($currentUser): ?>
                    <a href="<?= SITE_URL ?>/support.php?author_id=<?= $article['author_id'] ?>" class="btn btn-primary btn-lg">
                        ⭐ Поддержать автора
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/login.php" class="btn btn-primary btn-lg">Войдите для доступа</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</article>

<!-- Модальное окно жалобы -->
<?php if ($currentUser && !$isOwner): ?>
<div class="modal-overlay" id="complaintModal">
    <div class="modal">
        <div class="modal-header">
            <h3>🚩 Пожаловаться на статью</h3>
            <button class="modal-close" onclick="closeComplaintModal()">✕</button>
        </div>
        <form id="complaintForm" data-article-id="<?= $article['id'] ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Причина</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="reason" value="Спам" required> Спам
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="reason" value="Оскорбления"> Оскорбления
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="reason" value="Недостоверная информация"> Недостоверная информация
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="reason" value="Нарушение правил"> Нарушение правил
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="reason" value="Другое"> Другое
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Описание (необязательно)</label>
                    <textarea name="description" class="form-textarea" rows="3"
                              placeholder="Опишите проблему подробнее..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeComplaintModal()">Отмена</button>
                <button type="submit" class="btn btn-danger">Отправить жалобу</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>