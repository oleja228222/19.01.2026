<?php
require_once 'config.php';
$pageTitle = 'Главная';

// Параметры фильтрации
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'date';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Построение запроса
$where = ["a.status = 'published'"];
$params = [];

if ($category) {
    $where[] = "c.slug = ?";
    $params[] = $category;
}

if ($search) {
    $where[] = "(a.title LIKE ? OR a.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);

// Сортировка
$orderBy = $sort === 'popular'
    ? 'likes_count DESC, a.created_at DESC'
    : 'a.created_at DESC';

// Подсчёт
$countSql = "SELECT COUNT(*) FROM articles a JOIN categories c ON a.category_id = c.id WHERE $whereClause";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalArticles = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalArticles / $perPage));

// Выборка статей
$sql = "SELECT a.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon,
               u.username, u.avatar as author_avatar,
               (SELECT COUNT(*) FROM likes WHERE article_id = a.id) as likes_count,
               (SELECT COUNT(*) FROM comments WHERE article_id = a.id) as comments_count
        FROM articles a
        JOIN categories c ON a.category_id = c.id
        JOIN users u ON a.user_id = u.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Категории для фильтра
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

require 'includes/header.php';
?>

<!-- Hero-секция -->
<section class="hero">
    <div class="container">
        <h1 class="hero-title">Будь в курсе IT-трендов</h1>
        <p class="hero-subtitle">Читай статьи, поддерживай авторов, открывай премиум-контент</p>
        <form class="hero-search" action="" method="GET">
            <input type="text" name="search" placeholder="Поиск статей..." value="<?= e($search) ?>" class="hero-search-input">
            <button type="submit" class="btn btn-primary">Найти</button>
        </form>
    </div>
</section>

<div class="container">
    <!-- Фильтры -->
    <section class="filters">
        <div class="filter-categories">
            <a href="<?= SITE_URL ?>" class="filter-chip <?= !$category ? 'active' : '' ?>">Все</a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= SITE_URL ?>/?category=<?= $cat['slug'] ?><?= $search ? '&search=' . urlencode($search) : '' ?>&sort=<?= $sort ?>"
                   class="filter-chip <?= $category === $cat['slug'] ? 'active' : '' ?>">
                    <?= $cat['icon'] ?> <?= e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="filter-sort">
            <span class="filter-label">Сортировка:</span>
            <a href="<?= SITE_URL ?>/?<?= $category ? 'category=' . $category . '&' : '' ?><?= $search ? 'search=' . urlencode($search) . '&' : '' ?>sort=date"
               class="sort-btn <?= $sort === 'date' ? 'active' : '' ?>">По дате</a>
            <a href="<?= SITE_URL ?>/?<?= $category ? 'category=' . $category . '&' : '' ?><?= $search ? 'search=' . urlencode($search) . '&' : '' ?>sort=popular"
               class="sort-btn <?= $sort === 'popular' ? 'active' : '' ?>">По популярности</a>
        </div>
    </section>

    <!-- Статьи -->
    <?php if (empty($articles)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>Статей пока нет</h3>
            <p>Попробуйте изменить фильтры или поисковый запрос</p>
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
                        <a href="<?= SITE_URL ?>/?category=<?= $article['category_slug'] ?>" class="article-card-category">
                            <?= $article['category_icon'] ?> <?= e($article['category_name']) ?>
                        </a>
                        <h2 class="article-card-title">
                            <a href="<?= SITE_URL ?>/article.php?slug=<?= $article['slug'] ?>">
                                <?= e($article['title']) ?>
                            </a>
                        </h2>
                        <p class="article-card-excerpt">
                            <?= e(mb_substr(strip_tags($article['content']), 0, 120)) ?>...
                        </p>
                        <div class="article-card-footer">
                            <a href="<?= SITE_URL ?>/profile.php?id=<?= $article['user_id'] ?>" class="article-card-author">
                                <img src="<?= avatarUrl($article['author_avatar']) ?>" alt="" class="avatar-xs">
                                <span><?= e($article['username']) ?></span>
                            </a>
                            <div class="article-card-stats">
                                <span>❤️ <?= $article['likes_count'] ?></span>
                                <span>💬 <?= $article['comments_count'] ?></span>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Пагинация -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= SITE_URL ?>/?<?= $category ? 'category=' . $category . '&' : '' ?><?= $search ? 'search=' . urlencode($search) . '&' : '' ?>sort=<?= $sort ?>&page=<?= $i ?>"
                       class="pagination-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>