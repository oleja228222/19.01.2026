<?php
session_start();

// Настройки БД
define('DB_HOST', 'MySQL-8.0');
define('DB_NAME', 'techpulse');
define('DB_USER', 'root');
define('DB_PASS', '');

// Настройки сайта
define('SITE_NAME', 'TechPulse');
define('SITE_URL', 'http://techpulse');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Настройки почты
define('MAIL_FROM', 'zarilgo2005@mail.ru');
define('MAIL_FROM_NAME', 'TechPulse');

// ЮKassa (тестовый магазин)
define('YOKASSA_SHOP_ID', '1224152');
define('YOKASSA_SECRET_KEY', 'test_TQmJq6_24Ty0Z46F7Y6kejxPCZ7VnhHrJ8l8i0VO508');

// Время жизни кода восстановления (минуты)
define('RESET_CODE_LIFETIME', 15);

// Время жизни cookie "Запомнить меня" (дни)
define('REMEMBER_DAYS', 30);

// Автозагрузка классов
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Подключение к БД
$db = Database::getInstance();

// Проверка cookie "Запомнить меня"
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $db->prepare("SELECT id, username, email, avatar FROM users WHERE remember_token = ? AND is_verified = 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['avatar'] = $user['avatar'];
    } else {
        setcookie('remember_token', '', time() - 3600, '/');
    }
}

// Текущий пользователь
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();
}

// Хелпер: редирект
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

// Хелпер: безопасный вывод
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Хелпер: flash-сообщения
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Хелпер: создание slug
function createSlug(string $text): string {
    $transliteration = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo',
        'ж'=>'zh','з'=>'z','и'=>'i','й'=>'j','к'=>'k','л'=>'l','м'=>'m',
        'н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u',
        'ф'=>'f','х'=>'kh','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'shch',
        'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
        'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'Yo',
        'Ж'=>'Zh','З'=>'Z','И'=>'I','Й'=>'J','К'=>'K','Л'=>'L','М'=>'M',
        'Н'=>'N','О'=>'O','П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U',
        'Ф'=>'F','Х'=>'Kh','Ц'=>'Ts','Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Shch',
        'Ъ'=>'','Ы'=>'Y','Ь'=>'','Э'=>'E','Ю'=>'Yu','Я'=>'Ya'
    ];
    $text = strtr($text, $transliteration);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'article-' . time();
}

// Хелпер: аватар URL
function avatarUrl(?string $avatar): string {
    if ($avatar && file_exists(UPLOAD_DIR . 'avatars/' . $avatar)) {
        return SITE_URL . '/uploads/avatars/' . $avatar;
    }
    return SITE_URL . '/assets/img/default-avatar.svg';
}

// Хелпер: обложка URL
function coverUrl(?string $cover): string {
    if ($cover && file_exists(UPLOAD_DIR . 'covers/' . $cover)) {
        return SITE_URL . '/uploads/covers/' . $cover;
    }
    return SITE_URL . '/assets/img/default-cover.svg';
}

// Хелпер: форматирование даты
function formatDate(string $date): string {
    $months = [
        1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
        5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
        9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
    ];
    $ts = strtotime($date);
    $day = date('j', $ts);
    $month = $months[(int)date('n', $ts)];
    $year = date('Y', $ts);
    $time = date('H:i', $ts);
    return "$day $month $year, $time";
}

// Хелпер: проверка поддержки
function hasSupported(int $supporterId, int $authorId): bool {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT id FROM supports WHERE supporter_id = ? AND author_id = ? AND status = 'paid' LIMIT 1");
    $stmt->execute([$supporterId, $authorId]);
    return (bool)$stmt->fetch();
}
define('SMTP_HOST', 'smtp.mail.ru');
define('SMTP_PORT', 465);
define('SMTP_USER', 'zarilgo2005@mail.ru');
define('SMTP_PASS', 'uhhzfIyUZeKJDEJRBzhG');


//uhhzfIyUZeKJDEJRBzhG