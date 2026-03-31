<?php
// create_article.php - создание новой статьи
require_once 'config.php';

// Только авторизованные пользователи могут создавать статьи
if (!$currentUser) {
    redirect(SITE_URL . '/login.php');
}

$pageTitle = 'Создание статьи';
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors = [];

// Функция для генерации уникального slug
function generateSlug($title, $db) {
    // Транслитерация русских букв
    $translit = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    ];
    $slug = strtr(mb_strtolower($title), $translit);
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug); // замена всего лишнего на -
    $slug = preg_replace('/-+/', '-', $slug); // убираем множественные дефисы
    $slug = trim($slug, '-');
    
    if (empty($slug)) $slug = 'post';
    
    // Проверка уникальности
    $originalSlug = $slug;
    $counter = 1;
    while (true) {
        $stmt = $db->prepare("SELECT id FROM articles WHERE slug = ?");
        $stmt->execute([$slug]);
        if (!$stmt->fetch()) break;
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }
    return $slug;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    $isPremium = isset($_POST['is_premium']) ? 1 : 0;

    // Валидация
    if (empty($title)) $errors[] = 'Введите заголовок';
    if (!$categoryId) $errors[] = 'Выберите категорию';
    if (empty($content)) $errors[] = 'Напишите текст статьи';

    // Обработка обложки
    $coverName = null;
    if (!empty($_FILES['cover']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $file = $_FILES['cover'];

        if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $coverName = 'cover_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            move_uploaded_file($file['tmp_name'], UPLOAD_DIR . 'covers/' . $coverName);
        } else {
            $errors[] = 'Обложка должна быть JPEG, PNG или WEBP размером до 5 МБ';
        }
    }

    if (empty($errors)) {
        $slug = generateSlug($title, $db);
        
        $stmt = $db->prepare(
            "INSERT INTO articles (user_id, category_id, title, slug, content, cover, is_premium, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'published', NOW())"
        );
        $stmt->execute([
            $currentUser['id'],
            $categoryId,
            $title,
            $slug,
            $content,
            $coverName,
            $isPremium
        ]);
        
        $articleId = $db->lastInsertId();
        setFlash('success', 'Статья успешно создана');
        redirect(SITE_URL . '/article.php?slug=' . $slug);
    }
}

require 'includes/header.php';
?>

<div class="container">
    <div class="editor-wrapper">
        <h1 class="page-title">Создание новой статьи</h1>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <p><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="editor-form">
            <div class="form-group">
                <label class="form-label" for="title">Заголовок</label>
                <input type="text" id="title" name="title" class="form-input form-input-lg"
                       value="<?= e($_POST['title'] ?? '') ?>" required>
            </div>

            <div class="form-row-2col">
                <div class="form-group">
                    <label class="form-label" for="category_id">Категория</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">-- Выберите категорию --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? 0) == $cat['id']) ? 'selected' : '' ?>>
                                <?= $cat['icon'] ?> <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Обложка (опционально)</label>
                    <label class="file-upload">
                        <input type="file" name="cover" accept="image/*">
                        <span class="file-upload-label"> Выбрать изображение</span>
                    </label>
                    <small class="form-hint">JPEG, PNG, WEBP до 5 МБ</small>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="content">Текст статьи</label>
                <textarea id="content" name="content" class="form-textarea form-textarea-lg" rows="20" required><?= e($_POST['content'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-checkbox premium-checkbox">
                    <input type="checkbox" name="is_premium" value="1" <?= isset($_POST['is_premium']) ? 'checked' : '' ?>>
                    <span>Приватная статья (доступна только поддержавшим автора)</span>
                </label>
                <small class="form-hint">Публичная статья будет доступна всем пользователям</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">Опубликовать</button>
                <a href="<?= SITE_URL ?>" class="btn btn-secondary btn-lg">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php require 'includes/footer.php'; ?>