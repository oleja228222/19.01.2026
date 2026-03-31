<?php
require_once 'config.php';

if (!$currentUser) redirect(SITE_URL . '/login.php');

$articleId = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM articles WHERE id = ? AND user_id = ?");
$stmt->execute([$articleId, $currentUser['id']]);
$article = $stmt->fetch();

if (!$article) {
    setFlash('error', 'Статья не найдена');
    redirect(SITE_URL);
}

$pageTitle = 'Редактирование: ' . $article['title'];
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    $isPremium = isset($_POST['is_premium']) ? 1 : 0;

    if (empty($title)) $errors[] = 'Введите заголовок';
    if (!$categoryId) $errors[] = 'Выберите категорию';
    if (empty($content)) $errors[] = 'Напишите текст статьи';

    $coverName = $article['cover'];
    if (!empty($_FILES['cover']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $file = $_FILES['cover'];

        if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $coverName = 'cover_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            move_uploaded_file($file['tmp_name'], UPLOAD_DIR . 'covers/' . $coverName);

            // Удаление старой обложки
            if ($article['cover'] && file_exists(UPLOAD_DIR . 'covers/' . $article['cover'])) {
                unlink(UPLOAD_DIR . 'covers/' . $article['cover']);
            }
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare(
            "UPDATE articles SET title = ?, category_id = ?, content = ?, cover = ?, is_premium = ? WHERE id = ?"
        );
        $stmt->execute([$title, $categoryId, $content, $coverName, $isPremium, $articleId]);

        setFlash('success', 'Статья обновлена');
        redirect(SITE_URL . '/article.php?slug=' . $article['slug']);
    }
}

require 'includes/header.php';
?>

<div class="container">
    <div class="editor-wrapper">
        <h1 class="page-title">✏️ Редактирование статьи</h1>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="editor-form">
            <div class="form-group">
                <label class="form-label" for="title">Заголовок</label>
                <input type="text" id="title" name="title" class="form-input form-input-lg"
                       value="<?= e($article['title']) ?>" required>
            </div>

            <div class="form-row-2col">
                <div class="form-group">
                    <label class="form-label" for="category_id">Категория</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $article['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= $cat['icon'] ?> <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Обложка</label>
                    <label class="file-upload">
                        <input type="file" name="cover" accept="image/*">
                        <span class="file-upload-label"><?= $article['cover'] ? '🔄 Заменить обложку' : '📷 Выбрать' ?></span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="content">Текст статьи</label>
                <textarea id="content" name="content" class="form-textarea form-textarea-lg" rows="20"><?= e($article['content']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-checkbox premium-checkbox">
                    <input type="checkbox" name="is_premium" value="1" <?= $article['is_premium'] ? 'checked' : '' ?>>
                    <span>Премиум-статья</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">Сохранить</button>
                <a href="<?= SITE_URL ?>/article.php?slug=<?= $article['slug'] ?>" class="btn btn-secondary btn-lg">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php require 'includes/footer.php'; ?>